<?php

class CSSStylesheet {
  var $_charset;
  var $_imports;
  var $_pages;
  var $_rulesets;
  var $_media;

  function CSSStylesheet() {
    $this->set_charset(null);
    $this->set_imports(array());
    $this->set_pages(array());
    $this->set_rulesets(new CSSStylesheetRulesetCollection());
    $this->set_media(array());
  }
  
  function add_import(&$import) {
    $this->_imports[] =& $import;
  }

  function add_media(&$value) {
    $this->_media[] =& $value;
  }

  function add_page(&$page) {
    $this->_pages[] =& $page;
  }

  function add_ruleset($ruleset) {
    $this->_rulesets->add($ruleset);
  }

  function get_imports() {
    return $this->_imports;
  }

  function get_media() {
    return $this->_media;
  }

  function get_rulesets() {
    return $this->_rulesets;
  }

  function set_charset($charset) {
    $this->_charset = $charset;
  }

  function set_imports($value) {
    $this->_imports = $value;
  }

  function set_media($value) {
    $this->_media = $value;
  }

  function set_pages($value) {
    $this->_pages = $value;
  }

  function set_rulesets(&$value) {
    $this->_rulesets =& $value;
  }
}

?>