<?php
// $Header: /cvsroot/html2ps/css.word-spacing.inc.php,v 1.2 2006/09/07 18:38:15 Konstantin Exp $

class CSSWordSpacing extends CSSPropertyHandler {
  var $_default_value;

  function __construct() {
    CSSPropertyHandler::__construct(false, true);

    $this->_default_value = (new Value())->fromString("0");
  }

  function default_value() { 
    return $this->_default_value;
  }

  function parse($value) {
    $value = trim($value);

    if ($value === 'inherit') {
      return CSS_PROPERTY_INHERIT;
    }

    if ($value === 'normal') { 
      return $this->_default_value; 
    }

    return (new Value())->fromString($value);
  }

  function getPropertyCode() {
    return CSS_WORD_SPACING;
  }

  function getPropertyName() {
    return 'word-spacing';
  }
}

(new CSS())->register_css_property(new CSSWordSpacing);

?>
