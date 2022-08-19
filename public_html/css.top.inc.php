<?php
// $Header: /cvsroot/html2ps/css.top.inc.php,v 1.14 2006/11/11 13:43:52 Konstantin Exp $

require_once(HTML2PS_DIR.'value.top.php');

class CSSTop extends CSSPropertyHandler {
  function __construct() {
    CSSPropertyHandler::__construct(false, false);
    $this->_autoValue = (new ValueTop())->fromString('auto');
  }

  function _getAutoValue() {
    return $this->_autoValue->copy();
  }

  function default_value() { 
    return $this->_getAutoValue();
  }

  function getPropertyCode() {
    return CSS_TOP;
  }

  function getPropertyName() {
    return 'top';
  }

  function parse($value) { 
    return (new ValueTop())->fromString($value);
  }
}

(new CSS())->register_css_property(new CSSTop);

?>