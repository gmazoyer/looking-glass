# Changelog

## Version 2.3.0 | Established (New features release) | 2024-02-02

  * [#54](https://github.com/gmazoyer/looking-glass/issues/54) Add support for routing instances (routing tables, VRFs, or other names)
  * [#149](https://github.com/gmazoyer/looking-glass/issues/149) Add support for light and dark themes
  * [#150](https://github.com/gmazoyer/looking-glass/issues/150) Use [Composer](https://getcomposer.org/) for dependency management
  * [#170](https://github.com/gmazoyer/looking-glass/issues/170) Fix captcha initialisation
  * Add antispam config option `allow_list` to avoid spam detection for a list of IP prefixes
  * Add output config option `scroll` to enable or disable scrolling for comamnd result
  * Adjust action buttons
  * Show matching routes for IP on Mikrotik
  * Fix `show route ^AS` on Huawei
  * Improve example configuration and documentation
  * Deprecate Telnet as a connection/authentication mean
  * Test against PHP 8.2 and 8.3, remove PHP 8.0
  * Rework Docker image to use Composer and PHP 8.3
  * Move documentation to mkdocs-material

## Version 2.2.0 | Established (New features release) | 2022-05-27

  * Show response parse error in alert
  * [#160](https://github.com/gmazoyer/looking-glass/issues/160) Support modern SSH by using phpseclib 3.0
  * Configuration settings and support for reCAPTCHA and hCAPTCHA
  * Upgrade to Bootstrap 5.1
  * Add a built-in anti-spam to rate limit user requests
  * Add options to set logo width and height

## Version 2.1.0 | Established (New features release) | 2022-05-07

  * New configuration option to set the number of routers to show on the front
    page before the list scrolls. Display the list of available commands based
    on the doc configuration.
  * Fix OpenBGPD commands
  * CSS tweaks to avoid UI flickering
  * Add option to debug authentication mechanism `$config['logs']['auth_debug']`
  * Add Google reCAPTCHA support
  * Add Mikrotik/ROS support
  * Fix PHP extensions for Docker
  * Check if SSH key is readable when validating the config
  * New configuration option allowing users to create a list of AS path regexp
    to blacklist and reject. This could be used to avoid some potential harmful
    AS path regexp that could be used to overload the control plane of some
    routers (especially ones with low CPU and RAM).
  * Consider IPv6 as default version in `get_source_interface_id()`
  * Fix ping command construction for Linux/OpenBSD
  * Add configuration option to set min prefix length for route lookup
  * Enable inline output filtering
  * Move from phpseclib version 1 to phpseclib version 2
  * Allow custom HTML header elements via `additional_html_header`
  * Add basic implementation of Nokia SR-OS
  * Add support for Arista EOS
  * Add support for Huawei VRP
  * Use of Bootstrap utilities, container and columns for responsive design in
    the body content
  * Prefill parameter field with user's IP address
  * Add BIRD v2 support
  * Use Bootstrap 4.6.0, FontAwesome 5.15.2, phpseclib 2.0.30 and jQuery 3.5.1

For more details about the new configuration options take a loot at the
documentation.

## Version 2.0.1 | Established (Bug fixes release) | 2018-01-30

  * Remove dead code
  * Add documentation for FRRouting (thanks to pautiina)
  * Fix focus and highlight the parameter field if it was omitted

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
