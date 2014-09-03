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

/**
 * Test if a given parameter is a private IPv4 or IPv6.
 *
 * @param  string  $ip the parameter to test.
 * @return boolean true if the parameter is a private IP address, false
 *                 otherwise.
 */
function match_private_ip_range($ip) {
  if (empty($ip)) {
    return false;
  }

  $is_private = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE);

  return (!$is_private ? true : false);
}

/**
 * Test if a given parameter is a reserved IPv4.
 *
 * @param  string  $ip the parameter to test.
 * @return boolean true if the parameter is a reserved IPv4 address, false
 *                 otherwise.
 */
function match_reserved_ip_range($ip) {
  if (empty($ip)) {
    return false;
  }

  $is_reserved = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_RES_RANGE);

  return (!$is_reserved ? true : false);
}

/**
 * Test if a given parameter is an IPv4 or not.
 *
 * @param  string  $ip      the parameter to test.
 * @param  boolean $ip_only optional parameter, if omitted or set to true, it
 *                          will ensure that the first parameter is an IPv4
 *                          address without mask, if set to false the IP/MASK
 *                          form is considered as valid.
 * @return boolean true if the parameter matches an IPv4 address, false
 *                 otherwise.
 */
function match_ipv4($ip, $ip_only = true) {
  global $config;

  if (empty($ip)) {
    return false;
  }

  if (strrpos($ip, '/') && !$ip_only)  {
    $ip_and_mask = explode('/', $ip, 2);

    if (!$config['misc']['allow_private_ip'] &&
        match_private_ip_range($ip_and_mask[0])) {
      return false;
    }

    if (!$config['misc']['allow_reserved_ip'] &&
        match_reserved_ip_range($ip_and_mask[0])) {
      return false;
    }

    return filter_var($ip_and_mask[0], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) &&
      filter_var($ip_and_mask[1], FILTER_VALIDATE_INT);
  } else {
    if (!$config['misc']['allow_private_ip'] &&
        match_private_ip_range($ip)) {
      return false;
    }

    if (!$config['misc']['allow_reserved_ip'] &&
        match_reserved_ip_range($ip)) {
      return false;
    }

    return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
  }
}

/**
 * Test if a given parameter is an IPv6 or not.
 *
 * @param  string  $ip      the parameter to test.
 * @param  boolean $ip_only optional parameter, if omitted or set to true, it
 *                          will ensure that the first parameter is an IPv6
 *                          address without mask, if set to false the IP/MASK
 *                          form is considered as valid.
 * @return boolean true if the parameter matches an IPv6 address, false
 *                 otherwise.
 */
function match_ipv6($ip, $ip_only = true) {
  global $config;

  if (empty($ip)) {
    return false;
  }

  if (strrpos($ip, '/') && !$ip_only)  {
    $ip_and_mask = explode('/', $ip, 2);

    if (!$config['misc']['allow_private_ip'] &&
        match_private_ip_range($ip_and_mask[0])) {
      return false;
    }

    return filter_var($ip_and_mask[0], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) &&
      filter_var($ip_and_mask[1], FILTER_VALIDATE_INT);
  } else {
    if (!$config['misc']['allow_private_ip'] &&
        match_private_ip_range($ip)) {
      return false;
    }

    return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
  }
}

/**
 * Test if a given parameter is a valid FQDN or not.
 *
 * @param  string  $fqdn the parameter to test.
 * @return boolean true if the parameter matches a valid FQDN, false otherwise.
 */
function match_fqdn($fqdn) {
  $regex = '/(?=^.{4,255}$)(^((?!-)[a-zA-Z0-9-]{1,63}(?<!-)\.)+[a-zA-Z]{2,63}$)/';

  if (empty($fqdn)) {
    return false;
  }

  if ((preg_match($regex, $fqdn) === false) ||
      (preg_match($regex, $fqdn) === 0)) {
    return false;
  } else {
    return true;
  }
}

/**
 * Test if a given parameter is a valid AS number or not.
 *
 * @param  integer $as the parameter to test.
 * @return boolean true if the parameter matches a valid ASN, false otherwise.
 */
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

  if (empty($as)) {
    return false;
  }

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
  if (empty($aspath_regex)) {
    return false;
  }

  // TODO: validate a regex with a regex?
  return true;
}

/**
 * For a given FQDN try to find the corresponding IPv4 or IPv6 address.
 *
 * If there is multiple addresses attached to the same FQDN it will give the
 * first IPv4 or IPv6 found.
 *
 * If the FQDN have both IPv4 and IPv6 addresses it will give the first IPv6
 * address found.
 *
 * @param  string $fqdn the FQDN to use to search in the DNS databases.
 * @return string an IPv4 or IPv6 address based on the DNS records.
 */
function fqdn_to_ip_address($fqdn) {
  $dns_record = dns_get_record($fqdn, DNS_A + DNS_AAAA);

  // No DNS record found
  if (!$dns_record) {
    return false;
  }

  $records_nb = count($dns_record);

  // Only one record found
  if ($records_nb == 1) {
    if ($dns_record[0]['type'] == 'AAAA') {
      return $dns_record[0]['ipv6'];
    } else if ($dns_record[0]['type'] == 'A') {
      return $dns_record[0]['ip'];
    } else {
      return false;
    }
  }

  // Several records found
  if ($records_nb > 1) {
    // TODO: this could probably be more optimal
    foreach ($dns_record as $record) {
      if ($record['type'] == 'AAAA') {
        return $record['ipv6'];
      }
    }

    foreach ($dns_record as $record) {
      if ($record['type'] == 'AAA') {
        return $record['ipv4'];
      }
    }

    return false;
  }
}

/**
 * Send the given parameter to the logs file.
 *
 * @param string $log the log to be recorded in the logs file.
 */
function log_to_file($log) {
  global $config;

  $log = '['.date("Y-m-d H:i:s").'] '.$log."\n";
  file_put_contents($config['misc']['logs'], $log, FILE_APPEND | LOCK_EX);
}

// End of utils.php
