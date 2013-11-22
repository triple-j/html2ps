<?php

require_once(HTML2PS_DIR.'css/interface.stream.php');

class CSSStreamString extends ICSSStream {
  var $_string;
  var $_pos;

  function CSSStreamString($string) {
    $this->_string = $string;
    $this->_pos = 0;
  }

  function get_context_before($how_many = 1) { 
    return substr($this->_string,
                  max(0, $this->_pos - $how_many),
                  min($this->_pos, $how_many));
  }

  function get_context_after($how_many = 1) { 
    return substr($this->_string,
                  $this->_pos,
                  $how_many);
  }

  function next($how_many = 1) {
    $this->_pos += $how_many;
  }

  function peek($how_many = 1) {
    return substr($this->_string, $this->_pos, $how_many);
  }

  function read_expected($string, &$buffer) { 
    for ($i = 0, $size = strlen($string); $i < $size; $i++) {
      $char = $this->peek();
      if ($char !== $string[$i]) {
        return false;
      };

      $this->next();
      $buffer .= $char;
    };

    return true;
  }
}

?>