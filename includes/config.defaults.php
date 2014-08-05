<?php

$config = array (

  // Frontpage configuration
  'frontpage' => array (
    // CSS to use
    'css' => 'css/style.css',
    // Title of the page
    'title' => 'Looking Glass',
    'show_title' => true,
    // Show visitor IP address
    'show_visitor_ip' => true,
    // Frontpage order you can use: routers, commands, parameters, buttons
    'order' => array('routers', 'commands', 'parameters', 'buttons')
  ),

  // Misc
  'misc' => array(
    // Logs file when commands will be written
    'logs' => '/var/log/looking-glass.log',
    // Allow private ASN
    'allow_private_asn' => false
  ),

  // Documentation (must be HTML)
  'doc' => array (
    // Documentation for the 'show route' query
    'bgp' => array(
      'query' => 'Show the best routes to a given destination.',
      'parameter' => 'The parameter must be a valid destination. Destination means an IPv4/IPv6 address or a subnet. Masks are also accepted as part of a valid IPv4/IPv6 address.<br />Please note that some routers always need a mask to be given when looking for an IPv6 address.<br /><br />Example of valid arguments:<br /><ul><li>10.1.1.1</li><li>172.16.0.0/12</li><li>2001:db8:1337::42</li><li>2001:db8::/32</li>'
    ),
    // Documentation for the 'as-path-regex' query
    'as-path-regex' => array(
      'query' => 'Show the routes matching the given AS path regular expression.',
      'parameter' => 'The parameter must be a valid AS path regular expression.<br />Please note that these expression can change depending on the router and its software.<br /><br />Here are some examples:<ul><li><strong>Juniper</strong> - ^AS1 AS2 .*$</li><li><strong>Cisco</strong> - ^AS1_</li><li><strong>BIRD</strong> - AS1 AS2 AS3 &hellip; ASZ</li></ul><br />You may find some help with the following link:<br /><ul><li><a href="http://www.juniper.net/techpubs/en_US/junos13.3/topics/reference/command-summary/show-route-aspath-regex.html" title="Juniper Documentation">Juniper Documentation</a></li><li><a href="http://www.cisco.com/c/en/us/support/docs/ip/border-gateway-protocol-bgp/26634-bgp-toc.html#asregexp" title="Cisco Documentation">Cisco Documentation</a></li><li><a href="http://bird.network.cz/?get_doc&f=bird-5.html" title="BIRD Documentation">BIRD Documentation</a> (search for bgpmask)</li></ul>'
    ),
    // Documentation for the 'as' query
    'as' => array(
      'query' => 'Show the routes received from a given AS number.',
      'parameter' => 'The parameter must be a valid 16-bit or 32-bit autonomous system number.<br />Be careful, 32-bit ASN are not handled by old routers or old router softwares.<br />Unless specified, private ASN will be considered as invalid.<br /><br />Example of valid argument:<br /><ul><li>15169</li><li>29467</li></ul>'
    ),
    // Documentation for the 'ping' query
    'ping' => array(
      'query' => 'Send 10 pings to the given destination.',
      'parameter' => 'The parameter must be an IPv4/IPv6 address (without mask) or a fully qualified domain name.<br /><br />Example of valid arguments:<br /><ul><li>10.1.1.1</li><li>2001:db8:1337::42</li><li>example.com</li></ul>'
    ),
    // Documentation for the 'traceroute' query
    'traceroute' => array(
      'query' => 'Display the path to a given destination.',
      'parameter' => 'The parameter must be an IPv4/IPv6 address (without mask) or a fully qualified domain name.<br /><br />Example of valid arguments:<br /><ul><li>10.1.1.1</li><li>2001:db8:1337::42</li><li>example.com</li></ul>'
    )
  )

);

// End of config.defaults..php
