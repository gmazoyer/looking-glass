<?php

/*
 * Looking Glass - An easy to deploy Looking Glass
 * Copyright (C) 2017 Guillaume Mazoyer <gmazoyer@gravitons.in>
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

final class IOSXR extends Router {
  protected function build_ping($destination) {
    $ping = null;

    if (match_ipv6($destination) || match_ipv4($destination) ||
        (match_hostname($destination) && !$this->has_source_interface_id())) {
      $ping = 'ping '.$destination. ' repeat 10';
    } else if (match_hostname($destination)) {
      $hostname = $destination;
      $destination = hostname_to_ip_address($hostname, $this->config);

      if (!$destination) {
        throw new Exception('No record found for '.$hostname);
      }

      if (match_ipv6($destination)) {
        $ping = 'ping ipv6 '.(isset($hostname) ? $hostname : $destination).
          ' repeat 10';
      } else if (match_ipv4($destination)) {
        $ping = 'ping ipv4 '.(isset($hostname) ? $hostname : $destination).
          ' repeat 10';
      } else {
        throw new Exception('The parameter does not resolve to an IP address.');
      }
    } else {
      throw new Exception('The parameter is not an IP address or a hostname.');
    }

    if (($ping != null) && $this->has_source_interface_id()) {
      if (match_ipv6($destination) &&
          ($this->get_source_interface_id('ipv6') != null)) {
        $ping .= ' source '.$this->get_source_interface_id('ipv6');
      } else if (match_ipv4($destination) &&
          ($this->get_source_interface_id('ipv4') != null)) {
        $ping .= ' source '.$this->get_source_interface_id('ipv4');
      }
    }

    return $ping;
  }

  protected function build_traceroute($destination) {
    $traceroute = null;

    if (match_ipv6($destination) || match_ipv4($destination) ||
        (match_hostname($destination) && !$this->has_source_interface_id())) {
      $traceroute = 'traceroute '.$destination;
    } else if (match_hostname($destination)) {
      $hostname = $destination;
      $destination = hostname_to_ip_address($hostname);

      if (!$destination) {
        throw new Exception('No record found for '.$hostname);
      }

      if (match_ipv6($destination)) {
        $traceroute = 'traceroute ipv6 '.(isset($hostname) ? $hostname : $destination);
      } else if (match_ipv4($destination)) {
        $traceroute = 'traceroute ipv4 '.(isset($hostname) ? $hostname : $destination);
      } else {
        throw new Exception('The parameter does not resolve to an IP address.');
      }
    } else {
      throw new Exception('The parameter is not an IP address or a hostname.');
    }

    if (($traceroute != null) && $this->has_source_interface_id()) {
      if (match_ipv6($destination) &&
          ($this->get_source_interface_id('ipv6') != null)) {
        $traceroute .= ' source '.$this->get_source_interface_id('ipv6');
      } else if (match_ipv4($destination) &&
          ($this->get_source_interface_id('ipv4') != null)) {
        $traceroute .= ' source '.$this->get_source_interface_id('ipv4');
      }
    }

    return $traceroute;
  }

  protected function build_commands($command, $parameter) {
    $commands = array();

    switch ($command) {
      case 'bgp':
        if (match_ipv6($parameter, false)) {
          $commands[] = 'show bgp ipv6 unicast '.$parameter;
        } else if (match_ipv4($parameter, false)) {
          $commands[] = 'show bgp ipv4 unicast '.$parameter;
        } else {
          throw new Exception('The parameter is not an IP address.');
        }
        break;

      case 'as-path-regex':
        if (match_aspath_regex($parameter)) {
          if (!$this->config['disable_ipv6']) {
            $commands[] = 'show bgp ipv6 unicast regexp "'.$parameter.
              '"';
          }
          if (!$this->config['disable_ipv4']) {
            $commands[] = 'show bgp ipv4 unicast regexp "'.$parameter.
              '"';
          }
        } else {
          throw new Exception('The parameter is not an AS-Path regular expression.');
        }
        break;

      case 'as':
        if (match_as($parameter)) {
          if (!$this->config['disable_ipv6']) {
            $commands[] = 'show bgp ipv6 unicast regexp "^'.$parameter.
              '_"';
          }
          if (!$this->config['disable_ipv4']) {
            $commands[] = 'show bgp ipv4 unicast regexp "^'.$parameter.
              '_"';
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

// End of cisco_iosxr.php
