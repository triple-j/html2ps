<?php

class CSSStylesheetTerm {
  var $_value;
  var $_unary_operator;
  var $_function_name;
  var $_function_param;

  function CSSStylesheetTerm() {
    $this->set_value(null);
    $this->set_unary_operator(null);
    $this->set_function_name(null);
  }

  function get_function_name() {
    return $this->_function_name;
  }

  function set_function_name($value) {
    $this->_function_name = $value;
  }

  function get_function_param() {
    return $this->_function_param;
  }

  function set_function_param($value) {
    $this->_function_param = $value;
  }

  function get_unary_operator() {
    return $this->_unary_operator;
  }

  function set_unary_operator($operator) {
    $this->_unary_operator = $operator;
  }

  function set_value($value) {
    $this->_value =& $value;
  }

  function to_string() {
    if ($this->get_unary_operator()) {
      $op = $this->get_unary_operator();
      return $op->to_string() . $this->_value;
    } elseif ($this->get_function_name()) {
      $param = $this->get_function_param();
      return $this->_function_name . $param->to_string() . ')';
    } else {
      return $this->_value;
    };
  }
}

?>