<?php

class Anchor {
  var $name;
  var $page;
  var $x;
  var $y;

  function __construct($name, $page, $x, $y) {
    $this->name = $name;
    $this->page = $page;
    $this->x    = $x;
    $this->y    = $y;
  }
}

?>