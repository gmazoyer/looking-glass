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

require_once 'includes/config.defaults.php';
require_once 'config.php';
require_once 'bird.php';
require_once 'cisco.php';
require_once 'juniper.php';
require_once 'quagga.php';
require_once 'includes/utils.php';
require_once 'auth/authentication.php';

abstract class Router {
  protected $global_config;
  protected $config;
  protected $id;
  protected $requester;

  public function __construct($global_config, $config, $id, $requester) {
    $this->global_config = $global_config;
    $this->config = $config;
    $this->id = $id;
    $this->requester = $requester;
  }

  private function sanitize_output($output) {
    // No filters defined
    if (count($this->global_config['filters']) < 1) {
      return preg_replace('/(?:\n|\r\n|\r)$/D', '', $output);
    }

    $filtered = '';

    foreach (preg_split("/((\r?\n)|(\r\n?))/", $output) as $line) {
      $valid = true;

      foreach ($this->global_config['filters'] as $filter) {
        // Line has been marked as invalid
        // Or filtered based on the configuration
        if (!$valid || (preg_match($filter, $line) === 1)) {
          $valid = false;
          break;
        }
      }

     if ($valid) {
        // The line is valid, print it
        $filtered .= $line."\n";
      }
    }

    return preg_replace('/(?:\n|\r\n|\r)$/D', '', $filtered);
  }

  protected function format_output($command, $output) {
    $displayable  = '<p><kbd>Command: '.$command.'</kdb></p>';
    $displayable .= '<pre class="pre-scrollable">'.$output.'</pre>';

    return $displayable;
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
      $data = '';

      foreach ($commands as $selected) {
        $output = $auth->send_command($selected);
        $output = $this->sanitize_output($output);

        $data .= $this->format_output($selected, $output);

        $log = str_replace(array('%D', '%R', '%H', '%C'),
          array(date('Y-m-d H:i:s'), $this->requester, $this->config['host'],
          $selected), $this->global_config['logs']['format']);
        log_to_file($log);
      }
    } catch (Exception $e) {
      throw $e;
    }

    return $data;
  }

  public static final function instance($id, $requester) {
    global $config;

    $router_config = $config['routers'][$id];

    switch (strtolower($router_config['type'])) {
      case 'bird':
        return new Bird($config, $router_config, $id, $requester);

      case 'cisco':
      case 'ios':
        return new Cisco($config, $router_config, $id, $requester);

      case 'juniper':
      case 'junos':
        return new Juniper($config, $router_config, $id, $requester);

      case 'quagga':
      case 'zebra':
        return new Quagga($config, $router_config, $id, $requester);

      default:
        print 'Unknown router type "'.$router_config['type'].'"."';
        return null;
    }
  }
}

// End of router.php
