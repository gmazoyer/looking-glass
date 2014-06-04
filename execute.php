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
require_once 'router.php';
require_once 'utils.php';

// Obvious spam
if (isset($_POST['dontlook']) || !empty($_POST['dontlook'])) {
  die('Spam detected');
}

if (isset($_POST['query']) && !empty($_POST['query']) &&
    isset($_POST['routers']) && !empty($_POST['routers']) &&
    isset($_POST['parameters']) && !empty($_POST['parameters'])) {
  $query = htmlspecialchars($_POST['query']);
  $hostname = htmlspecialchars($_POST['routers']);
  $parameters = htmlspecialchars($_POST['parameters']);
  $valid_request = false;

  switch ($query) {
    case 'bgp':
      if (match_ipv4($parameters) || match_ipv6($parameters)) {
        $valid_request = true;
      } else {
        $error = 'The parameter is not an IPv4/IPv6 address.';
      }
      break;

    case 'as-path-regex':
      if (match_aspath_regex($parameters)) {
        $valid_request = true;
      } else {
        $error = 'The parameter is not an AS-Path regular expression.';
      }
      break;

    case 'as':
      if (match_as($parameters)) {
        $valid_request = true;
      } else {
        $error = 'The parameter is not an AS number.';
      }
      break;

    case 'ping':
    case 'traceroute':
      if (match_ipv4($parameters) || match_ipv6($parameters) ||
          match_fqdn($parameters)) {
        $valid_request = true;
      } else {
        $error = 'The parameter is not an IPv4/IPv6 address or a FQDN.';
      }
      break;

    default:
      $error = 'Unknown request: '.$query;
      break;
  }

  if (!$valid_request && isset($error)) {
    // Unknown query or invalid parameters
    echo $error;
  } else {
    // Do the processing
    // Router connection, command execution, disconnection
    $router = new Router($hostname, $_SERVER['REMOTE_ADDR']);
    $data = $router->send_command($query, $parameters);

    // Process the output line by line
    $return = '';
    foreach (preg_split("/((\r?\n)|(\r\n?))/", $data) as $line) {
      // Get rid of empty lines
      if (empty($line)) {
        continue;
      }

      $valid = true;

      foreach ($config['filters'] as $filter) {
        // Line has been marked as invalid
        if (!$valid) {
          break;
        }

        // Filter line based on the configuration
        if (preg_match($filter, $line) === 1) {
          $valid = false;
          break;
        }
      }

      // The line is valid, print it
      if ($valid) {
        $return .= $line."\n";
      }
    }

    // Display the result of the command
    echo $return;
  }
}

// End of execute.php
