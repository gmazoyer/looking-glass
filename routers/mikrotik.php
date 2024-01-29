<?php

/*
 * Looking Glass - An easy to deploy Looking Glass
 * Copyright (C) 2014-2024 Guillaume Mazoyer <guillaume@mazoyer.eu>
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

require_once('router.php');
require_once('includes/command_builder.php');
require_once('includes/utils.php');

final class Mikrotik extends Router {
  protected function build_bgp($parameter, $routing_instance = false) {
    if (!is_valid_ip_address($parameter)) {
      throw new Exception('The parameter is not an IP address.');
    }

    $cmd = new CommandBuilder();

    if (match_ipv6($parameter, false)) {
      $cmd->add('ipv6');
    } else if (match_ipv4($parameter, false)) {
      $cmd->add('ip');
    }
    $cmd->add('route print');

    if ($this->config['bgp_detail']) {
      $cmd->add('detail');
    }

    $cmd->add('where', quote($parameter).' in dst-address');

    return array($cmd);
  }

  protected function build_aspath_regexp($parameter, $routing_instance = false) {
    $commands = array();
    $cmd = new CommandBuilder();

    if (!$this->config['disable_ipv6']) {
      $cmd6 = (clone $cmd)->add('ipv6 route print');
      if ($this->config['bgp_detail']) {
        $cmd6->add('detail');
      }
      $commands[] = $cmd6->add('where', 'bgp-as-path='.quote($parameter));
    }
    if (!$this->config['disable_ipv4']) {
      $cmd4 = (clone $cmd)->add('ip route print');
      if ($this->config['bgp_detail']) {
        $cmd4->add('detail');
      }
      $commands[] = $cmd4->add('where', 'bgp-as-path='.quote($parameter));
    }

    return $commands;
  }

  protected function build_as($parameter, $routing_instance = false) {
    $commands = array();
    $cmd = new CommandBuilder();

    if (!$this->config['disable_ipv6']) {
      $cmd6 = (clone $cmd)->add('ipv6 route print');
      if ($this->config['bgp_detail']) {
        $cmd6->add('detail');
      }
      $commands[] = $cmd6->add('where', 'bgp-as-path~'.quote($parameter.'\$'));
    }
    if (!$this->config['disable_ipv4']) {
      $cmd4 = (clone $cmd)->add('ip route print');
      if ($this->config['bgp_detail']) {
        $cmd4->add('detail');
      }
      $commands[] = $cmd4->add('where', 'bgp-as-path~'.quote($parameter.'\$'));
    }

    return $commands;
  }

  protected function build_ping($parameter, $routing_instance = false) {
    if (!is_valid_destination($parameter)) {
      throw new Exception('The parameter is not an IP address or a hostname.');
    }

    $cmd = new CommandBuilder();
    $cmd->add('ping count=10');

    if (match_hostname($parameter)) {
      $cmd->add('address=[:resolv '.quote($parameter).']');
    } else {
      $cmd->add('address='.quote($parameter));
    }

    if ($this->has_source_interface_id()) {
      if (is_valid_ip_address($this->get_source_interface_id())) {
        $cmd->add('src-address=');
        if (match_ipv6($parameter)) {
          $cmd->add($this->get_source_interface_id('ipv6'));
        } else {
          $cmd->add($this->get_source_interface_id('ipv4'));
        }
      } else {
        $cmd->add('interface='.$this->get_source_interface_id());
      }
    }

    if ($routing_instance !== false) {
      $cmd->add('routing-table='.$routing_instance);
    }

    return array($cmd);
  }
  protected function build_traceroute($parameter, $routing_instance = false) {
    if (!is_valid_destination($parameter)) {
      throw new Exception('The parameter is not an IP address or a hostname.');
    }

    $cmd = new CommandBuilder();
    $cmd->add('tool traceroute count=1 use-dns=yes');

    if (match_hostname($parameter)) {
      $cmd->add('address=[:resolv '.quote($parameter).']');
    } else {
      $cmd->add('address='.quote($parameter));
    }

    if ($this->has_source_interface_id()) {
      if (is_valid_ip_address($this->get_source_interface_id())) {
        $cmd->add('src-address=');
        if (match_ipv6($parameter)) {
          $cmd->add($this->get_source_interface_id('ipv6'));
        } else {
          $cmd->add($this->get_source_interface_id('ipv4'));
        }
      } else {
        $cmd->add('interface='.$this->get_source_interface_id());
      }
    }

    if ($routing_instance !== false) {
      $cmd->add('routing-table='.$routing_instance);
    }

    return array($cmd);
  }
}

// End of mikrotik.php
