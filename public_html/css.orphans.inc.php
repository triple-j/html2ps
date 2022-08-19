<?php

class CSSOrphans extends CSSPropertyHandler {
  function __construct() {
    CSSPropertyHandler::__construct(true, false);
  }

  function default_value() { 
    return 2; 
  }

  function parse($value) {
    return (int)$value;
  }

  function getPropertyCode() {
    return CSS_ORPHANS;
  }

  function getPropertyName() {
    return 'orphans';
  }
}

(new CSS())->register_css_property(new CSSOrphans);

?>