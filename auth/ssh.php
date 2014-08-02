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

require_once 'Crypt/RSA.php';
require_once 'Net/SSH2.php';
require_once 'authentication.php';

final class SSH extends Authentication {
  private $port;

  public function __construct($config) {
    parent::__construct($config);

    $this->port = isset($this->config['port']) ? (int) $this->config['port'] : 22;
  }

  protected function check_config() {
    if ($this->config['auth'] == 'ssh-password') {
      if (!isset($this->config['user']) || !isset($this->config['pass'])) {
        throw new Exception('User and password required for ssh-password.');
      }
    }

    if ($this->config['auth'] == 'ssh-key') {
      if (!isset($this->config['user']) || !isset($this->config['private_key'])) {
        throw new Exception('User and private key required for ssh-key.');
      }
    }
  }

  public function connect() {
    $this->connection = new Net_SSH2($this->config['host'], $this->port);
    $success = false;

    if ($this->config['auth'] == 'ssh-password') {
      $success = $this->connection->login($this->config['user'], $this->config['pass']);
    } else if ($this->config['auth'] == 'ssh-key') {
      $key = new Crypt_RSA();
      $key->loadKey(file_get_contents($this->config['private_key']));

      if (isset($this->config['pass'])) {
        $key->setPassword($this->config['pass']);
      }

      $success = $this->connection->login($this->config['user'], $key);
    } else {
      throw new Exception('Unknown type of connection for SSH.');
    }

    if (!$success) {
      throw new Exception('SSH authentication failed.');
    }
  }

  public function send_command($command) {
    if ($this->connection == null) {
      $this->connect();
    }

    $data = $this->connection->exec($command);

    return $data;
  }

  public function disconnect() {
    $this->send_command('exit');
    $this->connection = null;
  }
}

// End of ssh.php
