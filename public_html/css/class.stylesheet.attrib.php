<?php

class CSSStylesheetAttrib {
  var $_name;
  var $_op;
  var $_value;

  function CSSStylesheetAttrib() {
    $this->set_name(null);
    $this->set_op(null);
    $this->set_value(null);
  }

  function get_name() {
    return $this->_name;
  }

  function get_op() {
    return $this->_op;
  }

  function get_value() {
    return $this->_value;
  }

  function set_name($value) {
    $this->_name = $value;
  }

  function set_op($value) {
    $this->_op = $value;
  }

  function set_value($value) {
    $this->_value = $value;
  }
}

?>