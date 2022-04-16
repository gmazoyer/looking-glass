<?php

/*
 * Looking Glass - An easy to deploy Looking Glass
 * Copyright (C) 2022 Guillaume Mazoyer <guillaume@mazoyer.eu>
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

  public function __construct($database_file) {
    try {
      $this->database = new PDO('sqlite:'.$database_file);
      $this->database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
      $this->database->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    } catch(PDOException $e) {
      die('Unable to open database: ' . $e->getMessage());
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
    http_response_code(403);
    die('Spam detected');
  }

  /**
   * Check the remote address against the database and determine if it's a
   * spam. Set a no query period if it is considered as spam.
   * 
   * @param string $remote_address the address of the client.
   */
  public function check_spammer($remote_address) {
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
