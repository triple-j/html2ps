<?php

class CSSStylesheetSimpleSelector {
  var $_ids;
  var $_attribs;
  var $_element;
  var $_classes;
  var $_pseudo;

  function CSSStylesheetSimpleSelector() {
    $this->set_ids(array());
    $this->set_attribs(array());
    $this->set_element(null);
    $this->set_classes(array());
    $this->set_pseudo(array());
  }

  function add_attrib($attrib) {
    $this->_attribs[] = $attrib;
  }

  function add_id($id) {
    $this->_ids[] = $id;
  }

  function add_class($class) {
    $this->_classes[] = $class;
  }

  function add_pseudo($pseudo) {
    $this->_pseudo[] = $pseudo;
  }

  function get_attribs() {
    return $this->_attribs;
  }

  function get_classes() {
    return $this->_classes;
  }

  function get_element() {
    return $this->_element;
  }

  function get_ids() {
    return $this->_ids;
  }

  function get_pseudo() {
    return $this->_pseudo;
  }

  function set_attribs($value) {
    $this->_attribs = $value;
  }

  function set_classes($value) {
    $this->_classes = $value;
  }

  function set_element($value) {
    $this->_element = $value;
  }

  function set_ids($value) {
    $this->_ids = $value;
  }

  function set_pseudo($value) {
    $this->_pseudo = $value;
  }
}

?>