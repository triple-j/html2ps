<?php

// We don't have _real_ interfaces in PHP 4, do we?
// So, let's make an "abstract" class instead

class ICSSLexer {
  function next_token() { die('ICSSLexer::next_token - implement me!'); }
  function get_line() { die('ICSSLexer::get_line - implement me!'); }
  function get_token_context_before() { die('ICSSLexer::get_token_context_before - implement me!'); }
  function get_token_context_after() { die('ICSSLexer::get_token_context_after - implement me!'); }
}

?>