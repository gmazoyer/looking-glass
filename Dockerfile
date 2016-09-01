FROM php:5.6-apache
MAINTAINER Guillaume Mazoyer <gmazoyer@gravitons.in>

# Need git
RUN apt-get update && apt-get install -y git

WORKDIR /var/www/html

# Clone repository with given branch
ARG BRANCH=master
ARG URL=https://github.com/respawner/looking-glass.git
RUN git clone --depth 1 $URL -b $BRANCH .

# Get rid of git
RUN apt-get purge -y --auto-remove git
