<?php

/*
 * Looking Glass - An easy to deploy Looking Glass
 * Copyright (C) 2014-2019 Guillaume Mazoyer <gmazoyer@gravitons.in>
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

final class Bird extends Router {
  private function get_bird_binary($ipv6 = true) {
    return $ipv6 ? 'birdc6' : 'birdc';
  }

  private function get_aspath($parameter) {
    $commands = array();

    foreach (array(true, false) as $ipv6_enabled) {
      $cmd = new CommandBuilder();
      $cmd->add($this->get_bird_binary($ipv6_enabled));

      // Build the command to send to the binary
      $cmd->add("'show route where bgp_path ~ [=", $parameter, '=]');
      if ($this->config['bgp_detail']) {
        $cmd->add('all');
      }
      $cmd("'");

      $commands[] = $cmd;
    }

    return $commands;
  }

  protected function build_bgp($parameter) {
    if (!is_valid_ip_address($parameter)) {
      throw new Exception('The parameter is not an IP address.');
    }

    $cmd = new CommandBuilder();
    $cmd->add($this->get_bird_binary(match_ipv6($parameter, false)));

    // Build the command to send to the binary
    $cmd->add("'show route for", $parameter);
    if ($this->config['bgp_detail']) {
      $cmd->add('all');
    }
    $cmd("'");

    return array($cmd);
  }

  protected function build_aspath_regexp($parameter) {
    if (!match_aspath_regexp($parameter)) {
      throw new Exception('The parameter is not an AS-Path regular expression.');
    }
    return $this->get_aspath($parameter);
  }

  protected function build_as($parameter) {
    if (!match_as($parameter)) {
      throw new Exception('The parameter is not an AS number.');
    }
    return $this->get_aspath($parameter);
  }

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
      $cmd->add('ping4', $this->global_config['tools']['ping_options'],
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

// End of bird.php
