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

require_once('includes/config.defaults.php');
require_once('config.php');
require_once('routers/router.php');
require_once('includes/utils.php');

// From where the user *really* comes from.
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
  $requester = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
  $requester = $_SERVER['REMOTE_ADDR'];
}

// Obvious spam
if (!isset($_POST['dontlook']) || !empty($_POST['dontlook'])) {
  log_to_file('Spam detected from '.$requester.'.');
  die('Spam detected');
}

// Just asked for the documentation
if (isset($_POST['doc']) && !empty($_POST['doc'])) {
  $query = htmlspecialchars($_POST['doc']);
  print(json_encode($config['doc'][$query]));
}

if (isset($_POST['query']) && !empty($_POST['query']) &&
    isset($_POST['routers']) && !empty($_POST['routers']) &&
    isset($_POST['parameter']) && !empty($_POST['parameter'])) {
  $query = trim($_POST['query']);
  $hostname = trim($_POST['routers']);
  $parameter = trim($_POST['parameter']);

  
  /// Rate limit
  $limit = 3; //limit command
	$tiempo = 120; //time to limit commands
	$comandos = 10; //total commands in execution

	// connect to DB
	try {
		$dbh = new \PDO('sqlite:ratelimit.db');
	} catch (PDOException $e) {

		exit($e -> getMessage());
	}

	//comandos en ejecución
	$q = $dbh -> prepare('SELECT * FROM Commands');
	$q -> execute();
	$row = $q -> fetch(\PDO::FETCH_ASSOC);
	$enejecucion = (int)$row['hits'];
	$ultimaejecucion = (int)$row['accessed'] + ($tiempo * 2);

	// check for IP
	$q = $dbh -> prepare('SELECT * FROM RateLimit WHERE ip = ?');
	$q -> execute(array($requester));
	$row = $q -> fetch(\PDO::FETCH_ASSOC);
  
	$time = time();

	// if IP does not exist
	if (!isset($row['ip'])) {
		$q = $dbh -> prepare('INSERT INTO RateLimit (ip, hits, accessed) VALUES (?, ?, ?)');
		$q -> execute(array($_SERVER['REMOTE_ADDR'], 1, $time));
		return true;
	}

	$accessed = (int)$row['accessed'] + $tiempo;
	$hits = (int)$row['hits'];

	if ($ultimaejecucion < $time) {
		$enejecucion = 0;
		$q = $dbh -> prepare('UPDATE Commands SET hits = ?, accessed = ?');
		$q -> execute(array(($enejecucion), time()));
	}

	if ($enejecucion >= $comandos) {
		$error = 'Too many commands. Try again later...';
		print(json_encode(array('error' => $error)));

		if ($ultimaejecucion < $time) {
			$enejecucion = 0;
			$q = $dbh -> prepare('UPDATE Commands SET hits = ?, accessed = ?');
			$q -> execute(array(($enejecucion), time()));
		}

		return;

	} else {

		if ($accessed > $time) {
			if ($hits >= $limit) {
				$reset = (int)(($accessed - $time) / 1);
				if ($reset <= 1) {
					$error = 'Rate limit exceeded. Try again in: 1 seconds';
					print(json_encode(array('error' => $error)));
					return;
				}
				$error = 'Rate limit exceeded. Try again in: ' . $reset . ' seconds';
				print(json_encode(array('error' => $error)));
				return;
			}
			$q = $dbh -> prepare('UPDATE RateLimit SET hits = ? WHERE ip = ?');
			$q -> execute(array(($hits + 1), $_SERVER['REMOTE_ADDR']));

			$q = $dbh -> prepare('UPDATE Commands SET hits = ?, accessed = ?');
			$q -> execute(array(($enejecucion + 1), time()));

		} else {
			$q = $dbh -> prepare('UPDATE RateLimit SET hits = ?, accessed = ? WHERE ip = ?');
			$q -> execute(array(1, time(), $_SERVER['REMOTE_ADDR']));

			$q = $dbh -> prepare('UPDATE Commands SET hits = ?, accessed = ?');
			$q -> execute(array(($enejecucion + 1), time()));
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

  try {
    $output = $router->send_command($query, $parameter);
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

  ///// Rate limit finished commands
	$q = $dbh -> prepare('SELECT * FROM Commands');
	$q -> execute();
	$row = $q -> fetch(\PDO::FETCH_ASSOC);
	$enejecucion = (int)$row['hits'];
	if ($enejecucion > 0) {
		// update command
		$q = $dbh -> prepare('UPDATE Commands SET hits = ?');
		$q -> execute(array(($enejecucion - 1)));
	}


  
  print(json_encode($data));
}

// End of execute.php
