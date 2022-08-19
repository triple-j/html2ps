<?php

class CSSPseudoFormRadioGroup extends CSSPropertyHandler {
  function __construct() {
    CSSPropertyHandler::__construct(true, true);
  }

  function default_value() { 
    return null; 
  }

  function parse($value) { 
    return $value;
  }

  function getPropertyCode() {
    return CSS_HTML2PS_FORM_RADIOGROUP;
  }

  function getPropertyName() {
    return '-html2ps-form-radiogroup';
  }
}

(new CSS())->register_css_property(new CSSPseudoFormRadioGroup);

?>