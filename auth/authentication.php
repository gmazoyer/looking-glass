<?php

/*
 * Looking Glass - An easy to deploy Looking Glass
 * Copyright (C) 2014 Guillaume Mazoyer <gmazoyer@gravitons.in>
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

ini_set('include_path', ini_get('include_path').':./phpseclib-0.3.7');

require_once 'ssh.php';
require_once 'telnet.php';

abstract class Authentication {
  protected $config;

  public function __construct($config) {
    $this->config = $config;
    $this->check_config();
  }

  protected abstract function check_config();
  public abstract function connect();
  public abstract function disconnect();
  public abstract function send_command($command);

  public function __destruct() {
    $this->disconnect();
  }

  public static final function instance($config) {
    switch ($config['auth']) {
      case 'ssh-password':
      case 'ssh-key':
        return new SSH($config);

      case 'telnet':
        return new Telnet($config);

      default:
        print 'Unknown authentication mecanism "'.$config['auth'].'"."';
        return null;
    }
  }
}

// End of authentication.php
