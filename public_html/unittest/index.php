<?php

error_reporting(E_ALL);
ini_set("display_errors","1");
@set_time_limit(10000);

require_once('PHPUnit/Framework.php');

require_once('../config.inc.php');
require_once(HTML2PS_DIR.'pipeline.factory.class.php');

require_once('fetcher.memory.php');

$test = &new GroupTest('All tests');
// $testfiles = array_merge(glob('test.*.php'), 
//                          glob('css/test.*.php'));
$testfiles = glob('css/test.*.php');
// $testfiles = array('css/test.parser.error.5.php');

foreach ($testfiles as $testfile) {
  $test->addTestFile($testfile);
};

$test->run(new HtmlReporter());

?>