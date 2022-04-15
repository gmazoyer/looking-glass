<?php

/*
 * Looking Glass - An easy to deploy Looking Glass
 * Copyright (C) 2014-2022 Guillaume Mazoyer <guillaume@mazoyer.eu>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301  USA
 */

/*
 * Please don't edit this file!
 * Make changes to the configuration array in config.php.
 */

function set_defaults_for_routers(&$parsed_config) {
  $router_defaults = array(
    'timeout' => 30,
    'disable_ipv6' => false,
    'disable_ipv4' => false,
    'bgp_detail' => false
  );

  // Loads defaults when key does not exist
  foreach ($parsed_config['routers'] as &$router) {
    foreach ($router_defaults as $key => $value) {
      if (!array_key_exists($key, $router)) {
        $router[$key] = $value;
      }
    }
  }
}

$config = array(

  // Release configuration
  'release' => array(
    'version' => '2.1.0',
    'codename' => 'Established',
    'repository' => 'https://github.com/gmazoyer/looking-glass'
  ),

  // Frontpage configuration
  'frontpage' => array(
    // CSS to use
    'css' => 'css/style.css',
    // Extra HTML header elements
    'additional_html_header' => null,
    // Title
    'title' => 'Looking Glass',
    // Image (null for no image)
    'image' => null,
    // Image width and heoght (0 to ignore)
    'image_width' => 0,
    'image_height' => 0,
    // Link for the title/image
    'header_link' => null,
    // Peering Policy file (null for no peering policy)
    'peering_policy_file' => null,
    // Disclaimer (null for no disclaimer)
    'disclaimer' => 'Disclaimer example',
    // Display the title
    'show_title' => true,
    // Show visitor IP address
    'show_visitor_ip' => true,
    // Frontpage order you can use: routers, commands, parameter, buttons
    'order' => array('routers', 'commands', 'parameter', 'buttons'),
    // Number of routers to show on frontpage
    'router_count' => 5,
    // Number of commands to show on frontpage (0 scales dynamically)
    'command_count' => 0
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
  'filters' => array(
    // Lines (based on regexp) not to show in the output
    'output' => array(),
    // AS path regexps to disallow
    'aspath_regexp' => array(
      '.',
      '.*',
      '.[,]*',
      '.[0-9,0-9]*',
      '.[0-9,0-9]+'
    )
  ),

// Google reCaptcha
  'recaptcha' => array(
    // Disabled by default
    'enabled' => false,
    'url' => 'https://www.google.com/recaptcha/api/siteverify',
    'apikey' => null,
    'secret' => null
  ),

  // Logs
  'logs' => array(
    // Logs file where commands will be written
    'file' => '/var/log/looking-glass.log',
    // Format for each logged command (%D is for the time, %R is for the
    // requester IP address, %H is for the host and %C is for the command)
    'format' => '[%D] [client: %R] %H > %C',
    // Logs authentication debug details to the logs file
    'auth_debug' => false
  ),

  // Misc
  'misc' => array(
    // Allow private ASN
    'allow_private_asn' => false,
    // Allow RFC1918 IPv4 and FD/FC IPv6 as parameters
    'allow_private_ip' => true,
    // Allow reserved IPv4 addresses (0.0.0.0/8, 169.254.0.0/16,
    // 192.0.2.0/24 and 224.0.0.0/4)
    'allow_reserved_ip' => true,
    // Allowed prefix length for route lookup
    'minimum_prefix_length' => array(
      'ipv6' => 0,
      'ipv4' => 0
    ),
    // Extract user "real" IP from the HTTP_X_FORWARDED_FOR header
    //  as this header can be spoofed by the user, it's not recommended to enable this option.
    'enable_http_x_forwarded_for' => false,

  ),

  // Tools used for some processing
  'tools' => array(
    // Options to be used when pinging from a UNIX host (case of BIRD, Quagga,
    // and others)
    'ping_options' => '-A -c 10',
    // Source option to use when pinging
    'ping_source_option' => '-I',
    // Traceroute tool to be used
    'traceroute4' => 'traceroute -4',
    'traceroute6' => 'traceroute -6',
    // Options to be used when tracerouting from a UNIX host (case of BIRD,
    // Quagga, and others)
    'traceroute_options' => '-A -q1 -N32 -w1 -m15',
    // Source option to use when tracerouting
    'traceroute_source_option' => '-s'
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
      'parameter' => 'The parameter must be a valid AS path regular expression and must not contain any " characters (the input will be automatically quoted if needed).<br />Please note that these expressions can change depending on the router and its software.<br />OpenBGPD does not support regular expressions, but will search for the submitted AS number anywhere in the AS path.<br /><br />Here are some examples:<ul><li><strong>Juniper</strong> - ^AS1 AS2 .*$</li><li><strong>Cisco</strong> - ^AS1_AS2_</li><li><strong>BIRD</strong> - AS1 AS2 AS3 &hellip; ASZ</li><li><strong>OpenBGPD</strong> - AS1</li></ul><br />You may find some help with the following link:<br /><ul><li><a href="http://www.juniper.net/techpubs/en_US/junos13.3/topics/reference/command-summary/show-route-aspath-regex.html" title="Juniper Documentation">Juniper Documentation</a></li><li><a href="http://www.cisco.com/c/en/us/support/docs/ip/border-gateway-protocol-bgp/26634-bgp-toc.html#asregexp" title="Cisco Documentation">Cisco Documentation</a></li><li><a href="http://bird.network.cz/?get_doc&f=bird-5.html" title="BIRD Documentation">BIRD Documentation</a> (search for bgpmask)</li></ul>'
    ),
    // Documentation for the 'as' query
    'as' => array(
      'command' => 'show route ^AS',
      'description' => 'Show the routes received from a given neighboring AS number.',
      'parameter' => 'The parameter must be a valid 16-bit or 32-bit autonomous system number.<br />Be careful, 32-bit ASN are not handled by old routers or old router softwares.<br />Unless specified, private ASN will be considered as invalid.<br /><br />Example of valid argument:<br /><ul><li>15169</li><li>29467</li></ul>'
    ),
    // Documentation for the 'ping' query
    'ping' => array(
      'command' => 'ping IP_ADDRESS|HOSTNAME',
      'description' => 'Send pings to the given destination.',
      'parameter' => 'The parameter must be an IPv4/IPv6 address (without mask) or a hostname.<br />RFC1918 addresses, IPv6 starting with FD or FC, and IPv4 reserved ranges (0.0.0.0/8, 169.254.0.0/16, 192.0.2.0/24 and 224.0.0.0/4) may be refused.<br /><br />Example of valid arguments:<br /><ul><li>8.8.8.8</li><li>2001:db8:1337::42</li><li>example.com</li></ul>'
    ),
    // Documentation for the 'traceroute' query
    'traceroute' => array(
      'command' => 'traceroute IP_ADDRESS|HOSTNAME',
      'description' => 'Display the path to a given destination.',
      'parameter' => 'The parameter must be an IPv4/IPv6 address (without mask) or a hostname.<br />RFC1918 addresses, IPv6 starting with FD or FC, and IPv4 reserved ranges (0.0.0.0/8, 169.254.0.0/16, 192.0.2.0/24 and 224.0.0.0/4) may be refused.<br /><br />Example of valid arguments:<br /><ul><li>8.8.8.8</li><li>2001:db8:1337::42</li><li>example.com</li></ul>'
    )
  ),

  // Routers
  'routers' => array()

);

// End of config.defaults.php
