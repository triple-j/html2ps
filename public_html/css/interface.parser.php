<?php

// We don't have _real_ interfaces in PHP 4, do we?
// So, let's make an "abstract" class instead

class ICSSParser {
  function get_errors() { die('ICSSParser::get_errors - imlement me!'); }
  function parse($css) { die('ICSSParser::parse_style - implement me!'); }
  function parse_style($style_value) { die('ICSSParser::parse_style - implement me!'); }
}

?>