<?php

/*
 * Please don't edit this file!
 * Make changes to the configuration array in config.php.
 */

$config = array(

  // Release configuration
  'release' => array(
    'version' => '1.1.0',
    'codename' => 'Convergence'
  ),

  // Frontpage configuration
  'frontpage' => array(
    // Use Bootstrap theme
    'bootstrap_theme' => true,
    // Custom Bootstrap theme
    'custom_bootstrap_theme' => false,
    // CSS to use
    'css' => 'css/style.css',
    // Title
    'title' => 'Looking Glass',
    // Image (null for no image)
    'image' => null,
    // Link for the title/image
    'header_link' => null,
    // Disclaimer (null for no disclaimer)
    'disclaimer' => 'Disclaimer example',
    // Display the title
    'show_title' => true,
    // Show visitor IP address
    'show_visitor_ip' => true,
    // Frontpage order you can use: routers, commands, parameters, buttons
    'order' => array('routers', 'commands', 'parameters', 'buttons')
  ),

  // Contact (both null for no contact)
  'contact' => array(
    // Name of the contact
    'name' => 'Example Support',
    // Email of the contact
    'mail' => 'support@example.com'
  ),

  // Output control
  'output' => array(
    // Show or hide command in output
    'show_command' => true
  ),

  // Filters
  'filters' => array(),

  // Logs
  'logs' => array(
    // Logs file where commands will be written
    'file' => '/var/log/looking-glass.log',
    // Format for each logged command (%D is for the time, %R is for the
    // requester IP address, %H is for the host and %C is for the command)
    'format' => '[%D] [client: %R] %H > %C'
  ),

  // Misc
  'misc' => array(
    // Allow private ASN
    'allow_private_asn' => false,
    // Allow RFC1918 IPv4 and FD/FC IPv6 as parameters
    'allow_private_ip' => true,
    // Allow reserved IPv4 addresses (0.0.0.0/8, 169.254.0.0/16,
    // 192.0.2.0/24 and 224.0.0.0/4)
    'allow_reserved_ip' => true
  ),

  // Documentation (must be HTML)
  'doc' => array(
    // Documentation for the 'show route' query
    'bgp' => array(
      'command' => 'show route IP_ADDRESS',
      'description' => 'Show the best routes to a given destination.',
      'parameter' => 'The parameter must be a valid destination. Destination means an IPv4/IPv6 address or a subnet. Masks are also accepted as part of a valid IPv4/IPv6 address.<br />RFC1918 addresses, IPv6 starting with FD or FC, and IPv4 reserved ranges (0.0.0.0/8, 169.254.0.0/16, 192.0.2.0/24 and 224.0.0.0/4) may be refused.<br />Please note that some routers always need a mask to be given when looking for an IPv6 address.<br /><br />Example of valid arguments:<br /><ul><li>8.8.8.8</li><li>8.8.4.0/24</li><li>2001:db8:1337::42</li><li>2001:db8::/32</li>'
    ),
    // Documentation for the 'as-path-regex' query
    'as-path-regex' => array(
      'command' => 'show route as-path-regex AS_PATH_REGEX',
      'description' => 'Show the routes matching the given AS path regular expression.',
      'parameter' => 'The parameter must be a valid AS path regular expression.<br />Please note that these expression can change depending on the router and its software.<br /><br />Here are some examples:<ul><li><strong>Juniper</strong> - ^AS1 AS2 .*$</li><li><strong>Cisco</strong> - ^AS1_</li><li><strong>BIRD</strong> - AS1 AS2 AS3 &hellip; ASZ</li></ul><br />You may find some help with the following link:<br /><ul><li><a href="http://www.juniper.net/techpubs/en_US/junos13.3/topics/reference/command-summary/show-route-aspath-regex.html" title="Juniper Documentation">Juniper Documentation</a></li><li><a href="http://www.cisco.com/c/en/us/support/docs/ip/border-gateway-protocol-bgp/26634-bgp-toc.html#asregexp" title="Cisco Documentation">Cisco Documentation</a></li><li><a href="http://bird.network.cz/?get_doc&f=bird-5.html" title="BIRD Documentation">BIRD Documentation</a> (search for bgpmask)</li></ul>'
    ),
    // Documentation for the 'as' query
    'as' => array(
      'command' => 'show route AS',
      'description' => 'Show the routes received from a given AS number.',
      'parameter' => 'The parameter must be a valid 16-bit or 32-bit autonomous system number.<br />Be careful, 32-bit ASN are not handled by old routers or old router softwares.<br />Unless specified, private ASN will be considered as invalid.<br /><br />Example of valid argument:<br /><ul><li>15169</li><li>29467</li></ul>'
    ),
    // Documentation for the 'ping' query
    'ping' => array(
      'command' => 'ping IP_ADDRESS',
      'description' => 'Send 10 pings to the given destination.',
      'parameter' => 'The parameter must be an IPv4/IPv6 address (without mask) or a fully qualified domain name.<br />RFC1918 addresses, IPv6 starting with FD or FC, and IPv4 reserved ranges (0.0.0.0/8, 169.254.0.0/16, 192.0.2.0/24 and 224.0.0.0/4) may be refused.<br /><br />Example of valid arguments:<br /><ul><li>8.8.8.8</li><li>2001:db8:1337::42</li><li>example.com</li></ul>'
    ),
    // Documentation for the 'traceroute' query
    'traceroute' => array(
      'command' =>'traceroute IP_ADDRESS',
      'description' => 'Display the path to a given destination.',
      'parameter' => 'The parameter must be an IPv4/IPv6 address (without mask) or a fully qualified domain name.<br />RFC1918 addresses, IPv6 starting with FD or FC, and IPv4 reserved ranges (0.0.0.0/8, 169.254.0.0/16, 192.0.2.0/24 and 224.0.0.0/4) may be refused.<br /><br />Example of valid arguments:<br /><ul><li>8.8.8.8</li><li>2001:db8:1337::42</li><li>example.com</li></ul>'
    )
  ),

  // Routers
  'routers' => array()

);

// End of config.defaults.php
