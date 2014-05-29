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

if (!defined('LOOKING_GLASS01')) {
  exit;
}

require_once 'config.php';
require_once 'utils.php';

class Router {
  private $id;
  private $host;
  private $type;
  private $auth;
  private $connection;
  private $requester;

  public function __construct($id, $requester) {
    global $config;

    $this->id = $id;
    $this->host = $config['routers'][$id]['host'];
    $this->type = $config['routers'][$id]['type'];
    $this->auth = $config['routers'][$id]['auth'];
    $this->requester = $requester;
  }

  private function log_command($command) {
    global $config;

    file_put_contents($config['misc']['logs'], $command,
      FILE_APPEND | LOCK_EX);
  }

  public function connect() {
    global $config;

    switch ($this->auth) {
      case 'ssh-password':
        $this->connection = ssh2_connect($this->host, 22);
        if (!$this->connection) {
          throw new Exception('Cannot connect to router');
        }

        $user = $config['routers'][$this->id]['user'];
        $pass = $config['routers'][$this->id]['pass'];

        if (!ssh2_auth_password($this->connection, $user, $pass)) {
          throw new Exception('Bad login/password.');
        }

        break;

      default:
        echo 'Unknown protocol for connection.';
    }
  }

  public function execute_command($command) {
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

  public function send_command($command, $parameters) {
    switch ($command) {
      case 'bgp':
        if (($parameters != null) && (strlen($parameters) > 0)) {
            $complete_command = 'show route '.$parameters.' | no-more';
        } else {
          return 'An IP address (and only one) is required as destination.';
        }
        break;

      case 'as-path-regex':
        if (($parameters != null) && (strlen($parameters) > 0)) {
          $complete_command = 'show route aspath-regex '.$parameters.' | no-more';
        } else {
          return 'An AS-Path regex is required like ".*XXXX YYYY.*".';
        }
        break;

      case 'as':
        if (($parameters != null) && (strlen($parameters) > 0)) {
	  $complete_command = 'show route aspath-regex .*'.$parameters.'.* | no-more';
        } else {
          return 'An AS number is required like XXXX.';
        }
        break;

      case 'ping':
        if (($parameters != null) && (strlen($parameters) > 0)) {
          $complete_command = 'ping count 10 '.$parameters.' rapid';
        } else {
          return 'An IP address (and only one) is required to ping a host.';
        }
        break;

      case 'traceroute':
        if (($parameters != null) && (strlen($parameters) > 0)) {
          if (match_ipv4($parameters)) {
            $complete_command = 'traceroute '.$parameters.' as-number-lookup';
          } else {
            $complete_command = 'traceroute '.$parameters;
          }
        } else {
          return 'An IP address is required to traceroute a host.';
        }
        break;

      default:
        return 'Command not supported.';
    }

    $this->log_command('['.date("Y-m-d H:i:s").'] [client: '.
      $this->requester.'] '.$this->host.'> '.$complete_command."\n");
    return $this->execute_command($complete_command);
  }

  public function disconnect() {
    $this->execute_command('exit'); 
    $this->connection = null;
  }

  public function __destruct() {
    $this->disconnect();
  }
}

// End of router.php
