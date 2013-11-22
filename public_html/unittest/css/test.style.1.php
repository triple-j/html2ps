<?php

require_once(HTML2PS_DIR.'css/stream.string.php');

class TestCSSStyle1 extends PHPUnit_Framework_TestCase {
  function test() {
    $pipeline = new Pipeline();
    $pipeline->configure(array('renderimages' => true));

    $style = 'background:url(/intl/en_com/images/logo_plain.png) no-repeat;height:110px;width:276px';

    $css_processor =& new CSSProcessor(); 
    $css_processor->set_pipeline($pipeline);
    $property_collection = $css_processor->import_source_ruleset($style, 
                                                                 $pipeline->get_base_url());

    $this->assertEquals(count($property_collection->_properties), 3);
  }
}

?>