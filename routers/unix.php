<?php

/*
 * Looking Glass - An easy to deploy Looking Glass
 * Copyright (C) 2019-2024 Guillaume Mazoyer <guillaume@mazoyer.eu>
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

/**
 * This class is used to define common behavior between UNIX based routers.
 *
 * The name of this class is not the best one since a lot of network OS are
 * based on UNIX anyway but Linux would have been a little bit to specific, and
 * wrong.
 */
abstract class UNIX extends Router {
  protected function build_ping($parameter, $routing_instance = false) {
    // If the destination is a hostname, try to resolve it to an IP address
    if (match_hostname($parameter)) {
      $hostname = $parameter;
      $parameter = hostname_to_ip_address($parameter, $this->config);
      if (!$parameter) {
        throw new Exception('No record found for '.$hostname);
      }
    }

    if (!is_valid_ip_address($parameter)) {
      throw new Exception('The parameter does not resolve to an IP address.');
    }

    // Choose ping binary based on the IP family (IPv6 or IPv4)
    $cmd = new CommandBuilder();
    $cmd->add(match_ipv6($parameter) ? 'ping6' : 'ping');

    // Add the source interface based on the IP address
    if ($this->has_source_interface_id()) {
      if (match_ipv6($parameter) && $this->get_source_interface_id('ipv6')) {
        $cmd->add($this->global_config['tools']['ping_source_option'],
                  $this->get_source_interface_id('ipv6'));
      }
      if (match_ipv4($parameter) && $this->get_source_interface_id('ipv4')) {
        $cmd->add($this->global_config['tools']['ping_source_option'],
                  $this->get_source_interface_id('ipv4'));
      }
    }

    $cmd->add($this->global_config['tools']['ping_options'],
              (isset($hostname) ? $hostname : $parameter));

    return array($cmd);
  }

  protected function build_traceroute($parameter, $routing_instance = false) {
    // If the destination is a hostname, try to resolve it to an IP address
    if (match_hostname($parameter)) {
      $hostname = $parameter;
      $parameter = hostname_to_ip_address($parameter, $this->config);
      if (!$parameter) {
        throw new Exception('No record found for '.$hostname);
      }
    }

    if (!is_valid_ip_address($parameter)) {
      throw new Exception('The parameter does not resolve to an IP address.');
    }

    $cmd = new CommandBuilder();

    // Build the command based on the IP address
    if (match_ipv6($parameter)) {
      $cmd->add($this->global_config['tools']['traceroute6'],
                $this->global_config['tools']['traceroute_options'],
                (isset($hostname) ? $hostname : $parameter));
    }
    if (match_ipv4($parameter)) {
      $cmd->add($this->global_config['tools']['traceroute4'],
                $this->global_config['tools']['traceroute_options'],
                (isset($hostname) ? $hostname : $parameter));
    }

    // Add the source interface based on the IP address
    if ($this->has_source_interface_id()) {
      if (match_ipv6($parameter) && $this->get_source_interface_id('ipv6')) {
        $cmd->add($this->global_config['tools']['traceroute_source_option'],
                  $this->get_source_interface_id('ipv6'));
      }
      if (match_ipv4($parameter) && $this->get_source_interface_id('ipv4')) {
        $cmd->add($this->global_config['tools']['traceroute_source_option'],
                  $this->get_source_interface_id('ipv4'));
      }
    }

    return array($cmd);
  }
}
