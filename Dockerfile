FROM php:8.1-apache

RUN apt update \
    && apt -y install bash git ssh openssl libgmp-dev libgmp3-dev libsqlite3-dev \
    && ln -s /usr/include/x86_64-linux-gnu/gmp.h /usr/include/gmp.h \
    && docker-php-ext-install -j$(nproc) gmp \
    && docker-php-ext-install pdo_sqlite \
    && a2enmod remoteip \
    && git clone https://github.com/gmazoyer/looking-glass.git --branch master --single-branch /var/www/html/ \
    && apt purge -y --auto-remove git \
    && rm -rf /var/lib/apt/lists/* \
    && mkdir -p /var/log/ \
    && touch /var/log/looking-glass.log \
    && chown www-data /var/log/looking-glass.log
