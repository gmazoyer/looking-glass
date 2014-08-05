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
require_once 'includes/utils.php';

final class Cisco extends Router {
  protected function build_commands($command, $parameters) {
    $commands = array();

    switch ($command) {
      case 'bgp':
        if (match_ipv4($parameters, false)) {
          $commands[] = 'show bgp ipv4 unicast '.$parameters;
        } else if (match_ipv6($parameters, false)) {
          $commands[] = 'show bgp ipv6 unicast '.$parameters;
        } else {
          throw new Exception('The parameter is not an IPv4/IPv6 address.');
        }
        break;

      case 'as-path-regex':
        if (match_aspath_regex($parameters)) {
          $commands[] = 'show bgp ipv4 unicast quote-regexp "'.$parameters.'"';
          $commands[] = 'show bgp ipv6 unicast quote-regexp "'.$parameters.'"';
        } else {
          throw new Exception('The parameter is not an AS-Path regular expression like ".*XXXX YYYY.*".');
        }
        break;

      case 'as':
        if (match_as($parameters)) {
          $commands[] = 'show bgp ipv4 unicast quote-regexp "^'.$parameters.'_"';
          $commands[] = 'show bgp ipv6 unicast quote-regexp "^'.$parameters.'_"';
        } else {
          throw new Exception('The parameter is not an AS number.');
        }
        break;

      case 'ping':
        if (match_ipv4($parameters)) {
          $commands[] = 'ping '.$parameters.' repeat 10';
        } else if (match_ipv6($parameters)) {
          $commands[] = 'ping ipv6 '.$parameters.' repeat 10';
        } else {
          throw new Exception('The parameter is not an IPv4/IPv6 address.');
        }
        break;

      case 'traceroute':
        if (match_ipv4($parameters)) {
          $commands[] = 'traceroute ip '.$parameters;
        } else if (match_ipv6($parameters)) {
          $commands[] = 'traceroute ipv6 '.$parameters;
        } else {
          throw new Exception('The parameter is not an IPv4/IPv6 address.');
        }
        break;

      default:
        throw new Exception('Command not supported.');
    }

    return $commands;
  }
}

// End of cisco.php
