![Build Status](https://github.com/gmazoyer/looking-glass/workflows/syntax/badge.svg)
[![Documentation Status](https://readthedocs.org/projects/looking-glass/badge/?version=latest)](http://looking-glass.readthedocs.io/)

# Looking Glass

Easy to deploy Looking Glass made in PHP.

The lack of time and funding has slown down this project quite a lot. It is
still maintained though. If you like it, use it and wants to support its
development, you can sponsor it or to contribute in any way you can.

## Requirements

  * Webserver such as Apache 2, or Lighttpd, etc…
  * PHP (>= 8.1) module for the webserver (`libapache2-mod-php` for Apache 2
    for example)
  * The PDO extension to interact with SQLite for anti-spam feature
    (`php8.2-sqlite3` on Debian for example)
  * The XML package is required as well (`php8.2-xml` on Debian for example)

## Description

This web application made in PHP is what we call a **Looking Glass**. This is a
tool used to get some information about networks by giving the opportunity to
execute some commands on routers. The output is sent back to the user.

For now this looking glass is quite simple. Here you have some features:

  * Interface using Javascript and AJAX calls (needs a decent browser)
  * Support the following router types:
    * Arista
    * BIRD (v1 and v2)
    * Cisco (IOS and IOS-XR)
    * Extreme/Brocade NetIron
    * FRRouting
    * Huawei (VRP)
    * Juniper
    * Mikrotik/RouterOS
    * Nokia
    * OpenBGPd
    * Quagga
    * Vyatta/VyOS/EdgeOS
  * Support of Telnet and SSH connection to routers using password
    authentication and SSH keys
  * Configurable list of routers
  * Tweakable interface (title, logo, footer, elements order)
  * Log all commands in a file
  * Customizable output with regular expressions
  * Configurable list of allowed commands
  * Custom routing instances, aka VRFs (Juniper only, for now)

And here is a list of what this looking glass should be able to do in the
future:

  * Support more routers
  * Support of other types of authentication

Questions? Comments? Join us in the `#looking-glass` Slack channel on
[NetDev Community](https://netdev.chat/).

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

You can also find it at
[Read the Docs](http://looking-glass.readthedocs.io/en/latest/).

## License

Looking Glass is released under the terms of the GNU GPLv3. Please read the
LICENSE file for more information.

## Contact

If you have any bugs, errors, improvements, patches, ideas, you can create an
issue. You are also welcome to fork and make some pull requests.

## Helping

You can help this project in many ways. Of course you can ask for features,
give some ideas for future development, open issues if you found any and
contribute to the code with pull requests and patches. You can also support the
development of this project by donating some coins.
