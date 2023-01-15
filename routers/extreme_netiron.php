<?php

/*
 * Looking Glass - An easy to deploy Looking Glass
 * Copyright (C) 2017-2023 Guillaume Mazoyer <guillaume@mazoyer.eu>
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

  protected function build_bgp($parameter) {
    $cmd = new CommandBuilder();
    $cmd->add(self::$wrapper.'show');

    if (match_ipv6($parameter, false)) {
      $cmd->add('ipv6');
    }
    if (match_ipv4($parameter, false)) {
      $cmd->add('ip');
    }
    $cmd->add('bgp routes');

    if ($this->config['bgp_detail']) {
      $cmd->add('detail');
    }

    $cmd->add($parameter);

    return array($cmd);
  }

  protected function build_aspath_regexp($parameter) {
    $commands = array();
    $cmd = new CommandBuilder();
    $cmd->add(self::$wrapper.'show');

    if (!$this->config['disable_ipv6']) {
      $cmd6 = clone $cmd;
      $cmd6->add('ipv6 bgp routes');

      if ($this->config['bgp_detail']) {
        $cmd6->add('detail');
      }

      $commands[] = $cmd6->add('regular-expression', quote($parameter));
    }
    if (!$this->config['disable_ipv4']) {
      $cmd4 = clone $cmd;
      $cmd4->add('ip bgp routes');

      if ($this->config['bgp_detail']) {
        $cmd4->add('detail');
      }

      $commands[] = $cmd4->add('regular-expression', quote($parameter));
    }

    return $commands;
  }

  protected function build_as($parameter) {
    $parameter = '^'.$parameter.'_';
    return $this->build_aspath_regexp($parameter);
  }

  protected function build_ping($parameter) {
    if (!is_valid_destination($parameter)) {
      throw new Exception('The parameter is not an IP address or a hostname.');
    }

    $cmd = new CommandBuilder();
    $cmd->add('ping');

    if (match_hostname($parameter)) {
      $hostname = $parameter;
      $parameter = hostname_to_ip_address($hostname, $this->config);

      if (!$parameter) {
        throw new Exception('No record found for '.$hostname);
      }

      if (match_ipv6($parameter)) {
        $cmd->add('ipv6');
      }
      $cmd->add(isset($hostname) ? $hostname : $parameter);
    } else {
      if (match_ipv6($parameter)) {
        $cmd->add('ipv6');
      }
      $cmd->add($parameter);
    }

    if ($this->has_source_interface_id()) {
      $cmd->add('source');

      if (match_ipv6($parameter) && $this->get_source_interface_id('ipv6')) {
        $cmd->add($this->get_source_interface_id('ipv6'));
      }
      if (match_ipv4($parameter) && $this->get_source_interface_id('ipv4')) {
        $cmd->add($this->get_source_interface_id('ipv4'));
      }
    }

    return array($cmd);
  }

  protected function build_traceroute($parameter) {
    if (!is_valid_destination($parameter)) {
      throw new Exception('The parameter is not an IP address or a hostname.');
    }

    $cmd = new CommandBuilder();
    $cmd->add('traceroute');

    if (match_hostname($parameter)) {
      $hostname = $parameter;
      $parameter = hostname_to_ip_address($hostname, $this->config);

      if (!$parameter) {
        throw new Exception('No record found for '.$hostname);
      }

      if (match_ipv6($parameter)) {
        $cmd->add('ipv6');
      }
      $cmd->add(isset($hostname) ? $hostname : $parameter);
    } else {
      if (match_ipv6($parameter)) {
        $cmd->add('ipv6');
      }
      $cmd->add($parameter);
    }

    if ($this->has_source_interface_id()) {
      $cmd->add('source');

      if (match_ipv6($parameter) && $this->get_source_interface_id('ipv6')) {
        $cmd->add($this->get_source_interface_id('ipv6'));
      }
      if (match_ipv4($parameter) && $this->get_source_interface_id('ipv4')) {
        $cmd->add($this->get_source_interface_id('ipv4'));
      }
    }

    return array($cmd);
  }
}

// End of extreme_netiron.php
