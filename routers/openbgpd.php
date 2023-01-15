<?php

/*
 * Looking Glass - An easy to deploy Looking Glass
 * Copyright (C) 2017-2023 Guillaume Mazoyer <guillaume@mazoyer.eu>
 * Copyright (C) 2017-2021 Denis Fondras <github@ggl.ledeuns.net>
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

final class OpenBGPD extends UNIX {

  public function __construct($global_config, $config, $id, $requester) {
    parent::__construct($global_config, $config, $id, $requester);

    // Check if we need sudo or dosu
    if (isset($this->config['become_method']) && $this->config['become_method'] == 'doas') {
      $this->wrapper = 'doas bgpctl';
    } elseif (isset($this->config['become_method']) && $this->config['become_method'] == 'sudo') {
      $this->wrapper = 'sudo bgpctl';
    } else {
      $this->wrapper = 'bgpctl';
    }
  }

  protected function build_bgp($parameter) {
    $cmd = new CommandBuilder();
    $cmd->add($this->wrapper, 'show rib');

    if ($this->config['bgp_detail']) {
      $cmd->add('detail');
    }
    $cmd->add($parameter);

    return array($cmd);
  }

  protected function build_aspath_regexp($parameter) {
    $cmd = new CommandBuilder();
    $cmd->add($this->wrapper, 'show rib');

    if ($this->config['bgp_detail']) {
      $cmd->add('detail');
    }
    $cmd->add('as', $parameter);

    return array($cmd);
  }

  protected function build_as($parameter) {
    $cmd = new CommandBuilder();
    $cmd->add($this->wrapper, 'show rib');

    if ($this->config['bgp_detail']) {
      $cmd->add('detail');
    }
    $cmd->add('peer-as', $parameter);

    return array($cmd);
  }
}

// End of openbgpd.php
