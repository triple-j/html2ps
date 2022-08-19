<?php
// $Header: /cvsroot/html2ps/css.list-style.inc.php,v 1.8 2007/02/04 17:08:19 Konstantin Exp $

require_once(HTML2PS_DIR.'value.list-style.class.php');

class CSSListStyle extends CSSPropertyHandler {
  // CSS 2.1: list-style is inherited
  function __construct() {
    $this->default_value = new ListStyleValue;
    $this->default_value->image    = (new CSSListStyleImage())->default_value();
    $this->default_value->position = (new CSSListStylePosition())->default_value();
    $this->default_value->type     = (new CSSListStyleType())->default_value();

    CSSPropertyHandler::__construct(true, true);
  }

  function parse($value, &$pipeline) { 
    $style = new ListStyleValue;
    $style->image     = (new CSSListStyleImage())->parse($value, $pipeline);
    $style->position  = (new CSSListStylePosition())->parse($value);
    $style->type      = (new CSSListStyleType())->parse($value);

    return $style;
  }

  function default_value() { return $this->default_value; }

  function getPropertyCode() {
    return CSS_LIST_STYLE;
  }

  function getPropertyName() {
    return 'list-style';
  }
}

$ls = new CSSListStyle;
(new CSS())->register_css_property($ls);
(new CSS())->register_css_property(new CSSListStyleImage($ls,    'image'));
(new CSS())->register_css_property(new CSSListStylePosition($ls, 'position'));
(new CSS())->register_css_property(new CSSListStyleType($ls,     'type'));

?>