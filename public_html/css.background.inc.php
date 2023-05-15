<?php
// $Header: /cvsroot/html2ps/css.background.inc.php,v 1.22 2006/11/11 13:43:52 Konstantin Exp $

require_once(HTML2PS_DIR.'value.background.php');

class CSSBackground extends CSSPropertyHandler {
  var $default_value;

  function getPropertyCode() {
    return CSS_BACKGROUND;
  }

  function getPropertyName() {
    return 'background';
  }

  function __construct() {
    $this->default_value = new Background(
        (new CSSBackgroundColor())->default_value(),
        (new CSSBackgroundImage())->default_value(),
        (new CSSBackgroundRepeat())->default_value(),
        (new CSSBackgroundPosition())->default_value()
    );

    CSSPropertyHandler::__construct(true, false);
  }

  function inherit($state, &$new_state) { 
    // Determine parent 'display' value
    $parent_display = $state[CSS_DISPLAY];

    // If parent is a table row, inherit the background settings
    $this->replace_array(($parent_display == 'table-row') ? $state[CSS_BACKGROUND] : $this->default_value(),
                         $new_state);
  }

  function default_value() {
    return $this->default_value->copy();
  }

  function parse($value, &$pipeline) {
    if ($value === 'inherit') {
      return CSS_PROPERTY_INHERIT;
    }

    $background = new Background((new CSSBackgroundColor())->parse($value),
                                 (new CSSBackgroundImage())->parse($value, $pipeline),
                                 (new CSSBackgroundRepeat())->parse($value),
                                 (new CSSBackgroundPosition())->parse($value));

    return $background;
  }
}

$bg = new CSSBackground;

(new CSS())->register_css_property($bg);
(new CSS())->register_css_property(new CSSBackgroundColor($bg, '_color'));
(new CSS())->register_css_property(new CSSBackgroundImage($bg, '_image'));
(new CSS())->register_css_property(new CSSBackgroundRepeat($bg, '_repeat'));
(new CSS())->register_css_property(new CSSBackgroundPosition($bg, '_position'));

?>