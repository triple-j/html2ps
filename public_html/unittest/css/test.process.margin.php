<?php

require_once(HTML2PS_DIR.'css/stream.string.php');

class TestCSSProcessMargin extends PHPUnit_Framework_TestCase {
  function test() {
    $tree = TreeBuilder::build(file_get_contents(dirname(__FILE__).'/test.process.margin.html'));

    $pipeline = new Pipeline();
    $pipeline->configure(array('renderimages' => true));

    $pipeline->scan_styles($tree);

    $css = $pipeline->get_current_css();  
    $this->assertEquals(count($css->rules), 1);
    
    $rule = $css->rules[0];   
    $margin = $rule->body->_properties[1]->_value;

    $this->assertEquals(UNIT_PX, $margin->top->_units->_unit);
    $this->assertEquals(0, $margin->top->_units->_number);

    $this->assertEquals(UNIT_PX, $margin->right->_units->_unit);
    $this->assertEquals(175, $margin->right->_units->_number);

    $this->assertEquals(UNIT_PX, $margin->bottom->_units->_unit);
    $this->assertEquals(0, $margin->bottom->_units->_number);

    $this->assertEquals(UNIT_PX, $margin->left->_units->_unit);
    $this->assertEquals(110, $margin->left->_units->_number);
  }
}

?>