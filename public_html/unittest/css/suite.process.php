<?php

if (!defined('PHPUnit_MAIN_METHOD')) {
  define('PHPUnit_MAIN_METHOD', 'HTML2PS_CSSProcessTests::main');
}

require_once('../../config.inc.php');
 
require_once('PHPUnit/Framework.php');
require_once('PHPUnit/TextUI/TestRunner.php');

require_once('test.process.1.php');
require_once('test.process.2.php');
require_once('test.process.3.php');
require_once('test.process.4.php');
require_once('test.process.5.php');
require_once('test.process.6.php');
require_once('test.process.7.php');
require_once('test.process.8.php');
require_once('test.process.import.php');
require_once('test.process.class.case.php');
require_once('test.process.padding.php');
require_once('test.process.margin.php');
require_once('test.process.margin.left.php');
 
class HTML2PS_CSSProcessTests {
  public static function main() {
    PHPUnit_TextUI_TestRunner::run(self::suite());
  }
  
  public static function suite() {
    $suite = new PHPUnit_Framework_TestSuite('HTML2PS CSS tokenization test suite');
    
    $suite->addTestSuite('TestCSSProcess1');
    $suite->addTestSuite('TestCSSProcess2');
    $suite->addTestSuite('TestCSSProcess3');
    $suite->addTestSuite('TestCSSProcess4');
    $suite->addTestSuite('TestCSSProcess5');
    $suite->addTestSuite('TestCSSProcess6');
    $suite->addTestSuite('TestCSSProcess7');
    $suite->addTestSuite('TestCSSProcess8');
    $suite->addTestSuite('TestCSSProcessImport');
    $suite->addTestSuite('TestCSSProcessClassCase');
    $suite->addTestSuite('TestCSSProcessPadding');
    $suite->addTestSuite('TestCSSProcessMargin');
    $suite->addTestSuite('TestCSSProcessMarginLeft');

    return $suite;
  }
}
 
if (PHPUnit_MAIN_METHOD == 'HTML2PS_CSSProcessTests::main') {
  HTML2PS_CSSProcessTests::main();
}

?>