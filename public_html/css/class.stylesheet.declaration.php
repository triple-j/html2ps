<?php

class CSSStylesheetDeclaration {
  var $_property;
  var $_expr;
  var $_important;

  function CSSStylesheetDeclaration() {
    $this->set_property(null);
    $this->set_expr(null);
    $this->set_important(false);
  }

  function get_expr() {
    return $this->_expr;
  }

  function get_important() {
    return $this->_important;
  }

  function get_property() {
    return $this->_property;
  }

  function is_empty() {
    return 
      is_null($this->_property) && 
      is_null($this->_expr);
  }

  function set_expr($expr) {
    $this->_expr =& $expr;
  }

  function set_important($important) {
    $this->_important = $important;
  }

  function set_property($property) {
    $this->_property =& $property;
  }
}

?>