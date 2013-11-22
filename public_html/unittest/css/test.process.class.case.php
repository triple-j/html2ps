<?php

require_once(HTML2PS_DIR.'css/stream.string.php');

class TestCSSProcessClassCase extends PHPUnit_Framework_TestCase {
  function test() {
    $tree = TreeBuilder::build(file_get_contents(dirname(__FILE__).'/test.process.class.case.html'));

    $pipeline = new Pipeline();
    $pipeline->configure(array('renderimages' => true));
    $pipeline->scan_styles($tree);

    $css = $pipeline->get_current_css();  
    $this->assertEquals(count($css->rules), 1);
    
    $rule = $css->rules[0];   

    $this->assertEquals(array(SELECTOR_ID, 'quickSummary'), $rule->selector[1][0]);
  }
}

?>