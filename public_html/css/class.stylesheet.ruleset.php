<?php

class CSSStylesheetRuleset {
  var $_selectors;
  var $_declarations;

  function CSSStylesheetRuleset() {
    $this->set_selectors(new CSSStylesheetSelectorCollection());
    $this->set_declarations(new CSSStylesheetDeclarationCollection());
  }

  function add_declarations(&$declaration_collection) {
    $this->_declarations->append($declaration_collection);
  }

  function add_selectors(&$selector_collection) {
    $this->_selectors->append($selector_collection);
  }

  function get_declarations() {
    return $this->_declarations;
  }

  function get_selectors() {
    return $this->_selectors;
  }

  function set_declarations($value) {
    $this->_declarations = $value;
  }

  function set_selectors($value) {
    $this->_selectors = $value;
  }
}

?>