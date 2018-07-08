FROM php:apache
MAINTAINER Guillaume Mazoyer <gmazoyer@gravitons.in>

RUN apt update
RUN apt -y install bash git ssh libmcrypt-dev openssl libsodium-dev libgmp-dev libgmp3-dev \
  && ln -s /usr/include/x86_64-linux-gnu/gmp.h /usr/include/gmp.h \
  && docker-php-ext-install -j$(nproc) mcrypt gmp \
  && pecl install libsodium \
  && docker-php-ext-enable libsodium \
  && git clone https://github.com/respawner/looking-glass.git --branch master --single-branch /var/www/html/ \
  && apt purge -y --auto-remove git \
  && mkdir -p /var/log/ \
  && touch /var/log/looking-glass.log \
  && chown www-data /var/log/looking-glass.log
