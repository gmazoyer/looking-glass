# Looking Glass: Using Docker

This looking glass can be run inside a Docker container as long as it meets
the requirements listed in the README. You can use the shipped Dockerfile or
create your own to match your need.

## Pulling an existing image

Looking Glass is available on
[Docker Hub](https://hub.docker.com/r/gmazoyer/looking-glass/) and can be
pulled from there.All official releases of Looking Glass will be pushed on
Docker Hub as soon as they are released. Just run
`docker pull gmazoyer/looking-glass` and you'll get the latest release. You
can also grab any tagged release by using the tag you need.

## Building your own image

If you need to build you own Docker container you'll only need to have a
webserver that is comfortable with interpreting PHP files. Be aware that it is
not yet compatible with PHP 7. However the compatibility has been tested from
PHP 5.3 up to PHP 5.6 (included). You can track down compatibility with PHP
versions [here](https://travis-ci.org/respawner/looking-glass).

## Usage and options

It is not recommended to add any SSH keys, the configuration file or any
additional files inside the Docker image. You can still mount them while
running Docker. Here are few examples:

### With a configuration file

```sh
docker run -p 80:80 -v $(pwd)/config.php:/var/www/html/config.php gmazoyer/looking-glass:latest
```

### With a custom CSS and logo

```sh
docker run -p 80:80 -v $(pwd)/config.php:/var/www/html/config.php -v $(pwd)/mystyle.css:/var/www/html/css/mystyle.css gmazoyer/looking-glass:latest
```
