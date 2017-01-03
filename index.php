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

final class LookingGlass {
  private $release;
  private $frontpage;
  private $contact;
  private $misc;
  private $routers;

  function __construct($config) {
    set_defaults_for_routers($config);

    $this->release = $config['release'];
    $this->frontpage = $config['frontpage'];
    $this->contact = $config['contact'];
    $this->misc = $config['misc'];
    $this->routers = $config['routers'];
  }

  private function render_routers() {
    print('<div class="form-group">');
    print('<label for="routers">Router to use</label>');
    print('<select size="5" class="form-control" name="routers" id="routers">');

    $first = true;
    foreach (array_keys($this->routers) as $router) {
      // IPv6 and IPv4 both disabled for the router, ignore it
      if ($this->routers[$router]['disable_ipv6'] &&
          $this->routers[$router]['disable_ipv4']) {
        continue;
      }

      print('<option value="'.$router.'"');
      if ($first) {
        $first = false;
        print(' selected="selected"');
      }
      print('>'.$this->routers[$router]['desc']);
      print('</option>');
    }

    print('</select>');
    print('</div>');
  }

  private function render_commands() {
    print('<div class="form-group">');
    print('<label for="query">Command to issue</label>');
    print('<select size="5" class="form-control" name="query" id="query">');
    print('<option value="bgp" selected="selected">show route IP_ADDRESS</option>');
    print('<option value="as-path-regex">show route as-path-regex AS_PATH_REGEX</option>');
    print('<option value="as">show route AS</option>');
    print('<option value="ping">ping IP_ADDRESS|HOSTNAME</option>');
    print('<option value="traceroute">traceroute IP_ADDRESS|HOSTNAME</option>');
    print('</select>');
    print('</div>');
  }

  private function render_parameter() {
    print('<div class="form-group">');
    print('<label for="input-param">Parameter</label>');
    print('<div class="input-group">');
    print('<input class="form-control" name="parameter" id="input-param" autofocus />');
    print('<div class="input-group-btn">');
    print('<button type="button" class="btn btn-info" data-toggle="modal" data-target="#help">');
    print('<span class="glyphicon glyphicon-question-sign"></span> Help');
    print('</button>');
    print('</div>');
    print('</div>');
    print('</div>');
  }

  private function render_buttons() {
    print('<div class="confirm btn-group">');
    print('<div class="col-md-12 btn-group">');
    print('<button class="col-md-6 btn btn-primary" id="send" type="submit">Enter</button>');
    print('<button class="col-md-6 btn btn-danger" id="clear" type="reset">Reset</button>');
    print('</div>');
    print('</div>');
  }

  private function render_header() {
    if ($this->frontpage['header_link']) {
      print('<a href="'.$this->frontpage['header_link'].'" title="Home">');
    }
    print('<div class="header_bar">');
    if ($this->frontpage['show_title']) {
      print('<h1>'.htmlentities($this->frontpage['title']).'</h1><br>');
    }
    if ($this->frontpage['image']) {
      print('<img src="'.$this->frontpage['image'].'" alt="Logo" />');
    }
    print('</div>');
    if ($this->frontpage['header_link']) {
      print('</a>');
    }
  }

  private function render_content() {
    print('<div class="alert alert-danger alert-dismissable" id="error">');
    print('<button type="button" class="close" aria-hidden="true">&times;</button>');
    print('<strong>Error!</strong>&nbsp;<span id="error-text"></span>');
    print('</div>');
    print('<div class="content" id="command_options">');
    print('<form role="form" action="execute.php" method="post">');

    foreach ($this->frontpage['order'] as $element) {
      switch ($element) {
        case 'routers':
          $this->render_routers();
          break;

        case 'commands':
          $this->render_commands();
          break;

        case 'parameter':
          $this->render_parameter();
          break;

        case 'buttons':
          $this->render_buttons();
          break;

        default:
          break;
      }
    }

    print('<input type="text" class="hidden" name="dontlook" placeholder="Don\'t look at me!" />');
    print('</form>');
    print('</div>');
    print('<div class="loading">');
    print('<div class="progress progress-striped active">');
    print('<div class="progress-bar" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">');
    print('</div>');
    print('</div>');
    print('</div>');
    print('<div class="result">');
    print('<div id="output"></div>');
    print('<div class="reset">');
    print('<button class="btn btn-danger btn-block" id="backhome">Reset</button>');
    print('</div>');
    print('</div>');
  }

  private function render_footer() {
    print('<div class="footer_bar">');
    print('<p class="text-center">');

    if ($this->frontpage['show_visitor_ip']) {
      if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        print('Your IP address: '.htmlentities($_SERVER['HTTP_X_FORWARDED_FOR']).'<br>');
      } else {
        print('Your IP address: '.htmlentities($_SERVER['REMOTE_ADDR']).'<br>');
      }
    }

    if ($this->frontpage['disclaimer']) {
      print($this->frontpage['disclaimer']);
      print('<br><br>');
    }

    if ($this->frontpage['peering_policy_file']) {
      print('<button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#peering-policy"><span class="glyphicon glyphicon-list-alt"></span> Peering Policy</button>');
      print('<br><br>');
    }

    if ($this->contact['name'] && $this->contact['mail']) {
      print('Contact:&nbsp;');
      print('<a href="mailto:'.$this->contact['mail'].'">'.
        htmlentities($this->contact['name']).'</a>');
    }

    print('<br><br>');
    print('<span class="origin">Powered by <a href="https://github.com/respawner/looking-glass" title="Looking Glass Project">Looking Glass '.$this->release['version'].'</a></span>');
    print('</p>');
    print('</div>');
  }

  private function render_peering_policy_modal() {
    print('<div id="peering-policy" class="modal fade">');
    print('<div class="modal-dialog">');
    print('<div class="modal-content">');
    print('<div class="modal-header">');
    print('<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>');
    print('<h4 class="modal-title">Peering Policy</h4>');
    print('</div>');
    print('<div class="modal-body">');
    if (!file_exists($this->frontpage['peering_policy_file'])) {
      print('The peering policy file ('.
        $this->frontpage['peering_policy_file'].') does not exist.');
    } else if (!is_readable($this->frontpage['peering_policy_file'])) {
      print('The peering policy file ('.
        $this->frontpage['peering_policy_file'].') is not readable.');
    } else {
      include($this->frontpage['peering_policy_file']);
    }
    print('</div>');
    print('<div class="modal-footer">');
    print('<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>');
    print('</div>');
    print('</div>');
    print('</div>');
    print('</div>');
  }

  private function render_help_modal() {
    print('<div id="help" class="modal fade">');
    print('<div class="modal-dialog">');
    print('<div class="modal-content">');
    print('<div class="modal-header">');
    print('<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>');
    print('<h4 class="modal-title">Help</h4>');
    print('</div>');
    print('<div class="modal-body">');
    print('<h4>Command <small id="command-reminder"></small></h4>');
    print('<p id="description-help"></p>');
    print('<h4>Parameter</h4>');
    print('<p id="parameter-help"></p>');
    print('</div>');
    print('<div class="modal-footer">');
    print('<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>');
    print('</div>');
    print('</div>');
    print('</div>');
    print('</div>');
  }

  public function render() {
    print('<!DOCTYPE html>');
    print('<html lang="en">');
    print('<head>');
    print('<meta charset="utf-8" />');
    print('<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />');
    print('<meta name="viewport" content="width=device-width, initial-scale=1" />');
    print('<meta name="keywords" content="Looking Glass, LG, BGP, prefix-list, AS-path, ASN, traceroute, ping, IPv4, IPv6, Cisco, Juniper, Internet" />');
    print('<meta name="description" content="'.$this->frontpage['title'].'" />');
    print('<title>'.htmlentities($this->frontpage['title']).'</title>');
    print('<link href="libs/bootstrap-3.3.7/css/bootstrap.min.css" rel="stylesheet" />');
    if ($this->frontpage['bootstrap_theme']) {
      print('<link href="libs/bootstrap-3.3.7/css/bootstrap-theme.min.css" rel="stylesheet" />');
    }
    if ($this->frontpage['custom_bootstrap_theme']) {
      print('<link href="'.$this->frontpage['custom_bootstrap_theme'].'" rel="stylesheet" />');
    }
    print('<link href="'.$this->frontpage['css'].'" rel="stylesheet" />');
    print('</head>');
    print('<body>');
    $this->render_header();
    $this->render_content();
    $this->render_footer();
    $this->render_help_modal();
    if ($this->frontpage['peering_policy_file']) {
      $this->render_peering_policy_modal();
    }
    print('</body>');
    print('<script src="libs/jquery-3.1.1.min.js"></script>');
    print('<script src="libs/bootstrap-3.3.7/js/bootstrap.min.js"></script>');
    print('<script src="js/looking-glass.js"></script>');
    print('</html>');
  }
}

$looking_glass = new LookingGlass($config);
$looking_glass->render();

// End of index.php
