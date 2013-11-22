<?php

if (!defined('PHPUnit_MAIN_METHOD')) {
  define('PHPUnit_MAIN_METHOD', 'HTML2PS_CSSLexerTests::main');
}

require_once('../../config.inc.php');
 
require_once('PHPUnit/Framework.php');
require_once('PHPUnit/TextUI/TestRunner.php');

require_once('test.lexer.php');
require_once('test.lexer.1.php');
require_once('test.lexer.2.php');
require_once('test.lexer.comment.long.php');
 
class HTML2PS_CSSLexerTests {
  public static function main() {
    PHPUnit_TextUI_TestRunner::run(self::suite());
  }
  
  public static function suite() {
    $suite = new PHPUnit_Framework_TestSuite('HTML2PS CSS tokenization test suite');
    
    $suite->addTestSuite('TestCSSLexer');
    $suite->addTestSuite('TestCSSLexer1');
    $suite->addTestSuite('TestCSSLexer2');
    $suite->addTestSuite('TestCSSLexerCommentLong');

    return $suite;
  }
}
 
if (PHPUnit_MAIN_METHOD == 'HTML2PS_CSSLexerTests::main') {
  HTML2PS_CSSLexerTests::main();
}

?>