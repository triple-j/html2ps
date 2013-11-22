<?php

require_once(HTML2PS_DIR.'css/stream.string.php');

class TestCSSProcess8 extends PHPUnit_Framework_TestCase {
  function test() {
    $tree = TreeBuilder::build(file_get_contents(dirname(__FILE__).'/test.process.8.html'));

    $pipeline = new Pipeline();
    $pipeline->configure(array('renderimages' => true));
    $pipeline->scan_styles($tree);

    $css = $pipeline->get_current_css();  
    $this->assertEquals(count($css->rules), 1);
    
    $rule = $css->rules[0];   
    $this->assertEquals(count($rule->body->_properties), 1);

    $property = $rule->body->_properties[0];
    $this->assertEquals($property->_code, CSS_BACKGROUND_COLOR);
    $this->assertEquals($property->_value->r, 1);
    $this->assertEquals($property->_value->g, 1);
    $this->assertEquals($property->_value->b, 1);
    $this->assertEquals($property->_value->transparent, false);
  }
}

?>