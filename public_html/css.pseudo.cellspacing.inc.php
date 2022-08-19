<?php
// $Header: /cvsroot/html2ps/css.pseudo.cellspacing.inc.php,v 1.6 2006/09/07 18:38:14 Konstantin Exp $

class CSSCellSpacing extends CSSPropertyHandler {
  function __construct() {
    CSSPropertyHandler::__construct(true, false);
  }

  function default_value() { 
    return (new Value())->fromData(1, UNIT_PX);
  }

  function parse($value) { 
    return (new Value())->fromString($value);
  }

  function getPropertyCode() {
    return CSS_HTML2PS_CELLSPACING;
  }

  function getPropertyName() {
    return '-html2ps-cellspacing';
  }
}

(new CSS())->register_css_property(new CSSCellSpacing);

?>