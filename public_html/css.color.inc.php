<?php
// $Header: /cvsroot/html2ps/css.color.inc.php,v 1.13 2007/01/24 18:55:51 Konstantin Exp $

class CSSColor extends CSSPropertyHandler {
  function __construct() {
    CSSPropertyHandler::__construct(true, true);
  }

  function default_value() { 
    return new Color(array(0,0,0),false); 
  }

  static function parse($value) {
    if ($value === 'inherit') {
      return CSS_PROPERTY_INHERIT;
    }

    return parse_color_declaration($value);
  }

  function getPropertyCode() {
    return CSS_COLOR;
  }

  function getPropertyName() {
    return 'color';
  }
}

(new CSS())->register_css_property(new CSSColor);

?>