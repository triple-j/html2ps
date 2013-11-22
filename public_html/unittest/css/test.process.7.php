<?php

require_once(HTML2PS_DIR.'css/stream.string.php');

class TestCSSProcess7 extends PHPUnit_Framework_TestCase {
  function test() {
    $tree = TreeBuilder::build(file_get_contents(dirname(__FILE__).'/test.process.7.html'));

    $pipeline = new Pipeline();
    $pipeline->configure(array('renderimages' => true));
    $pipeline->scan_styles($tree);

    $css = $pipeline->get_current_css();  
    $this->assertEquals(count($css->rules), 1);
    
    $rule = $css->rules[0];   
    $this->assertEquals(count($rule->body->_properties), 1);

    $property = $rule->body->_properties[0];
    $this->assertEquals($property->_code, CSS_BACKGROUND);
    $this->assertEquals($property->_value->_image->_url, 'http://localhost/intl/en_com/images/logo_plain.png');
  }
}

?>