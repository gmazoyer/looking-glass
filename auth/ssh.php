<?php

/*
 * Looking Glass - An easy to deploy Looking Glass
 * Copyright (C) 2014-2017 Guillaume Mazoyer <gmazoyer@gravitons.in>
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

require_once('Crypt/RSA.php');
require_once('Net/SSH2.php');
require_once('authentication.php');

final class SSH extends Authentication {
  private $port;

  public function __construct($config) {
    parent::__construct($config);

    $this->port = isset($this->config['port']) ? (int) $this->config['port'] : 22;
  }

  protected function check_config() {
    if ($this->config['auth'] == 'ssh-password') {
      if (!isset($this->config['user']) || !isset($this->config['pass'])) {
        throw new Exception('Router authentication configuration incomplete.');
      }
    }

    if ($this->config['auth'] == 'ssh-key') {
      if (!isset($this->config['user']) || !isset($this->config['private_key'])) {
        throw new Exception('Router authentication configuration incomplete.');
      }
    }
  }

  public function connect() {
    $this->connection = new Net_SSH2($this->config['host'], $this->port);
    $this->connection->setTimeout($this->config['timeout']);
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
      throw new Exception('Unknown type of connection.');
    }

    if (!$success) {
      throw new Exception('Cannot connect to router.');
    }
  }

  public function send_command($command) {
    $this->connect();

    $data = $this->connection->exec($command);

    $this->disconnect();

    return $data;
  }

  public function disconnect() {
    if (($this->connection != null) && $this->connection->isConnected()) {
      $this->connection->disconnect();
      $this->connection = null;
    }
  }
}

// End of ssh.php
