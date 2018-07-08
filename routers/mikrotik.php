<?php

/*
 * Looking Glass - An easy to deploy Looking Glass
 * Copyright (C) 2014-2018 Guillaume Mazoyer <gmazoyer@gravitons.in>
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

final class Mikrotik extends Router {
  protected function build_ping($destination) {
    $ping = null;

    if (match_ipv4($destination) || match_ipv6($destination)) {
      $ping = 'ping count=10 address="'.$destination.'"';
    } else if (match_hostname($destination)) {
      $ping = 'ping count=10 address=[:resolv "'.$destination.'"]';
    } else {
      throw new Exception('The parameter is not an IP address or a hostname.');
    }

    if (($ping != null) && $this->has_source_interface_id()) {
      $ping .= ' interface='.$this->get_source_interface_id();
    }

    return $ping;
  }

  protected function build_traceroute($destination) {
    $traceroute = null;

    if (match_ipv4($destination) || match_ipv6($destination)) {
      $traceroute = 'tool traceroute count=1 use-dns=yes address="'.$destination.'"';
    } else if (match_hostname($destination)) {
      $traceroute = 'tool traceroute count=1 use-dns=yes address=[:resolv "'.$destination.'"]';
    } else {
      throw new Exception('The parameter is not an IP address or a hostname.');
    }

    if (($traceroute != null) && $this->has_source_interface_id()) {
      $traceroute .= ' interface='.$this->get_source_interface_id();
    }

    return $traceroute;
  }

  protected function build_commands($command, $parameter) {
    $commands = array();

    if ($this->config['bgp_detail']) {
      $bgpdetail = ' detail';
    } else {
      $bgpdetail = '';
    }

    switch ($command) {
      case 'bgp':
        if (match_ipv6($parameter, false)) {
          $commands[] = 'ipv6 route print '.$bgpdetail.' where dst-address="'.$parameter.'"';
        } else if (match_ipv4($parameter, false)) {
          $commands[] = 'ip route print '.$bgpdetail.' where dst-address="'.$parameter.'"';
        } else {
          throw new Exception('The parameter is not an IP address.');
        }
        break;

      case 'as-path-regex':
        if (match_aspath_regex($parameter)) {
          if (!$this->config['disable_ipv6']) {
            $commands[] = 'ipv6 route print '.$bgpdetail.' where bgp-as-path="'.$parameter.'"';
          }
          if (!$this->config['disable_ipv4']) {
            $commands[] = 'ip route print '.$bgpdetail.' where bgp-as-path="'.$parameter.'"';
          }
        } else {
          throw new Exception('The parameter is not an AS-Path regular expression.');
        }
        break;

      case 'as':
        if (match_as($parameter)) {
          if (!$this->config['disable_ipv6']) {
            $commands[] = 'ipv6 route print '.$bgpdetail.' where bgp-as-path="'.$parameter.'"';
          }
          if (!$this->config['disable_ipv4']) {
            $commands[] = 'ip route print '.$bgpdetail.' where bgp-as-path="'.$parameter.'"';
          }
        } else {
          throw new Exception('The parameter is not an AS number.');
        }
        break;

      case 'ping':
        try {
          $commands[] = $this->build_ping($parameter);
        } catch (Exception $e) {
          throw $e;
        }
        break;

      case 'traceroute':
        try {
          $commands[] = $this->build_traceroute($parameter);
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

// End of mikrotik.php
