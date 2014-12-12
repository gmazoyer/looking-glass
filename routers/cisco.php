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

require_once('router.php');
require_once('includes/utils.php');

final class Cisco extends Router {
  protected function build_ping($destination) {
    $ping = null;

    if (match_ipv4($destination) || match_ipv6($destination) ||
        match_fqdn($destination)) {
      $ping = 'ping '.$destination.' repeat 10';
    } else {
      throw new Exception('The parameter is not an IPv4/IPv6 address or a FQDN.');
    }

    if (($ping != null) && $this->has_source_interface_id()) {
      $ping .= ' source '.$this->get_source_interface_id();
    }

    return $ping;
  }

  protected function build_traceroute($destination) {
    $traceroute = null;

    if (match_ipv4($destination) || match_ipv6($destination) ||
        (match_fqdn($destination) && !$this->has_source_interface_id())) {
      $traceroute = 'traceroute '.$destination;
    } else if (match_fqdn($destination)) {
      $fqdn = $destination;
      $destination = fqdn_to_ip_address($fqdn);

      if (!$destination) {
        throw new Exception('No A or AAAA record found for '.$fqdn);
      }

      if (match_ipv4($destination)) {
        $traceroute = 'traceroute ip '.$fqdn;
      } else if (match_ipv6($destination)) {
        $traceroute = 'traceroute ipv6 '.$fqdn;
      } else {
        throw new Exception('The parameter does not resolve to an IPv4/IPv6 address.');
      }
    } else {
      throw new Exception('The parameter is not an IPv4/IPv6 address or a FQDN.');
    }

    if (($traceroute != null) && $this->has_source_interface_id()) {
      $traceroute .= ' source '.$this->get_source_interface_id();
    }

    return $traceroute;
  }

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
          throw new Exception('The parameter is not an AS-Path regular expression.');
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
        try {
          $commands[] = $this->build_ping($parameters);
        } catch (Exception $e) {
          throw $e;
        }
        break;

      case 'traceroute':
        try {
          $commands[] = $this->build_traceroute($parameters);
        } catch (Exception $e) {
          throw $e;
        }
        break;

      default:
        throw new Exception('Command not supported.');
    }

    return $commands;
  }
}

// End of cisco.php
