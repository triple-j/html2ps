<?php

if (!defined('PHPUnit_MAIN_METHOD')) {
  define('PHPUnit_MAIN_METHOD', 'HTML2PS_CSSTests::main');
}

require_once('../../config.inc.php');
 
require_once('PHPUnit/Framework.php');
require_once('PHPUnit/TextUI/TestRunner.php');

require_once('suite.default.php');
require_once('suite.errors.php');
require_once('suite.parser.php');
require_once('suite.lexer.php');
require_once('suite.process.php');
require_once('suite.style.php');
 
class HTML2PS_CSSTests {
  public static function main() {
    PHPUnit_TextUI_TestRunner::run(self::suite());
  }
  
  public static function suite() {
    $suite = new PHPUnit_Framework_TestSuite('HTML2PS CSS parsing test suite ("dirty" tests)');
    
    $suite->addTestSuite(HTML2PS_CSSDefaultTests::suite());
    $suite->addTestSuite(HTML2PS_CSSParseErrorTests::suite());
    $suite->addTestSuite(HTML2PS_CSSParserTests::suite());
    $suite->addTestSuite(HTML2PS_CSSLexerTests::suite());
    $suite->addTestSuite(HTML2PS_CSSProcessTests::suite());
    $suite->addTestSuite(HTML2PS_CSSStyleTests::suite());

    return $suite;
  }
}
 
if (PHPUnit_MAIN_METHOD == 'HTML2PS_CSSTests::main') {
  HTML2PS_CSSTests::main();
}

?>