<?php

/*
 * Looking Glass - An easy to deploy Looking Glass
 * Copyright (C) 2014-2015 Guillaume Mazoyer <gmazoyer@gravitons.in>
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

final class Bird extends Router {
  protected function build_ping($destination) {
    $ping = null;

    if (match_hostname($destination)) {
      $hostname = $destination;
      $destination = hostname_to_ip_address($hostname);

      if (!$destination) {
        throw new Exception('No A or AAAA record found for '.$hostname);
      }
    }

    if (match_ipv4($destination)) {
      $ping = 'ping '.$this->global_config['tools']['ping_options'].' '.
        (isset($hostname) ? $hostname : $destination);
    } else if (match_ipv6($destination)) {
      $ping = 'ping6 '.$this->global_config['tools']['ping_options'].' '.
        (isset($hostname) ? $hostname : $destination);
    } else {
      throw new Exception('The parameter does not resolve to an IPv4/IPv6 address.');
    }

    if (($ping != null) && $this->has_source_interface_id()) {
      if (match_ipv4($destination) &&
          ($this->get_source_interface_id('ipv4') != null)) {
        $ping .= ' '.$this->global_config['tools']['ping_source_option'].' '.
          $this->get_source_interface_id('ipv4');
      } else if (match_ipv6($destination) &&
          ($this->get_source_interface_id('ipv6') != null)) {
        $ping .= ' '.$this->global_config['tools']['ping_source_option'].' '.
          $this->get_source_interface_id('ipv6');
      }
    }

    return $ping;
  }

  protected function build_traceroute($destination) {
    $traceroute = null;

    if (match_hostname($destination)) {
      $hostname = $destination;
      $destination = hostname_to_ip_address($hostname);

      if (!$destination) {
        throw new Exception('No A or AAAA record found for '.$hostname);
      }
    }

    if (match_ipv4($destination)) {
      $traceroute = $this->global_config['tools']['traceroute4'].' '.
        $this->global_config['tools']['traceroute_options'].' '.
        (isset($hostname) ? $hostname : $destination);
    } else if (match_ipv6($destination)) {
      $traceroute = $this->global_config['tools']['traceroute6'].' '.
        $this->global_config['tools']['traceroute_options'].' '.
        (isset($hostname) ? $hostname : $destination);
    } else {
      throw new Exception('The parameter does not resolve to an IPv4/IPv6 address.');
    }

    if (($traceroute != null) && $this->has_source_interface_id()) {
      if (match_ipv4($destination) &&
          ($this->get_source_interface_id('ipv4') != null)) {
        $traceroute .= ' '.
          $this->global_config['tools']['traceroute_source_option'].' '.
          $this->get_source_interface_id('ipv4');
      } else if (match_ipv6($destination) &&
          ($this->get_source_interface_id('ipv6') != null)) {
        $traceroute .= ' '.
          $this->global_config['tools']['traceroute_source_option'].' '.
          $this->get_source_interface_id('ipv6');
      }
    }

    return $traceroute;
  }

  protected function build_commands($command, $parameter) {
    $commands = array();

    $birdc = 'birdc';
    $birdc6 = 'birdc6';

    switch ($command) {
      case 'bgp':
        if (match_ipv4($parameter, false)) {
          $commands[] = $birdc.' \'show route for '.$parameter.'\'';
        } else if (match_ipv6($parameter, false)) {
          $commands[] = $birdc6.' \'show route for '.$parameter.'\'';
        } else {
          throw new Exception('The parameter is not an IPv4/IPv6 address.');
        }
        break;

      case 'as-path-regex':
        if (match_aspath_regex($parameter)) {
          $commands[] = $birdc.' \'show route where bgp_path ~ [= '.
            $parameter.' =]\'';
          $commands[] = $birdc6.' \'show route where bgp_path ~ [= '.
            $parameter.' =]\'';
        } else {
          throw new Exception('The parameter is not an AS-Path regular expression.');
        }
        break;

      case 'as':
        if (match_as($parameter)) {
          $commands[] = $birdc.' \'show route where bgp_path ~ [= '.
            $parameter.' =]\'';
          $commands[] = $birdc6.' \'show route where bgp_path ~ [= '.
            $parameter.' =]\'';
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

// End of bird.php
