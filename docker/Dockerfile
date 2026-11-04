# Stellar Crisis PHP — all-in-one (Apache + PHP + MariaDB)
# Build from the repository root:
#   docker build -f docker/Dockerfile -t sc-aio .

FROM debian:stable

ENV DEBIAN_FRONTEND=noninteractive \
    TZ=America/New_York \
    SC_CONFIG_PATH=/data/config/serverconfig.php \
    APACHE_CONFDIR=/etc/apache2 \
    APACHE_ENVVARS=/etc/apache2/envvars

# Prevent apt from starting services during install.
RUN printf '#!/bin/sh\nexit 101\n' > /usr/sbin/policy-rc.d \
	&& chmod +x /usr/sbin/policy-rc.d

RUN apt-get update \
	&& apt-get install -y --no-install-recommends \
		apache2 \
		ca-certificates \
		curl \
		libapache2-mod-php \
		mariadb-client \
		mariadb-server \
		openssl \
		php-cli \
		php-mysql \
		supervisor \
		tzdata \
	&& rm -rf /var/lib/apt/lists/* \
	&& rm -f /usr/sbin/policy-rc.d \
	&& if ! command -v mariadbd >/dev/null 2>&1; then \
		ln -s "$(command -v mysqld)" /usr/sbin/mariadbd; \
	fi

# Prefer prefork with mod_php (Debian usually switches this on install).
RUN a2dismod mpm_event 2>/dev/null || true \
	&& a2enmod mpm_prefork 2>/dev/null || true \
	&& for mod in /etc/apache2/mods-available/php*.load; do \
		[ -f "$mod" ] || continue; \
		a2enmod "$(basename "$mod" .load)"; \
	done

COPY docker/php.ini /tmp/99-sc.ini
RUN for d in /etc/php/*/apache2/conf.d /etc/php/*/cli/conf.d; do \
		if [ -d "$d" ]; then cp /tmp/99-sc.ini "$d/99-sc.ini"; fi; \
	done \
	&& rm -f /tmp/99-sc.ini

COPY docker/apache-sc.conf /etc/apache2/sites-available/sc.conf
COPY docker/mariadb-sc.cnf /etc/mysql/mariadb.conf.d/99-sc-aio.cnf
COPY docker/supervisord.conf /etc/supervisor/conf.d/sc.conf
COPY docker/apache-foreground /usr/local/bin/apache-foreground
COPY docker/entrypoint.sh /usr/local/bin/sc-entrypoint
COPY docker/healthcheck.sh /usr/local/bin/sc-healthcheck
COPY docker/render-config.php /usr/local/lib/sc/render-config.php
COPY docker/create-admin.php /usr/local/lib/sc/create-admin.php

RUN chmod +x /usr/local/bin/apache-foreground /usr/local/bin/sc-entrypoint /usr/local/bin/sc-healthcheck \
	&& a2dissite 000-default \
	&& a2ensite sc \
	&& mkdir -p /var/www/sc /data /usr/share/sc /usr/local/lib/sc \
	&& rm -rf /var/www/html

# Application tree (see .dockerignore). Schema is copied aside so a bind-mount
# of the working tree cannot hide first-boot SQL.
COPY . /var/www/sc
RUN cp /var/www/sc/sc.sql /usr/share/sc/sc.sql \
	&& mkdir -p /usr/share/sc/history-static \
	&& cd /var/www/sc/history \
	&& cp MapDraw.js styles.css blank.gif upno.gif d0.gif d1.gif d2.gif d3.gif d4.gif d5.gif d6.gif d7.gif d8.gif d9.gif /usr/share/sc/history-static/ \
	&& mkdir -p /var/www/sc/images/aliens/custom \
	&& chown -R www-data:www-data /var/www/sc \
	&& find /var/www/sc -type d -exec chmod 755 {} \; \
	&& find /var/www/sc -type f -exec chmod 644 {} \;

VOLUME ["/data"]
EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=300s --retries=5 \
	CMD /usr/local/bin/sc-healthcheck

STOPSIGNAL SIGTERM

ENTRYPOINT ["/usr/local/bin/sc-entrypoint"]
