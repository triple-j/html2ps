<?php

class CSSStylesheetOperator {
  var $_type;

  function CSSStylesheetOperator() {
    $this->set_type(null);
  }

  function get_type() {
    return $this->_type;
  }

  function set_type($type) {
    $this->_type = $type;
  }

  function to_string() {
    $strings = array(OPERATOR_SLASH => '/',
                     OPERATOR_COMMA => ',',
                     OPERATOR_EMPTY => '');
    return $strings[$this->get_type()];
  }
}

?>