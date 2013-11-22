<?php

class CSSStylesheetUnaryOperator {
  var $_type;

  function CSSStylesheetUnaryOperator() {
    $this->set_type(null);
  }

  function get_type() {
    return $this->_type;
  }

  function set_type($value) {
    $this->_type = $value;
  }

  function to_string() {
    switch ($this->get_type()) {
    case UNARY_OPERATOR_PLUS:
      return '+';
    case UNARY_OPERATOR_MINUS:
      return '-';
    default: 
      return '';
    };
  }
}

?>