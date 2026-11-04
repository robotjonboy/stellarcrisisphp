#!/bin/sh
set -eu

SECRETS=/data/config/secrets
if [ ! -f "$SECRETS" ]; then
	exit 1
fi

MYSQL_USER=sc
MYSQL_PASSWORD=
# shellcheck disable=SC1090
. "$SECRETS"

mariadb-admin ping --silent -h 127.0.0.1 -u "$MYSQL_USER" "-p${MYSQL_PASSWORD}" >/dev/null
curl -fsS -o /dev/null http://127.0.0.1/styles.css
