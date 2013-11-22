<?php

require_once(HTML2PS_DIR.'css/stream.string.php');

class TestCSSProcessImport extends PHPUnit_Framework_TestCase {
  function test() {
    $tree = TreeBuilder::build(file_get_contents(dirname(__FILE__).'/test.process.import.html'));

    $mock_fetcher = $this->getMock('Fetcher',
                                   array('get_data'),
                                   array(),
                                   '',
                                   false);

    $mock_fetcher->expects($this->at(0))
      ->method('get_data')
      ->with('http://www.test.com/subdir/test1.css')
      ->will($this->returnValue(null));

    $mock_fetcher->expects($this->at(1))
      ->method('get_data')
      ->with('http://www.test.com/subdir/test2/test2.css')
      ->will($this->returnValue(null));

    $mock_fetcher->expects($this->at(2))
      ->method('get_data')
      ->with('http://www.test.com/test3/test3.css')
      ->will($this->returnValue(null));

    $pipeline = new Pipeline();
    $pipeline->configure(array('renderimages' => true));
    $pipeline->push_base_url('http://www.test.com/subdir/test.html');
    $pipeline->fetchers[] = $mock_fetcher;

    $pipeline->scan_styles($tree);
  }
}

?>