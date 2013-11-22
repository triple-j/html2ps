<?php

require_once(HTML2PS_DIR.'css/stream.string.php');

class TestCSSProcess4 extends PHPUnit_Framework_TestCase {
  function test() {
    $tree = TreeBuilder::build(file_get_contents(dirname(__FILE__).'/test.process.4.html'));

    $pipeline = new Pipeline();
    $pipeline->scan_styles($tree);

    $css = $pipeline->get_current_css();  
    $this->assertEquals(count($css->rules), 1);
    
    $rule = $css->rules[0];
    $selector = $rule->selector;

    $this->assertEquals($selector[1][0][0], SELECTOR_ATTR_VALUE_WORD_HYPHEN);
    $this->assertEquals($selector[1][0][1], 'class');
    $this->assertEquals($selector[1][0][2], 'test-en');
  }
}

?>