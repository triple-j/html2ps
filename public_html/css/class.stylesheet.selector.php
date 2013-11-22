<?php

class CSSStylesheetSelector {
  var $_selectors;
  var $_combinators;

  function CSSStylesheetSelector() {
    $this->set_combinators(array());
    $this->set_selectors(array());
  }

  function add_combinator(&$combinator) {
    $this->_combinators[] =& $combinator;
  }

  function add_selector(&$selector) {
    $this->_selectors[] =& $selector;
  }

  function get_combinators() {
    return $this->_combinators;
  }

  function get_selector($index) {
    return $this->_selectors[$index];
  }

  function get_selectors() {
    return $this->_selectors;
  }

  function set_combinators($value) {
    $this->_combinators = $value;
  }

  function set_selectors($value) {
    $this->_selectors = $value;
  }
}

?>