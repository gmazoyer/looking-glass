Looking Glass
=============

Easy to deploy Looking Glass made in PHP.

Requirements
------------

  * Webserver such as Apache 2, or Lighttpd, etc…
  * PHP module for the webserver (mod-php5 for Apache 2 for example)
  * SSH2 library for PHP libssh2-php

Description
-----------

This web application made in PHP is what we call a **Looking Glass**. This is a
tool used to get some information about networks by giving the opportunity to
execute some commands on routers. The output is sent back to the user.

For now this looking glass is quite simple. Here you have some features:

  * Interface using Javascript and AJAX calls (needs a decent browser)
  * Support of Juniper routers
  * Support of SSH connection to routers using password authentication
  * Configurable list of routers
  * Tweakable interface (title, logo, footer)
  * Log all commands in a file

Configuration
-------------

License
-------

Looking Glass is released under the terms of the GNU GPLv3. Please read the
LICENSE file for more informations.
