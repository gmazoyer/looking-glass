FROM php:8.2-apache

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN apt update \
    && apt -y install bash git ssh openssl libgmp-dev libgmp3-dev libsqlite3-dev \
    && rm -rf /var/lib/apt/lists/* \
    && ln -s /usr/include/x86_64-linux-gnu/gmp.h /usr/include/gmp.h
RUN docker-php-ext-install -j$(nproc) gmp \
    && docker-php-ext-install pdo_sqlite \
    && a2enmod remoteip

COPY . /var/www/html
WORKDIR /var/www/html

RUN composer install \
    && mkdir -p /var/log/ \
    && touch /var/log/looking-glass.log \
    && chown www-data /var/log/looking-glass.log
