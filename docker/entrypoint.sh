#!/bin/sh
set -eu

DATA="${SC_DATA_DIR:-/data}"
APP="${SC_APP_DIR:-/var/www/sc}"
SCHEMA="${SC_SCHEMA:-/usr/share/sc/sc.sql}"
BOOTSTRAP_MARK="$DATA/.bootstrapped"
SECRETS="$DATA/config/secrets"
CONFIG="$DATA/config/serverconfig.php"

log() {
	echo "sc-aio: $*" >&2
}

die() {
	log "error: $*"
	exit 1
}

export TZ="${TZ:-America/New_York}"
if [ -e "/usr/share/zoneinfo/$TZ" ]; then
	ln -sfn "/usr/share/zoneinfo/$TZ" /etc/localtime
	echo "$TZ" > /etc/timezone
fi

# PHP drop-in timezone (best-effort; vhost also sets short_open_tag)
for php_ini in /etc/php/*/apache2/conf.d/99-sc.ini /etc/php/*/cli/conf.d/99-sc.ini; do
	if [ -f "$php_ini" ]; then
		sed -i "s|^date.timezone =.*|date.timezone = $TZ|" "$php_ini" || true
	fi
done

mkdir -p \
	"$DATA/mysql" \
	"$DATA/history" \
	"$DATA/icons" \
	"$DATA/logs" \
	"$DATA/config" \
	/run/mysqld \
	/run/apache2 \
	/var/lock/apache2

chown mysql:mysql "$DATA/mysql" /run/mysqld
chown www-data:www-data "$DATA/history" "$DATA/icons" "$DATA/logs"
# www-data must be able to traverse /data and read generated serverconfig.php.
# secrets stay root-only.
chmod 755 "$DATA"
chmod 750 "$DATA/config"
chown root:www-data "$DATA/config"
chmod 700 "$DATA/mysql"

# History viewer assets must sit next to generated HTML (relative MapDraw.js / styles.css).
if [ -d /usr/share/sc/history-static ]; then
	cp -an /usr/share/sc/history-static/. "$DATA/history/"
fi

# Custom icons: bind-mount volume over the app path (survives image rebuild).
if [ -d "$APP/images/aliens/custom" ]; then
	if ! mountpoint -q "$APP/images/aliens/custom"; then
		mount --bind "$DATA/icons" "$APP/images/aliens/custom" || {
			log "bind-mount of icons failed; falling back to symlink"
			rm -rf "$APP/images/aliens/custom"
			ln -sfn "$DATA/icons" "$APP/images/aliens/custom"
		}
	fi
fi

# App error log is a hardcoded relative filename in debug.php.
touch "$DATA/logs/errorlog.html"
chown www-data:www-data "$DATA/logs/errorlog.html"
if [ -e "$APP/errorlog.html" ] && [ ! -L "$APP/errorlog.html" ]; then
	rm -f "$APP/errorlog.html"
fi
if [ ! -e "$APP/errorlog.html" ]; then
	ln -sfn "$DATA/logs/errorlog.html" "$APP/errorlog.html"
fi

# --- secrets (app DB user; root stays unix_socket) ---
MYSQL_DATABASE="${MYSQL_DATABASE:-sc}"
MYSQL_USER="${MYSQL_USER:-sc}"

if [ -f "$SECRETS" ]; then
	# shellcheck disable=SC1090
	. "$SECRETS"
fi

if [ -z "${MYSQL_PASSWORD:-}" ]; then
	MYSQL_PASSWORD="$(openssl rand -hex 24)"
fi

case "$MYSQL_USER" in
	*[!a-zA-Z0-9_]*) die "MYSQL_USER must be alphanumeric" ;;
esac
case "$MYSQL_DATABASE" in
	*[!a-zA-Z0-9_]*) die "MYSQL_DATABASE must be alphanumeric" ;;
esac

umask 077
pw_shell="$(printf '%s' "$MYSQL_PASSWORD" | sed "s/'/'\\\\''/g")"
cat > "$SECRETS" <<EOF
MYSQL_DATABASE='$MYSQL_DATABASE'
MYSQL_USER='$MYSQL_USER'
MYSQL_PASSWORD='$pw_shell'
EOF
chmod 600 "$SECRETS"
export MYSQL_DATABASE MYSQL_USER MYSQL_PASSWORD

# --- MariaDB datadir ---
if [ ! -d "$DATA/mysql/mysql" ]; then
	log "initializing MariaDB datadir"
	if command -v mariadb-install-db >/dev/null 2>&1; then
		mariadb-install-db --user=mysql --datadir="$DATA/mysql" --skip-test-db --auth-root-authentication-method=socket \
			|| mariadb-install-db --user=mysql --datadir="$DATA/mysql" --skip-test-db
	else
		mysql_install_db --user=mysql --datadir="$DATA/mysql"
	fi
fi

log "starting MariaDB for bootstrap"
mariadbd --user=mysql --datadir="$DATA/mysql" --skip-networking=0 &
mysql_pid=$!

i=0
while [ "$i" -lt 90 ]; do
	if mariadb --protocol=socket -uroot -e "SELECT 1" >/dev/null 2>&1; then
		break
	fi
	i=$((i + 1))
	sleep 1
done
if [ "$i" -ge 90 ]; then
	die "MariaDB did not become ready"
fi

# App database + grants (idempotent)
pw_sql="$(printf '%s' "$MYSQL_PASSWORD" | sed "s/'/''/g")"
mariadb --protocol=socket -uroot <<SQL
CREATE DATABASE IF NOT EXISTS \`$MYSQL_DATABASE\` CHARACTER SET latin1 COLLATE latin1_swedish_ci;
CREATE USER IF NOT EXISTS '$MYSQL_USER'@'127.0.0.1' IDENTIFIED BY '$pw_sql';
CREATE USER IF NOT EXISTS '$MYSQL_USER'@'localhost' IDENTIFIED BY '$pw_sql';
ALTER USER '$MYSQL_USER'@'127.0.0.1' IDENTIFIED BY '$pw_sql';
ALTER USER '$MYSQL_USER'@'localhost' IDENTIFIED BY '$pw_sql';
GRANT ALL ON \`$MYSQL_DATABASE\`.* TO '$MYSQL_USER'@'127.0.0.1';
GRANT ALL ON \`$MYSQL_DATABASE\`.* TO '$MYSQL_USER'@'localhost';
FLUSH PRIVILEGES;
SQL

need_schema=0
if ! mariadb --protocol=socket -uroot -N -e "SHOW TABLES FROM \`$MYSQL_DATABASE\` LIKE 'empires'" | grep -q empires; then
	need_schema=1
fi

if [ "$need_schema" -eq 1 ]; then
	[ -f "$SCHEMA" ] || die "schema file missing: $SCHEMA"
	log "importing schema $SCHEMA"
	mariadb --protocol=socket -uroot "$MYSQL_DATABASE" < "$SCHEMA"
else
	log "schema already present, skipping import"
fi

export SC_CONFIG_PATH="$CONFIG"
php /usr/local/lib/sc/render-config.php
chown root:www-data "$CONFIG"
chmod 640 "$CONFIG"

# Admin empire: required on first boot only
admin_exists=0
if mariadb --protocol=socket -uroot -N -e "SELECT COUNT(*) FROM \`$MYSQL_DATABASE\`.empires" 2>/dev/null | grep -qv '^0$'; then
	admin_name="${SC_ADMIN_NAME:-admin}"
	if mariadb --protocol=socket -uroot -N -e "SELECT name FROM \`$MYSQL_DATABASE\`.empires WHERE name='$admin_name' LIMIT 1" | grep -q .; then
		admin_exists=1
	fi
fi

if [ ! -f "$BOOTSTRAP_MARK" ] && [ "$admin_exists" -eq 0 ]; then
	if [ -z "${SC_ADMIN_PASSWORD:-}" ]; then
		kill "$mysql_pid" 2>/dev/null || true
		die "SC_ADMIN_PASSWORD is required on first boot"
	fi
	log "creating admin empire"
	admin_out="$(php /usr/local/lib/sc/create-admin.php)"
	admin_name="$(printf '%s\n' "$admin_out" | sed -n '1p')"
	admin_hash="$(printf '%s\n' "$admin_out" | sed -n '2p')"
	admin_email="$(printf '%s\n' "$admin_out" | sed -n '3p')"
	esc_name="$(printf '%s' "$admin_name" | sed "s/'/''/g")"
	esc_hash="$(printf '%s' "$admin_hash" | sed "s/'/''/g")"
	esc_email="$(printf '%s' "$admin_email" | sed "s/'/''/g")"
	mariadb --protocol=socket -uroot "$MYSQL_DATABASE" <<SQL
INSERT INTO empires (name, last_ip, password, password_hash, is_admin, validation_info, comment, email, real_name, join_date)
VALUES ('$esc_name', '', NULL, '$esc_hash', '1', '', '', '$esc_email', '$esc_name', UNIX_TIMESTAMP());
SQL
fi

if [ "${SC_RESET_ADMIN_PASSWORD:-}" = "1" ] && [ -n "${SC_ADMIN_PASSWORD:-}" ]; then
	log "SC_RESET_ADMIN_PASSWORD=1: updating admin hash"
	admin_out="$(php /usr/local/lib/sc/create-admin.php)"
	admin_name="$(printf '%s\n' "$admin_out" | sed -n '1p')"
	admin_hash="$(printf '%s\n' "$admin_out" | sed -n '2p')"
	esc_name="$(printf '%s' "$admin_name" | sed "s/'/''/g")"
	esc_hash="$(printf '%s' "$admin_hash" | sed "s/'/''/g")"
	mariadb --protocol=socket -uroot "$MYSQL_DATABASE" <<SQL
UPDATE empires SET password = NULL, password_hash = '$esc_hash' WHERE name = '$esc_name';
SQL
fi

date -u +%Y-%m-%dT%H:%M:%SZ > "$BOOTSTRAP_MARK"
chown www-data:www-data "$DATA/history" "$DATA/icons" "$DATA/logs" || true
chmod 755 "$DATA/history" "$DATA/icons" "$DATA/logs"

log "stopping bootstrap MariaDB"
mariadb-admin --protocol=socket -uroot shutdown || true
i=0
while kill -0 "$mysql_pid" 2>/dev/null; do
	i=$((i + 1))
	if [ "$i" -gt 30 ]; then
		kill -9 "$mysql_pid" 2>/dev/null || true
		break
	fi
	sleep 1
done

log "starting supervisord"
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/sc.conf
