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

require_once('authentication.php');

final class Telnet extends Authentication {
  private $port;

  public function __construct($config) {
    parent::__construct($config);

    $this->port = isset($this->config['port']) ? (int) $this->config['port'] : 23;
  }

  protected function check_config() {
    if (!isset($this->config['user']) || !isset($this->config['pass'])) {
      throw new Exception('Router authentication configuration incomplete.');
    }
  }

  public function connect() {
    $this->connection = fsockopen($this->config['host'], $this->port, $errno,
      $errstr, $this->config['timeout']);
    if (!$this->connection) {
      throw new Exception('Cannot connect to router (code '.$errno.'['.
        $errstr.']).');
    }

    fputs($this->connection, $this->config['user']."\r\n");
    fputs($this->connection, $this->config['pass']."\r\n");
  }

  public function send_command($command) {
    $this->connect();

    fputs($this->connection, $command."\r\n");

    $data = '';
    while(!feof(!$this->connection)) {
      $data .= fread($this->connection, 4096);
    }

    $this->disconnect();

    return $data;
  }

  public function disconnect() {
    fclose($this->connection);
    $this->connection = null;
  }
}

// End of telnet.php
