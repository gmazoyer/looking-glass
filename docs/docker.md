# Looking Glass: Using Docker

This looking glass can be run inside a Docker container as long as it meets
the requirements listed in the README. You can use the shipped Dockerfile or
create your own to match your need.

## Pulling an existing image

Looking Glass is available on
[Docker Hub](https://hub.docker.com/r/gmazoyer/looking-glass/) and can be
pulled from there. All official releases of Looking Glass will be pushed on
Docker Hub as soon as they are released. Just run
`docker pull gmazoyer/looking-glass` and you'll get the latest. You can also
grab any tagged release by using the tag you need.

## Building your own image

If you need to build you own Docker container you'll only need to have a
webserver that is comfortable with interpreting PHP files. You can track
down PHP versions comptaibility
[here](https://travis-ci.org/respawner/looking-glass).

## Usage and options

It is not recommended to add any SSH keys, the configuration file or any
additional files inside the Docker image. You can still mount them while
running Docker. Below you'll find examples that expose the looking glass on
the port 80 of the host.

### With a configuration file

```sh
docker run -p 80:80 -v $(pwd)/config.php:/var/www/html/config.php gmazoyer/looking-glass:latest
```

### With a custom CSS and logo

```sh
docker run -p 80:80 -v $(pwd)/config.php:/var/www/html/config.php -v $(pwd)/mystyle.css:/var/www/html/css/mystyle.css gmazoyer/looking-glass:latest
```

## Running this Docker

If you need to automatically run the Docker container at the host startup you
can write a new service. With systemd it is pretty simple to do, here is an
example supposing the container has already been created and is named
"looking-glass":
```
[Unit]
Description=Looking Glass container
Requires=docker.service
After=docker.service

[Service]
Restart=always
ExecStart=/usr/bin/docker start -a looking-glass
ExecStop=/usr/bin/docker stop -t 2 looking-glass

[Install]
WantedBy=default.target
```
