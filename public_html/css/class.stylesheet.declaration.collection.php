<?php

class CSSStylesheetDeclarationCollection {
  var $_declarations;

  function CSSStylesheetDeclarationCollection() {
    $this->set(array());
  }

  function add(&$value) {
    if ($value->is_empty()) {
      return;
    };

    $this->_declarations[] =& $value;
  }

  function append(&$collection) {
    $this->_declarations = array_merge($this->_declarations,
                                       $collection->_declarations);
  }

  function get() {
    return $this->_declarations;
  }

  function get_size() {
    return count($this->_declarations);
  }

  function set($value) {
    $this->_declarations = $value;
  }
}

?>