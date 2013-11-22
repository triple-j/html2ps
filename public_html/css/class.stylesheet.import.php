<?php

class CSSStylesheetImport {
  var $_url;
  var $_media;

  function CSSStylesheetImport() {
    $this->set_url(null);
    $this->set_media(array());
  }

  function add_medium($value) {
    $this->_media[] = $value;
  }

  function get_media() {
    return $this->_media;
  }

  function get_url() {
    return $this->_url;
  }

  function set_url($value) {
    $this->_url = $value;
  }

  function set_media($value) {
    $this->_media = $value;
  }
}

?>