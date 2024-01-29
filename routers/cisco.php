<?php

/*
 * Looking Glass - An easy to deploy Looking Glass
 * Copyright (C) 2014-2024 Guillaume Mazoyer <guillaume@mazoyer.eu>
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

class Cisco extends Router {
  protected function build_bgp($parameter, $routing_instance = false) {
    $cmd = new CommandBuilder();
    $cmd->add('show bgp');

    if (match_ipv6($parameter, false)) {
      $cmd->add('ipv6');
    }
    if (match_ipv4($parameter, false)) {
      $cmd->add('ipv4');
    }
    $cmd->add('unicast', $parameter);

    return array($cmd);
  }

  protected function build_aspath_regexp($parameter, $routing_instance = false) {
    $parameter = quote($parameter);
    $commands = array();
    $cmd = new CommandBuilder();
    $cmd->add('show bgp');

    if (!$this->config['disable_ipv6']) {
      $commands[] = (clone $cmd)->add('ipv6 unicast quote-regexp', $parameter);
    }
    if (!$this->config['disable_ipv4']) {
      $commands[] = (clone $cmd)->add('ipv4 unicast quote-regexp', $parameter);
    }

    return $commands;
  }

  protected function build_as($parameter, $routing_instance = false) {
    $parameter = '^'.$parameter.'_';
    return $this->build_aspath_regexp($parameter);
  }

  protected function build_ping($parameter, $routing_instance = false) {
    if (!is_valid_destination($parameter)) {
      throw new Exception('The parameter is not an IP address or a hostname.');
    }

    $cmd = new CommandBuilder();
    $cmd->add('ping', $parameter, 'repeat 10');

    if ($this->has_source_interface_id()) {
      $cmd->add('source', $this->get_source_interface_id());
    }

    return array($cmd);
  }

  protected function build_traceroute($parameter, $routing_instance = false) {
    if (!is_valid_destination($parameter)) {
      throw new Exception('The parameter is not an IP address or a hostname.');
    }

    $cmd = new CommandBuilder();
    $cmd->add('traceroute');

    if (match_ipv6($parameter) || match_ipv4($parameter) ||
        !$this->has_source_interface_id()) {
      $cmd->add($parameter);
    } else {
      // Resolve the hostname and go for right IP version
      $hostname = $parameter;
      $parameter = hostname_to_ip_address($hostname);

      if (!$parameter) {
        throw new Exception('No record found for '.$hostname);
      }

      if (match_ipv6($parameter)) {
        $cmd->add('ipv6', (isset($hostname) ? $hostname : $parameter));
      }
      if (match_ipv4($parameter)) {
        $cmd->add('ip', (isset($hostname) ? $hostname : $parameter));
      }
    }

    // Make sure to use the right source interface
    if ($this->has_source_interface_id() && !match_ipv6($parameter)) {
      $cmd->add('source', $this->get_source_interface_id());
    }

    return array($cmd);
  }
}

// End of cisco.php
