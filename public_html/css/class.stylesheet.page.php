<?php

class CSSStylesheetPage {
  var $_declarations;

  function CSSStylesheetPage() {
    $this->_declarations = new CSSStylesheetDeclarationCollection();
  }

  function add_declarations(&$declaration_collection) {
    $this->_declarations->append($declaration_collection);
  }
}

?>