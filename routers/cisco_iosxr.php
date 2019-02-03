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

require_once('cisco.php');
require_once('includes/command_builder.php');
require_once('includes/utils.php');

final class IOSXR extends Cisco {
  protected function build_ping($parameter) {
    if (!is_valid_destination($parameter)) {
      throw new Exception('The parameter is not an IP address or a hostname.');
    }

    $cmd = new CommandBuilder('ping');
    if (match_ipv6($parameter) || match_ipv4($parameter) ||
        !$this->has_source_interface_id()) {
      $cmd->add($parameter);
    } else {
      // Resolve the hostname and go for right IP version
      $hostname = $parameter;
      $parameter = hostname_to_ip_address($hostname, $this->config);

      if (!$parameter) {
        throw new Exception('No record found for '.$hostname);
      }

      if (match_ipv6($parameter)) {
        $cmd->add('ipv6', (isset($hostname) ? $hostname : $parameter));
      }
      if (match_ipv4($parameter)) {
        $cmd->add('ipv4', (isset($hostname) ? $hostname : $parameter));
      }
    }

    $cmd->add('repeat 10');

    // Make sure to use the right source interface
    if ($this->has_source_interface_id()) {
      if (match_ipv6($parameter) && $this->get_source_interface_id('ipv6')) {
        $cmd->add('source', $this->get_source_interface_id('ipv6'));
      }
      if (match_ipv4($parameter) && $this->get_source_interface_id('ipv4')) {
        $cmd->add('source', $this->get_source_interface_id('ipv4'));
      }
    }

    return array($cmd);
  }

  protected function build_traceroute($destination) {
    if (!is_valid_destination($parameter)) {
      throw new Exception('The parameter is not an IP address or a hostname.');
    }

    $cmd = new CommandBuilder('traceroute');
    if (match_ipv6($destination) || match_ipv4($destination) ||
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
        $cmd->add('ipv4', (isset($hostname) ? $hostname : $parameter));
      }
    }

    // Make sure to use the right source interface
    if ($this->has_source_interface_id()) {
      if (match_ipv6($parameter) && $this->get_source_interface_id('ipv6')) {
        $cmd->add('source', $this->get_source_interface_id('ipv6'));
      }
      if (match_ipv4($parameter) && $this->get_source_interface_id('ipv4')) {
        $cmd->add('source', $this->get_source_interface_id('ipv4'));
      }
    }

    return array($cmd);
  }
}

// End of cisco_iosxr.php
