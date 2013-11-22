<?php

require_once(HTML2PS_DIR.'css/stream.string.php');
require_once(HTML2PS_DIR.'pipeline.class.php');

class TestCSSDefault1 extends PHPUnit_Framework_TestCase {
  function test() {
    $tree = TreeBuilder::build(file_get_contents(dirname(__FILE__).'/test.default.header.html') .
                               file_get_contents(HTML2PS_DIR.'default.css') .
                               file_get_contents(dirname(__FILE__).'/test.default.footer.html'));

    $pipeline = new Pipeline();
    $pipeline->scan_styles($tree);

    $css = $pipeline->get_current_css();  

    $input_rules = $css->tag_filtered['input'];

    $this->assertEquals(count($input_rules), 10);
  }
}

?>