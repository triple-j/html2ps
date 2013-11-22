<?php

require_once(HTML2PS_DIR.'misc/logger.php');

class CSSParserError {
  var $_line;
  var $_context_before;
  var $_context_after;
  var $_skipped_content;

  function CSSParserError($line, $context_before, $context_after, $skipped_content) {
    $this->set_line($line);
    $this->set_context_before($context_before);
    $this->set_context_after($context_after);
    $this->set_skipped_content($skipped_content);
  }

  function get_skipped_content() {
    return $this->_skipped_content;
  }

  function get_line() {
    return $this->_line;
  }

  function get_context_after() {
    return $this->_context_after;
  }

  function get_context_before() {
    return $this->_context_before;
  }

  function log($source_name) {
    $text = sprintf('CSS parse error in \'%s\' at line %s (%s[[here]]%s)',
                    $source_name,
                    $this->get_line(),
                    $this->get_context_before(),
                    $this->get_context_after());

    $logger = Logger::get_instance();
    $logger->log($text);
  }

  function set_skipped_content($value) {
    $this->_skipped_content = $value;
  }

  function set_line($value) {
    $this->_line = $value;
  }

  function set_context_after($context_after) {
    $this->_context_after = $context_after;
  }

  function set_context_before($context_before) {
    $this->_context_before = $context_before;
  }
}

?>