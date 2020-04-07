<?php

/*
 * Looking Glass - An easy to deploy Looking Glass
 * Copyright (C) 2014-2020 Guillaume Mazoyer <gmazoyer@gravitons.in>
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
 *
 *
 * Implementacao Huawei VRP by Alexandre J. Correa <ajcorrea@gmail.com> - 06/04/2020  
*/

require_once('router.php');
require_once('includes/command_builder.php');
require_once('includes/utils.php');

class Huawei extends Router {
  protected function build_bgp($parameter) {
    $cmd = new CommandBuilder();
    $cmd->add('display');

    if (match_ipv6($parameter, false)) {
      $cmd->add('ipv6');
    }
    if (match_ipv4($parameter, false)) {
      $cmd->add('ip');
    }
    $cmd->add('routing-table', $parameter);

    return array($cmd);
  }

  protected function build_aspath_regexp($parameter) {
    //$parameter = quote($parameter);
    $commands = array();
    $cmd = new CommandBuilder();
    $cmd->add('display bgp');

    if (!$this->config['disable_ipv6']) {
      $commands[] = (clone $cmd)->add('ipv6 routing-table regular-expression', $parameter);
    }
    if (!$this->config['disable_ipv4']) {
      $commands[] = (clone $cmd)->add('routing-table regular-expression', $parameter);
    }

    return $commands;
  }

  protected function build_as($parameter) {
    //$parameter = '^'.$parameter.'_';
    //return $this->build_aspath_regexp($parameter);
    throw new Exception('Coomand not supported.');
  }

  protected function build_ping($parameter) {
    if (!is_valid_destination($parameter)) {
      throw new Exception('The parameter is not an IP address or a hostname.');
    }

    $cmd = new CommandBuilder();
    $cmd->add('ping -c 10 ');

    if ($this->has_source_interface_id()) {
      $cmd->add('-a ', $this->get_source_interface_id('ipv4'));
    }
    $cmd->add($parameter);

    return array($cmd);
  }

  protected function build_traceroute($parameter) {
    if (!is_valid_destination($parameter)) {
      throw new Exception('The parameter is not an IP address or a hostname.');
    }

    $cmd = new CommandBuilder();
    $cmd->add('tracert');
    if ($this->has_source_interface_id() && !match_ipv6($parameter)) {
      $cmd->add('-a ', $this->get_source_interface_id('ipv4'));
    }
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
        $cmd->add((isset($hostname) ? $hostname : $parameter));
      }
    }

    // Make sure to use the right source interface

    return array($cmd);
  }
}

// End of huawei.php
