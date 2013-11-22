<?php

class CSSStylesheetExpr {
  var $_sequence;

  function CSSStylesheetExpr() {
    $this->set_sequence(array());
  }

  function add_term(&$term) {
    $this->_sequence[] =& $term;
  }

  function add_operator(&$term) {
    $this->_sequence[] =& $term;
  }

  function set_sequence($value) {
    $this->_sequence = $value;
  }

  function to_string() {
    $string = join(' ', array_map(array($this, 'to_string_element'), $this->_sequence));
    return $string;
  }

  function to_string_element($element) {
    return $element->to_string();
  }
}

?>