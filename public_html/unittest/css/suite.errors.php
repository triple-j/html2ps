<?php

if (!defined('PHPUnit_MAIN_METHOD')) {
  define('PHPUnit_MAIN_METHOD', 'HTML2PS_CSSParseErrorTests::main');
}

require_once('../../config.inc.php');
 
require_once('PHPUnit/Framework.php');
require_once('PHPUnit/TextUI/TestRunner.php');

require_once('test.parser.error.1.php');
require_once('test.parser.error.2.php');
require_once('test.parser.error.3.php');
require_once('test.parser.error.4.php');
require_once('test.parser.error.5.php');
require_once('test.parser.error.6.php');
require_once('test.parser.error.7.php');
require_once('test.parser.error.8.php');
 
class HTML2PS_CSSParseErrorTests {
  public static function main() {
    PHPUnit_TextUI_TestRunner::run(self::suite());
  }
  
  public static function suite() {
    $suite = new PHPUnit_Framework_TestSuite('HTML2PS CSS parsing test suite ("dirty" tests)');
    
    $suite->addTestSuite('TestCSSParserError1');
    $suite->addTestSuite('TestCSSParserError2');
    $suite->addTestSuite('TestCSSParserError3');
    $suite->addTestSuite('TestCSSParserError4');
    $suite->addTestSuite('TestCSSParserError5');
    $suite->addTestSuite('TestCSSParserError6');
    $suite->addTestSuite('TestCSSParserError7');
    $suite->addTestSuite('TestCSSParserError8');

    return $suite;
  }
}
 
if (PHPUnit_MAIN_METHOD == 'HTML2PS_CSSParseErrorTests::main') {
  HTML2PS_CSSParseErrorTests::main();
}

?>