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
require_once 'juniper.php';
require_once 'utils.php';
require_once 'auth/authentication.php';

abstract class Router {
  protected $config;
  protected $id;
  protected $requester;

  public function __construct($config, $id, $requester) {
    $this->config = $config;
    $this->id = $id;
    $this->requester = $requester;

    if (isset($config['routers'][$id]['port'])) {
      $this->port = (int) $config['routers'][$id]['port'];
    }
  }

  protected abstract function build_commands($command, $parameters);

  public function send_command($command, $parameters) {
    try {
      $commands = $this->build_commands($command, $parameters);
    } catch (Exception $e) {
      throw $e;
    }

    $auth = Authentication::instance($this->config);

    try {
      $auth->connect();
      $data = '';

      foreach ($commands as $selected) {
        $data .= $auth->send_command($selected);
      }
    } catch (Exception $e) {
      throw $e;
    } finally {
      $auth->disconnect();

      foreach ($commands as $selected) {
        log_to_file('[client: '.$this->requester.'] '.$this->config['host'].'> '.
        $selected);
      }
    }

    return $data;
  }

  public static final function instance($id, $requester) {
    global $config;

    $router_config = $config['routers'][$id];

    switch ($router_config['type']) {
      case 'juniper':
      case 'junos':
        return new Juniper($router_config, $id, $requester);

      default:
        print 'Unknown router type "'.$router_config['type'].'"."';
        return null;
    }
  }
}

// End of router.php
