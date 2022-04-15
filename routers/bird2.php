<?php

/*
 * Looking Glass - An easy to deploy Looking Glass
 * Copyright (C) 2014-2022 Guillaume Mazoyer <guillaume@mazoyer.eu>
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

final class Bird2 extends UNIX {
  private function get_aspath($parameter) {
    $cmd = new CommandBuilder();
    $cmd->add('birdc');

    // Build the command to send to the binary
    $cmd->add("'show route where bgp_path ~ [=", $parameter, '=]');
    if ($this->config['bgp_detail']) {
      $cmd->add('all');
    }
    $cmd->add("'");

    return array($cmd);
  }

  protected function build_bgp($parameter) {
    $cmd = new CommandBuilder();
    $cmd->add('birdc');

    // Build the command to send to the binary
    $cmd->add("'show route for", $parameter);
    if ($this->config['bgp_detail']) {
      $cmd->add('all');
    }
    $cmd->add("'");

    return array($cmd);
  }

  protected function build_aspath_regexp($parameter) {
    return $this->get_aspath($parameter);
  }

  protected function build_as($parameter) {
    return $this->get_aspath($parameter);
  }
}

// End of bird2.php
