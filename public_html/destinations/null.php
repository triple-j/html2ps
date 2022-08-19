<?php

class DestinationNull extends Destination {
  function __construct() {
    Destination::__construct('');
  }

  function process($filename, $content_type) {
    // Do nothing
  }
}

?>