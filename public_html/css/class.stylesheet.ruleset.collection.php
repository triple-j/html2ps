<?php

class CSSStylesheetRulesetCollection {
  var $_rulesets;

  function CSSStylesheetRulesetCollection() {
    $this->set(array());
  }

  function add(&$value) {
    $this->_rulesets[] =& $value;
  }

  function append(&$value) {
    $this->_rulesets = array_merge($this->_rulesets,
                                   $value->_rulesets);
  }

  function get() {
    return $this->_rulesets;
  }

  function set($value) {
    $this->_rulesets = $value;
  }
}

?>