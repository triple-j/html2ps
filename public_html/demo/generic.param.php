<?php

/**
 * Read a query parameter
 * If this parameter is not specified, return the default value
 * Remove magic quotes, if they're enabled
 * 
 */

function get_var($name, $array, $maxlength=255, $default=null) {
  /**
   * Check if this parameter is specified
   */
  if (!isset($array[$name])) { return $default; }

  /**
   * Read initial value of parameter
   */
  $data = $array[$name];

  if (!is_array($data)) {
    /**
     * Limit maximal length of passed data
     */
    $data = substr($data, 0, $maxlength);
  }

  return $data;
}
?>