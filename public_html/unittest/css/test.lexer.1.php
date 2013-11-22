<?php

require_once(HTML2PS_DIR.'css/stream.string.php');
require_once(HTML2PS_DIR.'unittest/css/_generic.test.lexer.php');

class TestCSSLexer1 extends GenericTestLexer {
  function test() {
    $stream = new CSSStreamString('url(/images/logo_plain.png)');
    $lexer = new CSSLexer($stream);

    $tokens = $this->get_all_tokens($lexer);

    $this->match_tokens($tokens,
                        array(array('code' => CSS_TOKEN_URI,
                                    'value' => 'url(/images/logo_plain.png)'),
                              array('code' => null,
                                    'value' => '')));
  }
}

?>