<?php

/*
 * Looking Glass - An easy to deploy Looking Glass
 * Copyright (C) 2014-2022 Guillaume Mazoyer <guillaume@mazoyer.eu>
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
require_once('includes/config.defaults.php');
require_once('config.php');
require_once('routers/router.php');
require_once('includes/antispam.php');
require_once('includes/captcha.php');
require_once('includes/utils.php');

// From where the user *really* comes from.
$requester = get_requester_ip();

// Check for spam
if ($config['antispam']['enabled']) {
  $antispam = new AntiSpam($config['antispam']['database_file']);
  $antispam->check_spammer($requester);
}

// Just asked for the documentation
if (isset($_POST['doc']) && !empty($_POST['doc'])) {
  $query = htmlspecialchars($_POST['doc']);
  print(json_encode($config['doc'][$query]));
  return;
}

if (isset($_POST['query']) && !empty($_POST['query']) &&
    isset($_POST['routers']) && !empty($_POST['routers']) &&
    isset($_POST['parameter']) && !empty($_POST['parameter'])) {
  $query = trim($_POST['query']);
  $hostname = trim($_POST['routers']);
  $parameter = trim($_POST['parameter']);

  // Check if query is disabled
  if (!isset($config['doc'][$query]['command'])) {
    $error = 'This query has been disabled in the configuration.';
    print(json_encode(array('error' => $error)));
    return;
  }

  // Process captcha if it is enabled
  if (isset($config['captcha']) && $config['captcha']['enabled']) {
    $captcha = new Captcha($config['captcha']['type']);
    if (!$captcha->validate($requester)) {
      reject_requester('Are you a robot?');
    }
  }

  // Do the processing
  $router = Router::instance($hostname, $requester);
  $router_config = $router->get_config();

  // Check if parameter is an IPv6 and if IPv6 is disabled
  if (match_ipv6($parameter) && $router_config['disable_ipv6']) {
    $error = 'IPv6 has been disabled for this router, you can only use IPv4.';
    print(json_encode(array('error' => $error)));
    return;
  }

  // Check if parameter is an IPv4 and if IPv4 is disabled
  if (match_ipv4($parameter) && $router_config['disable_ipv4']) {
    $error = 'IPv4 has been disabled for this router, you can only use IPv6.';
    print(json_encode(array('error' => $error)));
    return;
  }

  if (isset($_POST['vrf']) && mb_strtolower($_POST['vrf']) !== 'none' && $config['vrfs']['enabled']) {
      if (empty(trim($_POST['vrf']))) {
          $error = 'Empty vrf';
          print(json_encode(array('error' => $error)));
      }
      // To prevent people from entering a own value or arguments.
      if ( ! in_array($_POST['vrf'], $config['vrfs']['vrfs'])) {
          $error = 'Invalid Vrf. Given vrf is not configured on the server';
          print(json_encode(array('error' => $error)));
      }
      $vrf = $_POST['vrf'];
  } else {
      $vrf = false;
  }

  try {
    $output = $router->send_command($query, $parameter, $vrf);
  } catch (Exception $e) {
    $error = $e->getMessage();
  }

  if (isset($output)) {
    // Display the result of the command
    $data = array('result' => $output);
  } else {
    // Display the error
    $data = array('error' => $error);
  }

  print(json_encode($data));
}

// End of execute.php
