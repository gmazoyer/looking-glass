<?php

/*
 * Looking Glass - An easy to deploy Looking Glass
 * Copyright (C) 2019 Guillaume Mazoyer <gmazoyer@gravitons.in>
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

class CommandBuilder {
  private $elements;

  public function __construct() {
    // Use an array instead of a string to help in element manipulations
    $this->elements = array();

    if (func_num_args() > 0) {
      $this->add(func_get_args());
    }
  }

  /**
   * Adds given parameters to the command.
   *
   * There are no parameters in this function signature but it stil uses them.
   *
   * @return CommandBuilder the instance used when calling this function.
   */
  public function add() {
    array_push($this->elements, func_get_args());
    return $this;
  }

  /**
   * Returns a string of all the elements that have been used in the builder.
   * Elements will be separated from each other by the given separator.
   *
   * @param  string $separator the string to use as a separator, the default
   *                           value is one space.
   * @return string a single string containing all elements separated by a
   *                separator.
   */
  public function to_string($separator = ' ') {
    $string = '';
    foreach ($elements as $element) {
      $string .= ' '.$element;
    }
    return $string;
  }

  /**
   * Returns a string of all the elements that have been used in the builder.
   *
   * @return string a single string with all elements separated by spaces.
   */
  public function __toString() {
    return $this->to_string();
  }
}

// End of command_builder.php
