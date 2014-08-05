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

function match_ipv4($ip, $ip_only = true) {
  if (strrpos($ip, '/') && !$ip_only)  {
    $ip_and_mask = explode('/', $ip, 2);

    return filter_var($ip_and_mask[0], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) &&
      filter_var($ip_and_mask[1], FILTER_VALIDATE_INT);
  } else {
    return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
  }
}

function match_ipv6($ip, $ip_only = true) {
  if (strrpos($ip, '/') && !$ip_only)  {
    $ip_and_mask = explode('/', $ip, 2);

    return filter_var($ip_and_mask[0], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) &&
      filter_var($ip_and_mask[1], FILTER_VALIDATE_INT);
  } else {
    return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
  }
}

function match_fqdn($fqdn) {
  $regex = '/(?=^.{4,255}$)(^((?!-)[a-zA-Z0-9-]{1,63}(?<!-)\.)+[a-zA-Z]{2,63}$)/';

  if ((preg_match($regex, $fqdn) === false) ||
      (preg_match($regex, $fqdn) === 0)) {
    return false;
  } else {
    return true;
  }
}

function match_as($as) {
  global $config;

  $options_wide_range = array(
    'options' => array('min_range' => 1, 'max_range' => 4294967294)
  );
  $options_16bit_private = array(
    'options' => array( 'min_range' => 64512, 'max_range' => 65534)
  );
  $options_32bit_private = array(
    'options' => array('min_range' => 4200000000, 'max_range' => 4294967294)
  );

  if (!filter_var($as, FILTER_VALIDATE_INT, $options_wide_range)) {
    return false;
  }

  if (filter_var($as, FILTER_VALIDATE_INT, $options_16bit_private)) {
    return isset($config['misc']['allow_private_asn']) &&
      $config['misc']['allow_private_asn'] == true;
  }

  if (filter_var($as, FILTER_VALIDATE_INT, $options_32bit_private)) {
    return isset($config['misc']['allow_private_asn']) &&
      $config['misc']['allow_private_asn'] == true;
  }

  return true;
}

function match_aspath_regex($aspath_regex) {
  // TODO: validate a regex with a regex?
  return true;
}

function log_to_file($log) {
  global $config;

  $log = '['.date("Y-m-d H:i:s").'] '.$log."\n";
  file_put_contents($config['misc']['logs'], $log, FILE_APPEND | LOCK_EX);
}

// End of utils.php
