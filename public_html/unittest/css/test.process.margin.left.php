<?php

require_once(HTML2PS_DIR.'css/stream.string.php');

class TestCSSProcessMarginLeft extends PHPUnit_Framework_TestCase {
  function test() {
    $tree = TreeBuilder::build(file_get_contents(dirname(__FILE__).'/test.process.margin.left.html'));

    $pipeline = new Pipeline();
    $pipeline->configure(array('renderimages' => true));

    $pipeline->scan_styles($tree);

    $css = $pipeline->get_current_css();  
    $this->assertEquals(count($css->rules), 1);
    
    $rule = $css->rules[0];   
    $margin = $rule->body->_properties[0]->_value;

    $this->assertEquals(UNIT_PX, $margin->_units->_unit);
    $this->assertEquals(600, $margin->_units->_number);
 }
}

?>