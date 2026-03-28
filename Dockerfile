FROM php:8.5-apache AS builder

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN apt-get update \
    && apt-get -y install --no-install-recommends git unzip libgmp-dev libsqlite3-dev \
    && rm -rf /var/lib/apt/lists/* \
    && ln -sf /usr/include/*/gmp.h /usr/include/gmp.h \
    && docker-php-ext-install -j$(nproc) gmp pdo_sqlite

COPY composer.json composer.lock /var/www/html/
RUN composer install --no-dev --optimize-autoloader --no-interaction

FROM php:8.5-apache

RUN apt-get update \
    && apt-get -y install --no-install-recommends openssh-client openssl libgmp10 libsqlite3-0 \
    && rm -rf /var/lib/apt/lists/* \
    && a2enmod remoteip

COPY --from=builder /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=builder /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/
COPY --from=builder /var/www/html/vendor/ /var/www/html/vendor/

COPY . /var/www/html/
WORKDIR /var/www/html/

RUN mkdir -p /var/log/ \
    && touch /var/log/looking-glass.log \
    && chown www-data /var/log/looking-glass.log

