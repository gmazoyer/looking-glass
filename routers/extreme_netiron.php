<?php

/*
 * Looking Glass - An easy to deploy Looking Glass
 * Copyright (C) 2017-2019 Guillaume Mazoyer <gmazoyer@gravitons.in>
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
require_once('includes/command_builder.php');
require_once('includes/utils.php');

final class ExtremeNetIron extends Router {
  private static $wrapper = "skip-page-display\r\n";

  protected function build_ping($parameter) {
    if (!is_valid_destination($parameter)) {
      throw new Exception('The parameter is not an IP address or a hostname.');
    }

    $cmd = new CommandBuilder('ping');
    if (match_hostname($parameter)) {
      $hostname = $parameter;
      $parameter = hostname_to_ip_address($hostname, $this->config);

      if (!$parameter) {
        throw new Exception('No record found for '.$hostname);
      }

      if (match_ipv6($destination)) {
        $cmd->add('ipv6');
      }
      $cmd->add(isset($hostname) ? $hostname : $parameter);
    } else {
      if (match_ipv6($destination)) {
        $cmd->add('ipv6');
      }
      $cmd->add($parameter);
    }

    if ($this->has_source_interface_id()) {
      $cmd->add('source');

      if (match_ipv6($destination) && $this->get_source_interface_id('ipv6')) {
        $cmd->add($this->get_source_interface_id('ipv6'));
      }
      if (match_ipv4($destination) && $this->get_source_interface_id('ipv4')) {
        $cmd->add($this->get_source_interface_id('ipv4'));
      }
    }

    return array($cmd);
  }

  protected function build_traceroute($destination) {
    if (!is_valid_destination($parameter)) {
      throw new Exception('The parameter is not an IP address or a hostname.');
    }

    $cmd = new CommandBuilder('traceroute');
    if (match_hostname($parameter)) {
      $hostname = $parameter;
      $parameter = hostname_to_ip_address($hostname, $this->config);

      if (!$parameter) {
        throw new Exception('No record found for '.$hostname);
      }

      if (match_ipv6($destination)) {
        $cmd->add('ipv6');
      }
      $cmd->add(isset($hostname) ? $hostname : $parameter);
    } else {
      if (match_ipv6($destination)) {
        $cmd->add('ipv6');
      }
      $cmd->add($parameter);
    }

    if ($this->has_source_interface_id()) {
      $cmd->add('source');

      if (match_ipv6($destination) && $this->get_source_interface_id('ipv6')) {
        $cmd->add($this->get_source_interface_id('ipv6'));
      }
      if (match_ipv4($destination) && $this->get_source_interface_id('ipv4')) {
        $cmd->add($this->get_source_interface_id('ipv4'));
      }
    }

    return array($cmd);
  }

  protected function build_commands($command, $parameter) {
    $commands = array();

    if ($this->config['bgp_detail']) {
      $bgpdetail = 'detail ';
    } else {
      $bgpdetail = '';
    }

    switch ($command) {
      case 'bgp':
        if (match_ipv6($parameter, false)) {
          $commands[] = "skip-page-display\r\nshow ipv6 bgp routes ".$bgpdetail.$parameter;
        } else if (match_ipv4($parameter, false)) {
          $commands[] = "skip-page-display\r\nshow ip bgp routes ".$bgpdetail.$parameter;
        } else {
          throw new Exception('The parameter is not an IP address.');
        }
        break;

      case 'as-path-regex':
        if (match_aspath_regexp($parameter)) {
          if (!$this->config['disable_ipv6']) {
            $commands[] = "skip-page-display\r\nshow ipv6 bgp routes ".$bgpdetail."regular-expression \"".$parameter.
              '"';
          }
          if (!$this->config['disable_ipv4']) {
            $commands[] = "skip-page-display\r\nshow ip bgp routes ".$bgpdetail."regular-expression \"".$parameter.
              '"';
          }
        } else {
          throw new Exception('The parameter is not an AS-Path regular expression.');
        }
        break;

      case 'as':
        if (match_as($parameter)) {
          if (!$this->config['disable_ipv6']) {
            $commands[] = "skip-page-display\r\nshow ipv6 bgp routes ".$bgpdetail."regular-expression \"^".$parameter.
              '_"';
          }
          if (!$this->config['disable_ipv4']) {
            $commands[] = "skip-page-display\r\nshow ip bgp routes ".$bgpdetail."regular-expression \"^".$parameter.
              '_"';
          }
        } else {
          throw new Exception('The parameter is not an AS number.');
        }
        break;
    }

    return $commands;
  }
}

// End of extreme_netiron.php
