<?php

require_once(HTML2PS_DIR.'css/stream.string.php');
require_once(HTML2PS_DIR.'unittest/css/_generic.test.lexer.php');

class TestCSSLexerCommentLong extends GenericTestLexer {
  function test() {
    $contents = file_get_contents('test.lexer.comment.long.css');
    $stream = new CSSStreamString($contents);
    $lexer = new CSSLexer($stream);

    $tokens = $this->get_all_tokens($lexer);

    $this->match_tokens($tokens,
                        array(array('code' => CSS_TOKEN_COMMENT,
                                    'value' => $contents),
                              array('code' => null,
                                    'value' => '')));
  }
}

?>