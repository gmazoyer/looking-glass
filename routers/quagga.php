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

require_once('unix.php');
require_once('includes/command_builder.php');
require_once('includes/utils.php');

class Quagga extends UNIX {
  protected static $wrapper = 'vtysh -c';

  protected function build_bgp($parameter, $vrf = false) {
    $cmd = new CommandBuilder();
    // vytsh commands need to be quoted
    $cmd->add(self::$wrapper, '"', 'show');

    if (match_ipv6($parameter, false)) {
      $cmd->add('ipv6');
    }
    if (match_ipv4($parameter, false)) {
      $cmd->add('ipv4');
    }
    $cmd->add('bgp unicast', $parameter, '"');

    return array($cmd);
  }

  protected function build_aspath_regexp($parameter, $vrf = false) {
    $commands = array();
    $cmd = new CommandBuilder();
    // vytsh commands need to be quoted
    $cmd->add(self::$wrapper, '"', 'show');

    if (!$this->config['disable_ipv6']) {
      $commands[] = (clone $cmd)->add('ipv6 bgp regexp', $parameter, '"');
    }
    if (!$this->config['disable_ipv4']) {
      $commands[] = (clone $cmd)->add('ip bgp regexp', $parameter, '"');
    }

    return $commands;
  }

  protected function build_as($parameter, $vrf = false) {
    $parameter = '^'.$parameter.'_';
    return $this->build_aspath_regexp($parameter, $vrf);
  }
}

// End of quagga.php
