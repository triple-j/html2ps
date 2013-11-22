<?php

require_once(HTML2PS_DIR.'css/stream.string.php');

class TestCSSParser12 extends PHPUnit_Framework_TestCase {
  function test() {
    $stream = new CSSStreamString(file_get_contents(dirname(__FILE__).'/test.parser.12.css'));
    $lexer = new CSSLexer($stream);
    $parser = new CSSParser($lexer);
    $result = $parser->parse();    

    $this->assertTrue($result); 

    $syntax_stylesheet = $parser->get_context();
    $rulesets = $syntax_stylesheet->get_rulesets()->get();
    $this->assertEquals(count($rulesets), 2);
    $this->assertEquals($rulesets[0]->get_selectors()->get(0)->get_selector(0)->get_element(),
                       'ol');
    $this->assertEquals($rulesets[1]->get_selectors()->get(0)->get_selector(0)->get_element(),
                       'ul');
  }
}

?>