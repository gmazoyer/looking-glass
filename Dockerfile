FROM php:8.5-fpm-alpine AS builder

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN apk add --no-cache git unzip gmp-dev sqlite-dev \
    && docker-php-ext-install -j$(nproc) gmp pdo_sqlite

COPY composer.json composer.lock /var/www/html/
WORKDIR /var/www/html/
RUN composer install --no-dev --optimize-autoloader --no-interaction

FROM builder AS vendor-clean

RUN set -eux; \
    cd /var/www/html/vendor/twbs/bootstrap \
    && find . -mindepth 1 -maxdepth 1 ! -name dist ! -name composer.json -exec rm -rf {} + \
    && cd dist/css && find . ! -name 'bootstrap.min.css' ! -name 'bootstrap.min.css.map' ! -name '.' -exec rm -f {} + \
    && cd ../js && find . ! -name 'bootstrap.bundle.min.js' ! -name 'bootstrap.bundle.min.js.map' ! -name '.' -exec rm -f {} + \
    && cd /var/www/html/vendor/twbs/bootstrap-icons \
    && find . -mindepth 1 -maxdepth 1 ! -name font ! -name composer.json -exec rm -rf {} + \
    && cd /var/www/html \
    && find vendor/ -type f \( -name '*.md' -o -name 'LICENSE*' -o -name 'CHANGELOG*' -o -name '.gitignore' -o -name '.gitattributes' \) -delete \
    && find vendor/ -type d \( -name 'test' -o -name 'tests' -o -name 'Tests' -o -name 'doc' -o -name 'docs' \) -exec rm -rf {} + 2>/dev/null || true

FROM php:8.5-fpm-alpine

COPY --from=caddy:2-alpine /usr/bin/caddy /usr/bin/caddy

RUN apk add --no-cache openssh-client openssl gmp sqlite-libs

COPY --from=builder /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=builder /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/
COPY --from=vendor-clean /var/www/html/vendor/ /var/www/html/vendor/

COPY Caddyfile /etc/caddy/Caddyfile
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

COPY . /var/www/html/
WORKDIR /var/www/html/

RUN mkdir -p /var/log \
    && touch /var/log/looking-glass.log \
    && chown www-data:www-data /var/log/looking-glass.log \
    && chown www-data:www-data /var/www/html/

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
