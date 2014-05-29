<?php

function match_ipv4($ip) {
  return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
}

function match_ipv6($ip) {
  return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
}

function match_fqdn($fqdn) {
  $regex = '/^(?=.{1,255}$)[0-9A-Za-z](?:(?:[0-9A-Za-z]|-){0,61}[0-9A-Za-z])?(?:\.[0-9A-Za-z](?:(?:[0-9A-Za-z]|-){0,61}[0-9A-Za-z])?)*\.?$/';

  if ((preg_match($regex, $fqdn) === false) ||
      (preg_match($regex, $fqdn) === 0)) {
    return false;
  } else {
    return true;
  }
}

function match_as($as) {
  $options_wide_range = array(
    'options' => array('min_range' => 1, 'max_range' => 4294967294)
  );
  $options_16bit_private = array(
    'options' => array( 'min_range' => 64512, 'max_range' => 65534)
  );
  $options_32bit_private = array(
    'options' => array('min_range' => 4200000000, 'max_range' => 4294967294)
  );

  return (filter_var($as, FILTER_VALIDATE_INT, $options_wide_range) &&
         !filter_var($as, FILTER_VALIDATE_INT, $options_16bit_private) &&
         !filter_var($as, FILTER_VALIDATE_INT, $options_32bit_private));
}

function match_aspath_regex($aspath_regex) {
  // TODO: validate a regex with a regex?
  return true;
}

// End of utils.php
