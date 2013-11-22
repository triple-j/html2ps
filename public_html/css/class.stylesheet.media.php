<?php

class CSSStylesheetMedia {
  var $_media;
  var $_rulesets;

  function CSSStylesheetMedia() {
    $this->set_media(array());
    $this->set_rulesets(new CSSStylesheetRulesetCollection());
  }

  function add_medium($value) {
    $this->_media[] = $value;
  }

  function add_rulesets($ruleset) {
    $this->_rulesets->append($ruleset);
  }

  function get_media() {
    return $this->_media;
  }
  
  function get_rulesets() {
    return $this->_rulesets;
  }

  function set_media($value) {
    $this->_media = $value;
  }

  function set_rulesets(&$value) {
    $this->_rulesets =& $value;
  }
}

?>