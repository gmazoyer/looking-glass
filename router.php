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

require_once 'config.php';
require_once 'utils.php';
require_once 'auth/ssh.php';

class Router {
  private $id;
  private $host;
  private $port;
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

    if (isset($config['routers'][$id]['port'])) {
      $this->port = $config['routers'][$id]['port'];
    }
  }

  protected function log_command($command) {
    global $config;

    file_put_contents($config['misc']['logs'], $command,
      FILE_APPEND | LOCK_EX);
  }

  public function send_command($command, $parameters) {
    global $config;

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

    $data = null;

    if ($this->auth == 'ssh-password') {
      if ($this->port != null) {
        $connection = new SSH($this->host, $this->port);
      } else {
        $connection = new SSH($this->host);
      }

      $connection->set_type_auth_password(
        $config['routers'][$this->id]['user'],
        $config['routers'][$this->id]['pass']);
      $connection->connect();
      $data = $connection->send_command($complete_command);
      $connection->disconnect();
    }

    $this->log_command('['.date("Y-m-d H:i:s").'] [client: '.
      $this->requester.'] '.$this->host.'> '.$complete_command."\n");

    return $data;
  }
}

// End of router.php
