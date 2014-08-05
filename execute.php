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
require_once 'routers/router.php';

// From where the user *really* comes from.
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
  $requester = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
  $requester = $_SERVER['REMOTE_ADDR'];
}

function process_output($output) {
  global $config;

  $return = '';

  foreach (preg_split("/((\r?\n)|(\r\n?))/", $output) as $line) {
    // Get rid of empty lines
    if (empty($line)) {
      continue;
    }

    $valid = true;

    if (isset($config['filters'])) {
      foreach ($config['filters'] as $filter) {
        // Line has been marked as invalid
        // Or filtered based on the configuration
        if (!$valid || (preg_match($filter, $line) === 1)) {
          $valid = false;
          break;
        }
      }
    }

    if ($valid) {
      // The line is valid, print it
      $return .= $line."\n";
    }
  }

  return $return;
}

// Obvious spam
if (!isset($_POST['dontlook']) || !empty($_POST['dontlook'])) {
  log_to_file('Spam detected from '.$requester.'.');
  die('Spam detected');
}

// Just asked for the documentation
if (isset($_POST['doc']) && !empty($_POST['doc'])) {
  $query = htmlspecialchars($_POST['doc']);
  print json_encode($config['doc'][$query]);
}

if (isset($_POST['query']) && !empty($_POST['query']) &&
    isset($_POST['routers']) && !empty($_POST['routers']) &&
    isset($_POST['parameters']) && !empty($_POST['parameters'])) {
  $query = htmlspecialchars($_POST['query']);
  $hostname = htmlspecialchars($_POST['routers']);
  $parameters = htmlspecialchars($_POST['parameters']);

  // Do the processing
  $router = Router::instance($hostname, $requester);

  try {
    $output = $router->send_command($query, $parameters);
  } catch (Exception $e) {
    $error = $e->getMessage();
  }

  if (isset($output)) {
    // Display the result of the command
    $data = array('result' => process_output($output));
  } else {
    // Display the error
    $data = array('error' => $error);
  }

  print json_encode($data);
}

// End of execute.php
