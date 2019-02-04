<?php

/*
 * Looking Glass - An easy to deploy Looking Glass
 * Copyright (C) 2019 Guillaume Mazoyer <gmazoyer@gravitons.in>
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
  protected function build_ping($destination) {
    // If the destination is a hostname, try to resolve it to an IP address
    if (match_hostname($destination)) {
      $destination = hostname_to_ip_address($destination, $this->config);
      if (!$destination) {
        throw new Exception('No record found for '.$hostname);
      }
    }

    if (!is_valid_ip_address($destination)) {
      throw new Exception('The parameter does not resolve to an IP address.');
    }

    $cmd = new CommandBuilder();

    // Build the command based on the IP address
    if (match_ipv6($destination)) {
      $cmd->add('ping6', $this->global_config['tools']['ping_options'],
                (isset($hostname) ? $hostname : $destination));
    }
    if (match_ipv4($destination)) {
      $cmd->add('ping', $this->global_config['tools']['ping_options'],
                (isset($hostname) ? $hostname : $destination));
    }

    // Add the source interface based on the IP address
    if ($this->has_source_interface_id()) {
      if (match_ipv6($destination) && $this->get_source_interface_id('ipv6')) {
        $cmd->add($this->global_config['tools']['ping_source_option'],
                  $this->get_source_interface_id('ipv6'));
      }
      if (match_ipv4($destination) && $this->get_source_interface_id('ipv4')) {
        $cmd->add($this->global_config['tools']['ping_source_option'],
                  $this->get_source_interface_id('ipv4'));
      }
    }

    return array($cmd);
  }

  protected function build_traceroute($destination) {
    // If the destination is a hostname, try to resolve it to an IP address
    if (match_hostname($destination)) {
      $destination = hostname_to_ip_address($destination, $this->config);
      if (!$destination) {
        throw new Exception('No record found for '.$hostname);
      }
    }

    if (!is_valid_ip_address($destination)) {
      throw new Exception('The parameter does not resolve to an IP address.');
    }

    $cmd = new CommandBuilder();

    // Build the command based on the IP address
    if (match_ipv6($destination)) {
      $cmd->add($this->global_config['tools']['traceroute6'],
                $this->global_config['tools']['traceroute_options'],
                (isset($hostname) ? $hostname : $destination));
    }
    if (match_ipv4($destination)) {
      $cmd->add($this->global_config['tools']['traceroute4'],
                $this->global_config['tools']['traceroute_options'],
                (isset($hostname) ? $hostname : $destination));
    }

    // Add the source interface based on the IP address
    if ($this->has_source_interface_id()) {
      if (match_ipv6($destination) && $this->get_source_interface_id('ipv6')) {
        $cmd->add($this->global_config['tools']['traceroute_source_option'],
                  $this->get_source_interface_id('ipv6'));
      }
      if (match_ipv4($destination) && $this->get_source_interface_id('ipv4')) {
        $cmd->add($this->global_config['tools']['traceroute_source_option'],
                  $this->get_source_interface_id('ipv4'));
      }
    }

    return array($cmd);
  }
}
