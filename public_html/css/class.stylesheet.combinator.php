<?php

class CSSStylesheetCombinator {
  var $_type;

  function CSSStylesheetCombinator() {
    $this->set_type(null);
  }

  function get_type() {
    return $this->_type;
  }

  function set_type($value) {
    $this->_type = $value;
  }
}

?>