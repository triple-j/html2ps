<?php

class CSSStylesheetPseudo {
  var $_name;

  function CSSStylesheetPseudo() {
    $this->set_name(null);
  }

  function get_name() {
    return $this->_name;
  }

  function set_name($value) {
    $this->_name = $value;
  }
}

?>