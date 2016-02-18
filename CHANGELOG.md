# Changelog

## Version 1.2.1 | Convergence (Security fix release) | 2016-02-18

  * First security fix release of 1.X.Y branch
  * Treat AS path regex input as invalid if containing ; or " characters

## Version 1.2.0 | Convergence (New features release) | 2016-02-16

  * Second feature release of 1.X.Y branch
  * New configuration options to customize ping and traceroute commands for
    BIRD and Quagga
  * Rework hostname parameter check
  * Embeds Bootstrap 3.3.6, jQuery 2.2.0 and phpseclib 1.0.1

## Version 1.1.0 | Convergence (New features release) | 2014-12-23

  * First feature release of 1.X.Y branch
  * Add support for source interface or addresses in ping and traceroute
    commands ; use interface for Juniper and Cisco ; use addresses for BIRD
    and Quagga
  * Add option to hide the issued command from the output
  * Add option to configure the link used by the header (default is no link)
  * Embeds Bootstrap 3.3.1, JQuery 2.1.3 and phpseclib 0.3.9

For more details about the new configuration options take a loot at the
documentation.

## Version 1.0.0 | Convergence | 2014-12-10

  * Initial release
  * Support for BIRD, Cisco, Juniper, Quagga
  * Routes, AS, ping and traceroute related commands
  * Requires at least PHP 5.2
  * Embeds Bootstrap 3.3.1, JQuery 2.1.1 and phpseclib 0.3.7

