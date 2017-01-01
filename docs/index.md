[![Build Status](https://travis-ci.org/respawner/looking-glass.svg?branch=master)](https://travis-ci.org/respawner/looking-glass)
[![Documentation Status](https://readthedocs.org/projects/looking-glass/badge/?version=latest)](http://looking-glass.readthedocs.io/en/latest/?badge=latest)

# Looking Glass

Easy to deploy Looking Glass made in PHP.

## Requirements

  * Webserver such as Apache 2, or Lighttpd, etc…
  * PHP (>= 5.3) module for the webserver (mod-php5 for Apache 2 for example)

## Description

This web application made in PHP is what we call a **Looking Glass**. This is a
tool used to get some information about networks by giving the opportunity to
execute some commands on routers. The output is sent back to the user.

For now this looking glass is quite simple. Here you have some features:

  * Interface using Javascript and AJAX calls (needs a decent browser)
  * Support of BIRD, Cisco (IOS and IOS-XR), Juniper and Quagga routers
  * Support of Telnet and SSH connection to routers using password
    authentication and SSH keys.
  * Configurable list of routers
  * Tweakable interface (title, logo, footer, elements order)
  * Log all commands in a file
  * Customizable output with regular expressions

And here is a list of what this looking glass should be able to do in the
future:

  * Support more routers
  * Support of other types of authentication
  * Configurable list of allowed commands

## Configuration

Copy the configuration **config.php.example** file to create a **config.php**
file. It contains all the values (PHP variables) used to customize the looking
glass. Details about configuration options are available in the
[documentation](docs/configuration.md).

## Docker

If you want to run the looking glass inside a Docker container, a Dockerfile
is provided in this repository. More details can be found
[here](docs/docker.md).

## Documentation

An up-to-date (hopefully) documentation is available in the **docs/**
directory. It gives enough details to setup the looking glass, to configure it
and to prepare your routers.

You can also find it at [Read the Docs](http://looking-glass.readthedocs.io/en/latest/).

## License

Looking Glass is released under the terms of the GNU GPLv3. Please read the
LICENSE file for more information.

## Contact

If you have any bugs, errors, improvements, patches, ideas, you can contact me
on my email address <gmazoyer@gravitons.in>. You are also welcome to fork and
make some pull requests.

If you use this looking glass in your company, please drop me a mail. I would
be glad to know that this project was helpful for you, and I will update our
[documentation](docs/our_users.md) to include your company inside the list of
users if you want me to.

## Thanks

  * [Bootstrap](http://getbootstrap.com/) and [jQuery](http://jquery.com/) for
    making CSS and Javascript less painful for people like me.
  * [Romain Boissat](https://chroot-me.in/) for all his great ideas, bug reports and contributions.
