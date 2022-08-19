<?php

class CSSHTML2PSPixels extends CSSPropertyHandler {
  function __construct() {
    CSSPropertyHandler::__construct(false, false);
  }

  function &default_value() { 
    $value = 800;
    return $value;
  }

  function &parse($value) {
    $value_data = (int)$value;
    return $value_data;
  }

  function get_property_code() {
    return CSS_HTML2PS_PIXELS;
  }

  function get_property_name() {
    return '-html2ps-pixels';
  }
}

(new CSS())->register_css_property(new CSSHTML2PSPixels);

?>