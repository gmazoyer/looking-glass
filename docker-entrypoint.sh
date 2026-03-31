#!/bin/sh
set -e

php-fpm --daemonize

exec caddy run --config /etc/caddy/Caddyfile --adapter caddyfile
