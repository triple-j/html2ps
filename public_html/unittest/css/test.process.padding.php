<?php

require_once(HTML2PS_DIR.'css/stream.string.php');

class TestCSSProcessPadding extends PHPUnit_Framework_TestCase {
  function test() {
    $tree = TreeBuilder::build(file_get_contents(dirname(__FILE__).'/test.process.padding.html'));

    $pipeline = new Pipeline();
    $pipeline->configure(array('renderimages' => true));

    $pipeline->scan_styles($tree);

    $css = $pipeline->get_current_css();  
    $this->assertEquals(count($css->rules), 1);
    
    $rule = $css->rules[0];   
    $padding = $rule->body->_properties[1]->_value;

    $this->assertEquals(UNIT_PX, $padding->top->_units->_unit);
    $this->assertEquals(0, $padding->top->_units->_number);

    $this->assertEquals(UNIT_PX, $padding->right->_units->_unit);
    $this->assertEquals(175, $padding->right->_units->_number);

    $this->assertEquals(UNIT_PX, $padding->bottom->_units->_unit);
    $this->assertEquals(0, $padding->bottom->_units->_number);

    $this->assertEquals(UNIT_PX, $padding->left->_units->_unit);
    $this->assertEquals(110, $padding->left->_units->_number);
  }
}

?>