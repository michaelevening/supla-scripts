#!/bin/sh
set -e

if [ ! -f /etc/apache2/ssl/server.crt ]; then
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/apache2/ssl/server.key -out /etc/apache2/ssl/server.crt -subj "/C=PL/ST=SUPLA/L=SUPLA/O=SUPLA/CN=SUPLA"
fi

rm -fr /var/www/var/cache/*
/usr/local/bin/php /var/www/supla-scripts init --no-interaction
chown -R www-data:www-data var/backups var/cache var/config var/logs var/ssl var/system

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- apache2-foreground "$@"
fi

exec "$@"
