FROM php:5.6-apache
MAINTAINER Guillaume Mazoyer <gmazoyer@gravitons.in>

WORKDIR /var/www/html

# add timezone to php.ini
RUN echo 'date.timezone = UTC' >> /usr/local/etc/php/php.ini

# copy code from local folder
COPY ./ /var/www/html

RUN touch /var/log/looking-glass.log && chmod 777 /var/log/looking-glass.log


