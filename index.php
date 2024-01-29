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

require_once('includes/config.defaults.php');
require_once('includes/captcha.php');
require_once('includes/utils.php');
require_once('config.php');

final class LookingGlass {
  private $release;
  private $frontpage;
  private $contact;
  private $misc;
  private $captcha;
  private $routers;
  private $routing_instances;
  private $doc;

  public function __construct($config) {
    set_defaults_for_routers($config);

    $this->release = $config['release'];
    $this->frontpage = $config['frontpage'];
    $this->contact = $config['contact'];
    $this->misc = $config['misc'];
    $this->captcha = new Captcha($config['captcha']);
    $this->routers = $config['routers'];
    $this->doc = $config['doc'];
    $this->routing_instances = $config['routing_instances'];
  }

  private function router_count() {
    if ($this->frontpage['router_count'] > 0)
      return $this->frontpage['router_count'];
    else
      return count($this->routers);
  }

  private function command_count() {
    if ($this->frontpage['command_count'] > 0)
      return $this->frontpage['command_count'];
    else
      $command_count = 0;
      foreach (array_keys($this->doc) as $cmd) {
        if (isset($this->doc[$cmd]['command'])) {
          $command_count++;
        }
      }
      return $command_count;
  }

  private function render_routers() {
    print('<div class="mb-3">');
    print('<label class="form-label" for="routers">Router to use</label>');
    print('<select size="'.$this->router_count().'" class="form-select" name="routers" id="routers">');

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
    print('<div class="mb-3">');
    print('<label class="form-label" for="query">Command to issue</label>');
    print('<select size="'.$this->command_count().'" class="form-select" name="query" id="query">');
    $selected = ' selected="selected"';
    foreach (array_keys($this->doc) as $cmd) {
      if (isset($this->doc[$cmd]['command'])) {
        print('<option value="'.$cmd.'"'.$selected.'>'.$this->doc[$cmd]['command'].'</option>');
      }
      $selected = '';
    }
    print('</select>');
    print('</div>');
  }

  private function render_parameter() {
    if ($this->frontpage['show_visitor_ip']) {
      $requester = htmlentities(get_requester_ip());
    } else {
      $requester = "";
    }
    print('<div class="mb-3">');
    print('<label class="form-label for="input-param">Parameter</label>');
    print('<div class="input-group mb-3">');
    print('<input class="form-control" name="parameter" id="input-param" autofocus value="'.$requester.'" />');
    print('<button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#help">');
    print('<i class="bi bi-question-circle-fill"></i> Help');
    print('</button>');
    print('</div>');
    print('</div>');
  }

  private function render_routing_instances() {
    $routing_instances_count = count($this->routing_instances);
    if ($routing_instances_count === 0) {
        return;
    }

    print('<div class="mb-3">');
    print('<label class="form-label" for="routing-instances">Routing instance</label>');
    print('<select size="'.($routing_instances_count + 1).'" class="form-select" name="routing_instance" id="routing-instances">');
    print('<option value="none" selected>None</option>');
    foreach ($this->routing_instances as $name => $display) {
      print('<option value="'.$name.'">'.$display.'</option>');
    }
    print('</select>');
    print('</div>');
  }

  private function render_buttons() {
    $this->captcha->render();
    print('<div class="row">');
    print('<div class="col-4 offset-4 btn-group btn-group-lg">');
    print('<button class="btn btn-primary" id="send" type="submit">Enter</button>');
    print('<button class="btn btn-danger" id="clear" type="reset">Reset</button>');
    print('</div>');
    print('</div>');
  }

  private function render_header() {
    if ($this->frontpage['header_link']) {
      print('<a href="'.$this->frontpage['header_link'].'" class="text-decoration-none text-secondary-emphasis" title="Home">');
    }
    print('<div class="header_bar text-center mx-auto">');
    if ($this->frontpage['show_title']) {
      print('<h1>'.htmlentities($this->frontpage['title']).'</h1><br>');
    }
    if ($this->frontpage['image']) {
      print('<img src="'.$this->frontpage['image'].'" alt="Logo"');
      if ((int) $this->frontpage['image_width'] > 0) {
        print(' width="'.$this->frontpage['image_width'].'"');
      }
      if ((int) $this->frontpage['image_height'] > 0) {
        print(' height="'.$this->frontpage['image_height'].'"');
      }
      print('/>');
    }
    print('</div>');
    if ($this->frontpage['header_link']) {
      print('</a>');
    }
  }

  private function render_content() {
    print('<div class="alert alert-danger alert-dismissible" id="error">');
    print('<strong>Error!</strong>&nbsp;<span id="error-text"></span>');
    print('<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>');
    print('</div>');
    print('<div class="content text-center" id="command_options">');
    print('<form role="form" action="execute.php" method="post">');
    print('<fieldset id="command_properties">');

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

        case 'routing_instances':
          $this->render_routing_instances();
          break;

        default:
          break;
      }
    }

    print('<input type="text" class="d-none" name="dontlook" placeholder="Don\'t look at me!" />');
    print('<fieldset>');
    print('</form>');
    print('</div>');
    print('<div class="loading">');
    print('<div class="progress">');
    print('<div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">');
    print('</div>');
    print('</div>');
    print('</div>');
    print('<div class="result">');
    print('<div id="output"></div>');
    print('<div class="row">');
    print('<div class="col-4 offset-4 btn-group btn-group-lg">');
    print('<button class="btn btn-danger" id="backhome">Reset</button>');
    print('</div>');
    print('</div>');
    print('</div>');
  }

  private function render_footer() {
    print('<div class="footer_bar text-center">');
    print('<p class="text-center">');

    if ($this->frontpage['show_visitor_ip']) {
      print('Your IP address: <code>'.htmlentities(get_requester_ip()).'</code><br>');
    }

    if ($this->frontpage['disclaimer']) {
      print($this->frontpage['disclaimer']);
      print('<br><br>');
    }

    if ($this->frontpage['peering_policy_file']) {
      print('<button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#peering-policy"><i class="bi bi-list-task"></i> Peering Policy</button>');
      print('<br><br>');
    }

    if ($this->contact['name'] && $this->contact['mail']) {
      print('Contact:&nbsp;');
      print('<a href="mailto:'.$this->contact['mail'].'" class="text-decoration-none text-secondary-emphasis">'.
        htmlentities($this->contact['name']).'</a>');
    }

    print('<br><br>');
    print('<span class="origin">Powered by <a href="'.$this->release['repository'].'" class="text-decoration-none text-secondary-emphasis" title="Looking Glass Project">Looking Glass '.$this->release['version'].'</a></span>');
    print('</p>');
    print('</div>');
  }

  private function render_peering_policy_modal() {
    print('<div id="peering-policy" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">');
    print('<div class="modal-dialog modal-dialog-centered modal-lg">');
    print('<div class="modal-content">');
    print('<div class="modal-header">');
    print('<h5 class="modal-title">Peering Policy</h5>');
    print('<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>');
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
    print('<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>');
    print('</div>');
    print('</div>');
    print('</div>');
    print('</div>');
  }

  private function render_help_modal() {
    print('<div id="help" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">');
    print('<div class="modal-dialog modal-dialog-centered modal-lg">');
    print('<div class="modal-content">');
    print('<div class="modal-header">');
    print('<h5 class="modal-title">Help</h5>');
    print('<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>');
    print('</div>');
    print('<div class="modal-body">');
    print('<h4>Command <span class="badge badge-dark"><small id="command-reminder"></small></span></h4>');
    print('<p id="description-help"></p>');
    print('<h4>Parameter</h4>');
    print('<p id="parameter-help"></p>');
    print('</div>');
    print('<div class="modal-footer">');
    print('<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>');
    print('</div>');
    print('</div>');
    print('</div>');
    print('</div>');
  }

  private function render_color_mode_switch() {
    print('<svg xmlns="http://www.w3.org/2000/svg" class="d-none">');
    print('<symbol id="check2" viewBox="0 0 16 16">');
    print('<path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/>');
    print('</symbol>');
    print('<symbol id="circle-half" viewBox="0 0 16 16">');
    print('<path d="M8 15A7 7 0 1 0 8 1v14zm0 1A8 8 0 1 1 8 0a8 8 0 0 1 0 16z"/>');
    print('</symbol>');
    print('<symbol id="moon-stars-fill" viewBox="0 0 16 16">');
    print('<path d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278z"/>');
    print('<path d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.734 1.734 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.734 1.734 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.734 1.734 0 0 0 1.097-1.097l.387-1.162zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.156 1.156 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.156 1.156 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732L13.863.1z"/>');
    print('</symbol>');
    print('<symbol id="sun-fill" viewBox="0 0 16 16">');
    print('<path d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z"/>');
    print('</symbol>');
    print('</svg>');
    print('<div class="dropdown dropup position-fixed bottom-0 end-0 mb-3 me-3 color-mode-toggle">');
    print('<button class="btn btn-secondary py-2 dropdown-toggle d-flex align-items-center" id="bd-theme" type="button" aria-expanded="false" data-bs-toggle="dropdown" aria-label="Toggle theme (auto)">');
    print('<svg class="bi my-1 theme-icon-active" width="1em" height="1em"><use href="#circle-half"></use></svg>');
    print('<span class="visually-hidden" id="bd-theme-text">Toggle theme</span>');
    print('</button>');
    print('<ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="bd-theme-text">');
    print('<li>');
    print('<button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="light" aria-pressed="false">');
    print('<svg class="bi me-2 opacity-50 theme-icon" width="1em" height="1em"><use href="#sun-fill"></use></svg>');
    print('Light');
    print('<svg class="bi ms-auto d-none" width="1em" height="1em"><use href="#check2"></use></svg>');
    print('</button>');
    print('</li>');
    print('<li>');
    print('<button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="dark" aria-pressed="false">');
    print('<svg class="bi me-2 opacity-50 theme-icon" width="1em" height="1em"><use href="#moon-stars-fill"></use></svg>');
    print('Dark');
    print('<svg class="bi ms-auto d-none" width="1em" height="1em"><use href="#check2"></use></svg>');
    print('</button>');
    print('</li>');
    print('<li>');
    print('<button type="button" class="dropdown-item d-flex align-items-center active" data-bs-theme-value="auto" aria-pressed="true">');
    print('<svg class="bi me-2 opacity-50 theme-icon" width="1em" height="1em"><use href="#circle-half"></use></svg>');
    print('Auto');
    print('<svg class="bi ms-auto d-none" width="1em" height="1em"><use href="#check2"></use></svg>');
    print('</button>');
    print('</li>');
    print('</ul>');
    print('</div>');
  }

  public function render() {
    print('<!DOCTYPE html>');
    print('<html lang="en">');
    print('<head>');
    print('<meta charset="utf-8">');
    print('<meta name="viewport" content="width=device-width, initial-scale=1">');
    print('<meta name="keywords" content="Looking Glass, LG, BGP, prefix-list, AS-path, ASN, traceroute, ping, IPv4, IPv6, Cisco, Juniper, Internet">');
    print('<meta name="description" content="'.$this->frontpage['title'].'">');
    if ($this->frontpage['additional_html_header']) {
      print($this->frontpage['additional_html_header']);
    }
    print('<title>'.htmlentities($this->frontpage['title']).'</title>');
    print('<link href="vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">');
    print('<link href="vendor/twbs/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">');
    print('<link href="'.$this->frontpage['css'].'" rel="stylesheet">');
    print('</head>');
    print('<body class="d-flex flex-column h-100">');
    if ($this->frontpage['color_mode_enabled']) {
      $this->render_color_mode_switch();
    }
    print('<main class="flex-shrink-0">');
    print('<div class="container">');
    $this->render_header();
    $this->render_content();
    $this->render_footer();
    $this->render_help_modal();
    if ($this->frontpage['peering_policy_file']) {
      $this->render_peering_policy_modal();
    }
    print('</div>');
    print('</main>');
    print('</body>');
    print('<script src="vendor/components/jquery/jquery.min.js"></script>');
    print('<script src="vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>');
    print('<script src="js/looking-glass.js"></script>');
    if ($this->frontpage['color_mode_enabled']) {
      print('<script src="js/color-mode.js"></script>');
    }
    $this->captcha->render_script();
    print('</html>');
  }
}

$looking_glass = new LookingGlass($config);
$looking_glass->render();

// End of index.php
