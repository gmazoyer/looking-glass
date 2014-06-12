<?php

/*
 * Looking Glass - An easy to deploy Looking Glass
 * Copyright (C) 2014 Guillaume Mazoyer <gmazoyer@gravitons.in>
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

require_once 'router.php';
require_once 'utils.php';

final class Juniper extends Router {
  protected function build_command($command, $parameters) {
    switch ($command) {
      case 'bgp':
        if (match_ipv4($parameters) || match_ipv6($parameters)) {
          $complete_command = 'show route '.$parameters.
            ' table inet.0 protocol bgp active-path | no-more';
        } else if (match_ipv6($parameters)) {
          $complete_command = 'show route '.$parameters.
            ' table inet6.0 protocol bgp active-path | no-more';
        } else {
          throw new Exception('The parameter is not an IPv4/IPv6 address.');
        }
        break;

      case 'as-path-regex':
        if (match_aspath_regex($parameters)) {
          $complete_command = 'show route aspath-regex '.$parameters.' | no-more';
        } else {
          throw new Exception('The parameter is not an AS-Path regular expression like ".*XXXX YYYY.*".');
        }
        break;

      case 'as':
        if (match_as($parameters)) {
	  $complete_command = 'show route aspath-regex .*'.$parameters.'.* | no-more';
        } else {
          throw new Exception('The parameter is not an AS number.');
        }
        break;

      case 'ping':
        if (match_ipv4($parameters) || match_ipv6($parameters) ||
          match_fqdn($parameters)) {
          $complete_command = 'ping count 10 '.$parameters.' rapid';
        } else {
          throw new Exception('The parameter is not an IPv4/IPv6 address or a FQDN.');
        }
        break;

      case 'traceroute':
        if (match_ipv4($parameters)) {
          $complete_command = 'traceroute '.$parameters.' as-number-lookup';
        } else if (match_ipv6($parameters) || match_fqdn($parameters)) {
          $complete_command = 'traceroute '.$parameters;
        } else {
          throw new Exception('The parameter is not an IPv4/IPv6 address or a FQDN.');
        }
        break;

      default:
        throw new Exception('Command not supported.');
    }

    return $complete_command;
  }
}

// End of juniper.php
