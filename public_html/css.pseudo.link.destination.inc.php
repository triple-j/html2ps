<?php

class CSSPseudoLinkDestination extends CSSPropertyHandler {
  function __construct() {
    CSSPropertyHandler::__construct(false, false);
  }

  function default_value() { 
    return ""; 
  }

  function parse($value) { 
    return $value;
  }

  function getPropertyCode() {
    return CSS_HTML2PS_LINK_DESTINATION;
  }

  function getPropertyName() {
    return '-html2ps-link-destination';
  }
}

(new CSS())->register_css_property(new CSSPseudoLinkDestination);

?>