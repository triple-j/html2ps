<?php

class GenericTestLexer extends PHPUnit_Framework_TestCase {
  function get_all_tokens($lexer) {
    $tokens = array();

    do {
      $token = $lexer->next_token();
      $tokens[] = $token;
    } while (!is_null($token['code']));

    return $tokens;
  }

  function match_tokens($tokens1, $tokens2) {
    $this->assertEquals(count($tokens1), count($tokens2));

    for ($i=0, $size = count($tokens1); $i < $size; $i++) {
      $this->assertEquals($tokens1[$i]['code'], 
                         $tokens2[$i]['code']);
      $this->assertEquals($tokens1[$i]['value'], 
                         $tokens2[$i]['value']);
    }
  }
}

?>