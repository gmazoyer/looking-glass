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

final class Mikrotik extends Router {
  protected function build_bgp($parameter) {
    if (!is_valid_ip_address($parameter)) {
      throw new Exception('The parameter is not an IP address.');
    }

    $cmd = new CommandBuilder();
    if (match_ipv6($parameter, false)) {
      $cmd->add('ipv6');
    } else if (match_ipv4($parameter, false)) {
      $cmd->add('ip');
    }
    $cmd->add('route print');

    if ($this->config['bgp_detail']) {
      $cmd->add('detail');
    }

    $cmd->add('where', 'dst-address="'.$parameter.'"');

    return array($cmd);
  }

  protected function build_aspath_regexp($parameter) {
    if (!match_aspath_regexp($parameter)) {
      throw new Exception('The parameter is not an AS-Path regular expression.');
    }

    $commands = array();

    if (!$this->config['disable_ipv6']) {
      $cmd6 = new CommandBuilder('ipv6 route print');
      if ($this->config['bgp_detail']) {
        $cmd6->add('detail');
      }
      $commands[] = $cmd6->add('where', 'bgp-as-path="'.$parameter.'"');
    }
    if (!$this->config['disable_ipv4']) {
      $cmd4 = new CommandBuilder('ip route print');
      if ($this->config['bgp_detail']) {
        $cmd4->add('detail');
      }
      $commands[] = $cmd4->add('where', 'bgp-as-path="'.$parameter.'"');
    }

    return $commands;
  }

  protected function build_as($parameter) {
    if (!match_as($parameter)) {
      throw new Exception('The parameter is not an AS number.');
    }

    $commands = array();

    if (!$this->config['disable_ipv6']) {
      $cmd6 = new CommandBuilder('ipv6 route print');
      if ($this->config['bgp_detail']) {
        $cmd6->add('detail');
      }
      $commands[] = $cmd6->add('where', 'bgp-as-path~"'.$parameter.'\$"');
    }
    if (!$this->config['disable_ipv4']) {
      $cmd4 = new CommandBuilder('ip route print');
      if ($this->config['bgp_detail']) {
        $cmd4->add('detail');
      }
      $commands[] = $cmd4->add('where', 'bgp-as-path~"'.$parameter.'\$"');
    }

    return $commands;
  }
}

// End of mikrotik.php
