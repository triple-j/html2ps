<?php

if (!defined('PHPUnit_MAIN_METHOD')) {
  define('PHPUnit_MAIN_METHOD', 'HTML2PS_CSSStyleTests::main');
}

require_once('../../config.inc.php');
 
require_once('PHPUnit/Framework.php');
require_once('PHPUnit/TextUI/TestRunner.php');

require_once('test.style.1.php');
 
class HTML2PS_CSSStyleTests {
  public static function main() {
    PHPUnit_TextUI_TestRunner::run(self::suite());
  }
  
  public static function suite() {
    $suite = new PHPUnit_Framework_TestSuite('HTML2PS CSS parsing test suite ("clean" tests)');
    
    $suite->addTestSuite('TestCSSStyle1');

    return $suite;
  }
}
 
if (PHPUnit_MAIN_METHOD == 'HTML2PS_CSSStyleTests::main') {
  HTML2PS_CSSStyleTests::main();
}

?>