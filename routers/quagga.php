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

final class Quagga extends Router {
  protected function build_ping($destination) {
    $ping = null;

    if (match_fqdn($destination)) {
      $fqdn = $destination;
      $destination = fqdn_to_ip_address($fqdn);

      if (!$destination) {
        throw new Exception('No A or AAAA record found for '.$fqdn);
      }
    }

    if (match_ipv4($destination)) {
      $ping = 'ping -A -c 10 '.(isset($fqdn) ? $fqdn : $destination);
    } else if (match_ipv6($destination)) {
      $ping = 'ping6 -A -c 10 '.(isset($fqdn) ? $fqdn : $destination);
    } else {
      throw new Exception('The parameter does not resolve to an IPv4/IPv6 address.');
    }

    if (($ping != null) && $this->has_source_interface_id()) {
      if (match_ipv4($destination) &&
          ($this->get_source_interface_id('ipv4') != null)) {
        $ping .= ' -I '.$this->get_source_interface_id('ipv4');
      } else if (match_ipv6($destination) &&
          ($this->get_source_interface_id('ipv6') != null)) {
        $ping .= ' -I '.$this->get_source_interface_id('ipv6');
      }
    }

    return $ping;
  }

  protected function build_traceroute($destination) {
    $traceroute = null;

    if (match_fqdn($destination)) {
      $fqdn = $destination;
      $destination = fqdn_to_ip_address($fqdn);

      if (!$destination) {
        throw new Exception('No A or AAAA record found for '.$fqdn);
      }
    }

    if (match_ipv4($destination)) {
      $traceroute = 'traceroute -4 -A -q1 -N32 -w1 -m15 '.
        (isset($fqdn) ? $fqdn : $destination);
    } else if (match_ipv6($destination)) {
      $traceroute = 'traceroute -6 -A -q1 -N32 -w1 -m15 '.
        (isset($fqdn) ? $fqdn : $destination);
    } else {
      throw new Exception('The parameter does not resolve to an IPv4/IPv6 address.');
    }

    if (($traceroute != null) && $this->has_source_interface_id()) {
      if (match_ipv4($destination) &&
          ($this->get_source_interface_id('ipv4') != null)) {
        $traceroute .= ' -s '.$this->get_source_interface_id('ipv4');
      } else if (match_ipv6($destination) &&
          ($this->get_source_interface_id('ipv6') != null)) {
        $traceroute .= ' -s '.$this->get_source_interface_id('ipv6');
      }
    }

    return $traceroute;
  }

  protected function build_commands($command, $parameters) {
    $commands = array();

    $vtysh = 'vtysh -c "';

    switch ($command) {
      case 'bgp':
        if (match_ipv4($parameters, false)) {
          $commands[] = $vtysh.'show bgp ipv4 unicast '.$parameters.'"';
        } else if (match_ipv6($parameters, false)) {
          $commands[] = $vtysh.'show bgp ipv6 unicast '.$parameters.'"';
        } else {
          throw new Exception('The parameter is not an IPv4/IPv6 address.');
        }
        break;

      case 'as-path-regex':
        if (match_aspath_regex($parameters)) {
          $commands[] = $vtysh.'show ip bgp regexp '.$parameters.'"';
          $commands[] = $vtysh.'show ipv6 bgp regexp '.$parameters.'"';
        } else {
          throw new Exception('The parameter is not an AS-Path regular expression.');
        }
        break;

      case 'as':
        if (match_as($parameters)) {
          $commands[] = $vtysh.'show ip bgp regexp ^'.$parameters.'_'.'"';
          $commands[] = $vtysh.'show ipv6 bgp regexp ^'.$parameters.'_'.'"';
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

// End of quagga.php
