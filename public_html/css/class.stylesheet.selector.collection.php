<?php

class CSSStylesheetSelectorCollection {
  var $_selectors;

  function CSSStylesheetSelectorCollection() {
    $this->set(array());
  }

  function add(&$value) {
    $this->_selectors[] =& $value;
  }

  function append(&$collection) {
    $this->_selectors = array_merge($this->_selectors,
                                    $collection->_selectors);
  }
  
  function get($index = null) {
    if (is_null($index)) {
      return $this->_selectors;
    } else {
      return $this->_selectors[$index];
    };
  }

  function set($value) {
    $this->_selectors = $value;
  }

}

?>