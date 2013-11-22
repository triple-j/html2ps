<?php

require_once(HTML2PS_DIR.'css/stream.string.php');

class TestCSSParser extends PHPUnit_Framework_TestCase {
  function testParserStylesheetCharset() {
    $stream = new CSSStreamString('@charset "utf-8";');
    $lexer = new CSSLexer($stream);
    $parser = new CSSParser($lexer);
    $result = $parser->parse();

    $this->assertTrue($result);    
  }

  function testParserStylesheetImport() {
    $stream = new CSSStreamString('@import "sample.css";');
    $lexer = new CSSLexer($stream);
    $parser = new CSSParser($lexer);
    $result = $parser->parse();

    $this->assertTrue($result);    
  }

  function testParserStylesheetRuleset() {
    $stream = new CSSStreamString('div { color: red; }');
    $lexer = new CSSLexer($stream);
    $parser = new CSSParser($lexer);
    $result = $parser->parse();

    $this->assertTrue($result);    
  }

  function testParserStylesheetMedia() {
    $stream = new CSSStreamString('@media screen, print {}');
    $lexer = new CSSLexer($stream);
    $parser = new CSSParser($lexer);
    $result = $parser->parse();

    $this->assertTrue($result);    
  }

  function testParserStylesheetPage() {
    $stream = new CSSStreamString('@page { }');
    $lexer = new CSSLexer($stream);
    $parser = new CSSParser($lexer);
    $result = $parser->parse();

    $this->assertTrue($result);    
  }

  function testParserStylesheetEmpty() {
    $stream = new CSSStreamString('');
    $lexer = new CSSLexer($stream);
    $parser = new CSSParser($lexer);
    $result = $parser->parse();

    $this->assertTrue($result);
  }

  function testParserImportString() {
    $stream = new CSSStreamString('@import "sample.css";');
    $lexer = new CSSLexer($stream);
    $parser = new CSSParser($lexer);
    $result = $parser->parse();

    $this->assertTrue($result);    
  }

  function testParserImportUri() {
    $stream = new CSSStreamString('@import url("sample.css");');
    $lexer = new CSSLexer($stream);
    $parser = new CSSParser($lexer);
    $result = $parser->parse();

    $this->assertTrue($result);    
  }

  function testParserImportUriMedium() {
    $stream = new CSSStreamString('@import url("sample.css") screen;');
    $lexer = new CSSLexer($stream);
    $parser = new CSSParser($lexer);
    $result = $parser->parse();

    $this->assertTrue($result);    
  }

  function testParserImportUriMediumSeq() {
    $stream = new CSSStreamString('@import url("sample.css") screen, print;');
    $lexer = new CSSLexer($stream);
    $parser = new CSSParser($lexer);
    $result = $parser->parse();

    $this->assertTrue($result);    
  }
}

?>