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

require_once 'authentication.php';

class SSH extends Authentication {
  private $port;

  public function __construct($config) {
    parent::__construct($config);

    $this->port = isset($this->config['port']) ? $this->config['port'] : 22;
  }

  protected function check_config() {
    if ($this->config['auth'] == 'ssh-password') {
      if (!isset($this->config['user']) || !isset($this->config['pass'])) {
        throw new Exception('User and password required for ssh-password.');
      }
    }

    if ($this->config['auth'] == 'ssh-key') {
      if (!isset($this->config['user']) || !isset($this->config['public_key'])
        || !isset($this->config['private_key'])) {
        throw new Exception('User and key pair required for ssh-key.');
      }
    }
  }

  public function connect() {
    $this->connection = ssh2_connect($this->config['host'], $this->port);
    if (!$this->connection) {
      throw new Exception('Cannot connect to router.');
    }

    $success = false;

    if ($this->config['auth'] == 'ssh-password') {
      $success = ssh2_auth_password($this->connection, $this->config['user'],
        $this->config['pass']);
    } else if ($this->config['auth'] == 'ssh-key') {
      if (isset($this->config['pass'])) {
        $success = ssh2_auth_pubkey_file($this->connection, $this->config['user'],
          $this->config['public_key'], $this->config['private_key'],
          $this->config['pass']);
      } else {
        $success = ssh2_auth_pubkey_file($this->connection, $this->config['user'],
          $this->config['public_key'], $this->config['private_key']);
      }
    } else {
      throw new Exception('Unknown type of connection for SSH.');
    }

    if (!success) {
      throw new Exception('SSH authentication failed.');
    }
  }

  public function send_command($command) {
    if ($this->connection == null) {
      $this->connect();
    }

    if (!($stream = ssh2_exec($this->connection, $command))) {
      throw new Exception('SSH command failed');
    }

    stream_set_blocking($stream, true);

    $data = '';
    while ($buf = fread($stream, 4096)) {
      $data .= $buf;
    }
    fclose($stream);

    return $data;
  }

  public function disconnect() {
    $this->send_command('exit');
    $this->connection = null;
  }

  public function __destruct() {
    parent::__destruct();
  }
}

// End of ssh.php
