<?php

require_once(HTML2PS_DIR.'css/stream.string.php');

class TestCSSParserError8 extends PHPUnit_Framework_TestCase {
  function test() {
    $stream = new CSSStreamString(file_get_contents(dirname(__FILE__).'/test.parser.error.8.css'));
    $lexer = new CSSLexer($stream);
    $parser = new CSSParser($lexer);
    $result = $parser->parse();    

    $this->assertTrue($result); 
    
    $errors = $parser->get_errors();

    $this->assertEquals(count($errors), 2);

    $this->assertEquals($errors[0]->get_line(), 1);
    $this->assertEquals($errors[0]->get_skipped_content(), "@skrew-me {text: xxx;\n}");

    $this->assertEquals($errors[1]->get_line(), 3);
    $this->assertEquals($errors[1]->get_skipped_content(), ";");
  }
}

?>