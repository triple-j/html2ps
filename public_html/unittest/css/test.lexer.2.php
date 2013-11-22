<?php

require_once(HTML2PS_DIR.'css/stream.string.php');
require_once(HTML2PS_DIR.'unittest/css/_generic.test.lexer.php');

class TestCSSLexer2 extends GenericTestLexer {
  function test() {
    $stream = new CSSStreamString('background-color: rgb(255, 255, 255)');
    $lexer = new CSSLexer($stream);

    $tokens = $this->get_all_tokens($lexer);

    $this->match_tokens($tokens,
                        array(array('code' => CSS_TOKEN_IDENT,
                                    'value' => 'background-color'),
                              array('code' => CSS_TOKEN_DELIM,
                                    'value' => ':'),
                              array('code' => CSS_TOKEN_SPACE,
                                    'value' => ' '),
                              array('code' => CSS_TOKEN_FUNCTION,
                                    'value' => 'rgb('),
                              array('code' => CSS_TOKEN_NUMBER,
                                    'value' => '255'),
                              array('code' => CSS_TOKEN_DELIM,
                                    'value' => ','),
                              array('code' => CSS_TOKEN_SPACE,
                                    'value' => ' '),
                              array('code' => CSS_TOKEN_NUMBER,
                                    'value' => '255'),
                              array('code' => CSS_TOKEN_DELIM,
                                    'value' => ','),
                              array('code' => CSS_TOKEN_SPACE,
                                    'value' => ' '),
                              array('code' => CSS_TOKEN_NUMBER,
                                    'value' => '255'),
                              array('code' => CSS_TOKEN_RPAREN,
                                    'value' => ')'),
                              array('code' => null,
                                    'value' => '')));
  }
}

?>