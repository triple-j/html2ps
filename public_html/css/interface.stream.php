<?php

class ICSSStream {
  function next($how_many = 1) { die('ICCStream::next - implement me!'); }
  function peek($how_many = 1) { die('ICCStream::peek - implement me!'); }
  function read_expected($string, &$buffer) { die('ICCStream::read_expected - implement me!'); }
  function get_context_before($how_many = 1) { die('ICCStream::get_context_before - implement me!'); }
  function get_context_after($how_many = 1) { die('ICCStream::get_context_after - implement me!'); }
}

?>
