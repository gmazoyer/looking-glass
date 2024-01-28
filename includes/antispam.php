<?php

/*
 * Looking Glass - An easy to deploy Looking Glass
 * Copyright (C) 2022-2024 Guillaume Mazoyer <guillaume@mazoyer.eu>
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

require_once('includes/utils.php');

class AntiSpam {
  private $database_file;
  private $database;
  private $allow_list;

  public function __construct($config) {
    try {
      $this->database = new PDO('sqlite:'.$config['database_file']);
      $this->database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
      $this->database->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    } catch(PDOException $e) {
      die('Unable to open database: '.$e->getMessage());
    }

    $this->database->exec('pragma auto_vacuum = 1');
    $this->database->exec(
      'CREATE TABLE if not exists users (
        hash PRIMARY KEY,
        no_query_period TIMESTAMP,
        degree INTEGER
      );'
    );

    $this->clean_no_query_periods();
    $this->allow_list = $config['allow_list'];
  }

  public function __destruct() {
    $this->database = null;
  }

  /**
   * Compute the no query period based a degree.
   * 
   * @param  int $degree a coefficient to compute the no query period, the
   *                     higher the longer.
   * @return int the timestamp until when the user cannot send a query.
   */
  private function no_query_period($degree) {
    return time() + intval(pow($degree, 2.5));
  }

  /**
   * Clean expired query limitations from the database.
   */
  private function clean_no_query_periods() {
    $this->database->exec(
      'DELETE FROM users
       WHERE strftime ("%s", "now") > no_query_period;'
    );
  }

  /**
   * Send a HTTP 403 response to the spammer and quit.
   */
  private function reject_spammer() {
    reject_requester('Spam detected');
  }

  /**
   * Check if an IP address fits in an IP prefix.
   * 
   * @param \IPLib\Address\AddressInterface $ip_address the IP address to check.
   * @return boolean                        true if the IP address fits in one
   *                                             of the configured IP prefixes.
   */
  private function is_allowed_ip($ip_address) {
    $address = \IPLib\Factory::parseAddressString($ip_address);

    foreach ($this->allow_list as $network) {
      $prefix = \IPLib\Factory::parseRangeString($network);
      if (is_ip_address_in_prefix($address, $prefix)) {
        return true;
      }
    }

    return false;
  }

  /**
   * Check the remote address against the database and determine if it's a
   * spam. Set a no query period if it is considered as spam.
   * 
   * If the remote address matches one of the prefix in the configured allow
   * list, the spammer logic will not run.
   * 
   * @param string $remote_address the address of the client.
   */
  public function check_spammer($remote_address) {
    if ($this->is_allowed_ip($remote_address)) {
      return;
    }

    $hash = sha1($remote_address);

    $query = $this->database->prepare(
      'SELECT * FROM users WHERE hash = :hash'
    );
    $query->bindValue(':hash', $hash, PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch();

    $in_period = !empty($result) and time() < $result['no_query_period'];
    $obvious_spam = !isset($_POST['dontlook']) or !empty($_POST['dontlook']);

    $degree = $in_period ? $result['degree'] + 1 : ($obvious_spam ? 512 : 1);
    $no_query_period = $this->no_query_period($degree);

    $query = $this->database->prepare(
      'REPLACE INTO users (hash, no_query_period, degree) VALUES (:hash, :no_query_period, :degree);'
    );
    $query->bindValue(':hash', $hash, PDO::PARAM_STR);
    $query->bindValue(':no_query_period', $no_query_period, PDO::PARAM_INT);
    $query->bindValue(':degree', $degree, PDO::PARAM_INT);
    $query->execute();

    if ($obvious_spam) {
      log_to_file('Spam (obvious) detected from '.$remote_address);
      $this->reject_spammer();
    }
    if ($in_period) {
      log_to_file('Spam (in period) detected from '.$remote_address);
      $this->reject_spammer();
    }
  }
}

// End of antispam.php
