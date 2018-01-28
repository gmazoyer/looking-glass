# Changelog

## Version 2.0.0 | Established (New major release) | 2018-01-28

  * First major release of 2.X.Y branch
  * Introduce configuration changes
  * Add support for Cisco IOS-XR
  * Add basic support for OpenBGPd (thanks to ledeuns)
  * Add support for Vyatta (thanks to mikenowak)
  * Add support for FRRouting (thanks to pautiina)
  * Focus and highlight the parameter field if it was omitted
  * Official support for PHP 7.x
  * Add a proper Dockerfile (thanks amtypaldos)
  * Remove Bootstrap related configuration options
  * Changes to user interface with Bootstrap 4
  * Embeds Bootstrap 4.0.0, jQuery 3.3.1, Font Awesome 5.0.6 and phpseclib
    1.0.9

## Version 1.3.0 | Convergence (New features release) | 2016-08-28

  * Third feature release of 1.X.Y branch
  * Add peering policy integration ; when using the configuration option that
    link to a peering policy written in HTML, a small button will be displayed
    below the main buttons allowing to display the policy in a modal
  * Add configuration option to disable IPv6 or IPv4 for defined routers ;
    using this option will limit the query to the enabled protocol version
    (both versions of the protocol are enabled by default)
  * Add configuration option to set the connection timeout for defined
    routers ; it can be useful when a query takes a long time (ie traceroute
    with hidden hosts in the path)
  * Display IPv6 results before IPv4
  * Small changes for UI buttons (should be better on mobile)
  * Embeds Bootstrap 3.3.7, jQuery 3.1.0 and phpseclib 1.0.3

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
  * Embeds Bootstrap 3.3.1, jQuery 2.1.3 and phpseclib 0.3.9

For more details about the new configuration options take a loot at the
documentation.

## Version 1.0.0 | Convergence | 2014-12-10

  * Initial release
  * Support for BIRD, Cisco, Juniper, Quagga
  * Routes, AS, ping and traceroute related commands
  * Requires at least PHP 5.2
  * Embeds Bootstrap 3.3.1, jQuery 2.1.1 and phpseclib 0.3.7

