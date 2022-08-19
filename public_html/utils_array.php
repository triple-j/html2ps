<?php
// $Header: /cvsroot/html2ps/utils_array.php,v 1.7 2006/09/07 18:38:16 Konstantin Exp $

function any_flag_set(&$flags) {
  for ($i=0, $size = is_countable($flags) ? count($flags) : 0; $i<$size; $i++) {
    if ($flags[$i]) { return true; }
  }
  return false;
}

function expand_to_with_flags($size, $array, $flags) {
  // if array have no elements - return immediately 
  if (is_countable($array) && count($array) == 0) { return $array; }

  // Never decrease exising values
  if (array_sum($array) > $size) {
    return $array;
  }

  // Subtract non-modifiable values from target value
  for ($i=0; $i < count($array); $i++) {
    if (!$flags[$i]) { $size -= $array[$i]; }
  }

  // Check if there's any expandable columns
  $sum = 0;
  for ($i=0, $count = is_countable($flags) ? count($flags) : 0; $i<$count; $i++) {
    if ($flags[$i]) { $sum += $array[$i]; }
  }

  if ($sum == 0) {
    // Note that this function is used in colpans-width calculation routine
    // If we executing this branch, then we've got a colspan over non-resizable columns
    // So, we decide to expand the very first column; note that 'Size' in this case
    // will contain the delta value for the width and we need to _add_ it to the first
    // column's width
    $array[0] += $size;
    return $array;
  }

  // Calculate scale koeff
  $koeff = $size / $sum;

  // Apply scale koeff
  for ($i=0, $count = is_countable($flags) ? count($flags) : 0; $i < $count; $i++) {
    if ($flags[$i]) { $array[$i] *= $koeff; }
  }  

  return $array;
}

function expand_to($size, $array) {
  // if array have no elements - return immediately 
  if (is_countable($array) && count($array) == 0) { return $array; }

  // If array contains only zero elements (or no elements at all) do not do anything
  if (array_sum($array) == 0) { return $array; }

  // Never decrease exising values
  if (array_sum($array) > $size) {
    return $array;
  }

  // Calculate scale koeff
  $koeff = $size / array_sum($array);

  // Apply scale koeff
  for ($i=0, $size = is_countable($array) ? count($array) : 0; $i<$size; $i++) {
    $array[$i] *= $koeff;
  }  

  return $array;
}
?>