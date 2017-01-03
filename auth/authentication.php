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

ini_set('include_path', ini_get('include_path').':./libs/phpseclib-1.0.5');

require_once('ssh.php');
require_once('telnet.php');

/**
 * This class needs to be extended by every class implementing an
 * authentication mecanism.
 *
 * It provides the basis to interact with an authentication mecanism.
 *
 * When implementing an authentication mecanism, the subclass will need to
 * override several methods that define if the configuration is correct and
 * how to connect, disconnect and send a command to the host.
 */
abstract class Authentication {
  /**
   * The configuration array containing information needed by the
   * authentication mecanism.
   */
  protected $config;

  /**
   * Build a new object to manipulate the authentication mecanism.
   *
   * It will also check if the configuration is correct before doing the
   * instanciation.
   *
   * @param array $config the configuration for the authentication mecanism.
   */
  public function __construct($config) {
    $this->config = $config;
    $this->check_config();
  }

  /**
   * Method that check if the configuration is correct.
   *
   * If the validation does not pass, this method must throw an Exception.
   */
  protected abstract function check_config();

  /**
   * Method that provides the needed logic to connect to a host.
   */
  public abstract function connect();

  /**
   * Method that provides the needed logic to disconnect from a host.
   */
  public abstract function disconnect();

  /**
   * Method that provides the needed logic to send a given command to a host.
   *
   * @param string $command the command to be sent to the host.
   */
  public abstract function send_command($command);

  /**
   * Method called when the object is destructed.
   *
   * It will ensure that the disconnection has been done before destructing
   * the object.
   */
  public function __destruct() {
    $this->disconnect();
  }

  /**
   * Method that decides which authentication method to instanciate based on a
   * given configuration.
   *
   * Note: no instanciation of authentication mecanism class should be done
   * elsewhere than here.
   *
   * @param  array          $config the configuration for the authentication
   *                                mecanism.
   * @return Authentication the authentication mecanism to be used.
   */
  public static final function instance($config) {
    switch ($config['auth']) {
      case 'ssh-password':
      case 'ssh-key':
        return new SSH($config);

      case 'telnet':
        return new Telnet($config);

      default:
        print('Unknown authentication mecanism "'.$config['auth'].'"."');
        return null;
    }
  }
}

// End of authentication.php
