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

class Captcha {
  private $config;

  public function __construct($config) {
    $this->config = $config;
  }

  /**
   * Validate hCAPTCHA.
   * 
   * @return boolean true if the captcha is validated, false otherwise.
   */
  private function validate_hcaptcha($requester) {
    if (!$this->config['enabled']) {
      return true;
    }

    $options = array(
      // http even if it is https
      'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query(
          array(
            'secret' => $config['secret'],
            'response' => $_POST['h-captcha-response'],
            'remoteip' => $requester
          )
        )
      )
    );

    $context  = stream_context_create($options);
    $output = file_get_contents($config['url'], false, $context);
    $captcha = json_decode($output, true);

    return $captcha["success"] == true;
  }

  /**
   * Validate reCAPTCHA.
   * 
   * @return boolean true if the captcha is validated, false otherwise.
   */
  private function validate_recaptcha($requester) {
    if (!$this->config['enabled']) {
      return true;
    }

    $url = $this->config['url'].'?secret='.$this->config['secret'].
           '&response='.$_POST['g-recaptcha-response'].'&remoteip='.
           $requester;

    $verify = file_get_contents($url);
    $captcha = json_decode($verify, true);

    return $captcha["success"] == true;
  }

  /**
   * Render hCAPTCHA user interface elements. Script is not included and must
   * be rendered separately.
   */
  private function render_hcaptcha() {
    if (!$this->config['enabled'] && !isset($this->config['apikey'])) {
      return;
    }

    print('<div class="form-group d-flex justify-content-center pb-2">');
    print('<div class="h-captcha" data-sitekey="'.$this->config['apikey'].'"></div>');
    print('</div>');
  }

  /**
   * Render reCAPTCHA user interface elements. Script is not included and must
   * be rendered separately.
   */
  private function render_recaptcha() {
    if (!$this->config['enabled'] && !isset($this->config['apikey'])) {
      return;
    }

    print('<div class="form-group d-flex justify-content-center pb-2">');
    print('<div class="g-recaptcha" data-sitekey="'.$this->config['apikey'].'"></div>');
    print('</div>');
  }

  /**
   * Render hCAPTCHA script.
   */
  private function render_script_hcaptcha() {
    if (!$this->config['enabled']) {
      return;
    }

    print('<script src="https://js.hcaptcha.com/1/api.js" async defer></script>');
  }

  /**
   * Render reCAPTCHA script.
   */
  private function render_script_recaptcha() {
    if (!$this->config['enabled']) {
      return;
    }

    print('<script src="https://www.google.com/recaptcha/api.js" async defer></script>');
  }

  /**
   * Perform captcha rendering depending on its type.
   */
  public function render() {
    switch ($this->config['type']) {
      case 'hcaptcha':
        $this->render_hcaptcha();
        break;

      case 'recaptcha':
        $this->render_recaptcha();
        break;

      default:
        break;
    }
  }

  /**
   * Perform captcha script rendering depending on its type.
   */
  public function render_script() {
    switch($this->config['type']) {
      case 'hcaptcha':
        $this->render_script_hcaptcha();
        break;

      case 'recaptcha':
        $this->render_script_recaptcha();
        break;

      default:
        break;
    }
  }

  /**
   * Perform captcha validation depending on its type.
   * 
   * @return boolean true if the captcha is validated, false otherwise.
   */
  public function validate($requester) {
    switch ($this->config['type']) {
      case 'hcaptcha':
        return $this->validate_hcaptcha($requester);

      case 'recaptcha':
        return $this->validate_recaptcha($requester);

      default:
        print('Unknown captcha type "'.$this->config['type'].'".');
        return false;
    }
  }
}
