<?php

require_once(HTML2PS_DIR.'css/stream.string.php');

class TestCSSLexer extends PHPUnit_Framework_TestCase {
  function get_all_tokens($lexer) {
    $tokens = array();

    do {
      $token = $lexer->next_token();
      $tokens[] = $token;
    } while (!is_null($token['code']));

    return $tokens;
  }

  function match_tokens($tokens1, $tokens2) {
    $this->assertEquals(count($tokens1), count($tokens2));

    for ($i=0, $size = count($tokens1); $i < $size; $i++) {
      $this->assertEquals($tokens1[$i]['code'], 
                         $tokens2[$i]['code']);
      $this->assertEquals($tokens1[$i]['value'], 
                         $tokens2[$i]['value']);
    }
  }

  function testLexerCleanIdentSimple() {
    $stream = new CSSStreamString('sample-identificator');
    $lexer = new CSSLexer($stream);

    // IDENT token
    $token = $lexer->next_token();
    $this->assertEquals($token['code'], CSS_TOKEN_IDENT);
    $this->assertEquals($token['value'], 'sample-identificator');

    // No next token
    $token = $lexer->next_token();
    $this->assertNull($token['code']);
  }

  function testLexerCleanIdentU() {
    $stream = new CSSStreamString('u');
    $lexer = new CSSLexer($stream);

    // IDENT token
    $token = $lexer->next_token();
    $this->assertEquals($token['code'], CSS_TOKEN_IDENT);
    $this->assertEquals($token['value'], 'u');

    // No next token
    $token = $lexer->next_token();
    $this->assertNull($token['code']);
  }

  function testLexerCleanIdentUr() {
    $stream = new CSSStreamString('ur');
    $lexer = new CSSLexer($stream);

    // IDENT token
    $token = $lexer->next_token();
    $this->assertEquals($token['code'], CSS_TOKEN_IDENT);
    $this->assertEquals($token['value'], 'ur');

    // No next token
    $token = $lexer->next_token();
    $this->assertNull($token['code']);
  }

  function testLexerCleanIdentUrl() {
    $stream = new CSSStreamString('url');
    $lexer = new CSSLexer($stream);

    // IDENT token
    $token = $lexer->next_token();
    $this->assertEquals($token['code'], CSS_TOKEN_IDENT);
    $this->assertEquals($token['value'], 'url');

    // No next token
    $token = $lexer->next_token();
    $this->assertNull($token['code']);
  }

  function testLexerCleanIdentEscaped() {
    $stream = new CSSStreamString('B\&W\?');
    $lexer = new CSSLexer($stream);

    // IDENT token
    $token = $lexer->next_token();
    $this->assertEquals($token['code'], CSS_TOKEN_IDENT);
    $this->assertEquals($token['value'], 'B\&W\?');

    // No next token
    $token = $lexer->next_token();
    $this->assertNull($token['code']);
  }

  function testLexerCleanIdentEscapedCode() {
    $stream = new CSSStreamString('B\26 W\3F');
    $lexer = new CSSLexer($stream);

    // IDENT token
    $token = $lexer->next_token();
    $this->assertEquals($token['code'], CSS_TOKEN_IDENT);
    $this->assertEquals($token['value'], 'B\26 W\3F');

    // No next token
    $token = $lexer->next_token();
    $this->assertNull($token['code']);
  }

  function testLexerCleanIdentMunis() {
    $stream = new CSSStreamString('-ident');
    $lexer = new CSSLexer($stream);

    // IDENT token
    $token = $lexer->next_token();
    $this->assertEquals($token['code'], CSS_TOKEN_IDENT);
    $this->assertEquals($token['value'], '-ident');

    // No next token
    $token = $lexer->next_token();
    $this->assertNull($token['code']);
  }

  function testLexerCleanIdentUndescore() {
    $stream = new CSSStreamString('_ident');
    $lexer = new CSSLexer($stream);

    // IDENT token
    $token = $lexer->next_token();
    $this->assertEquals($token['code'], CSS_TOKEN_IDENT);
    $this->assertEquals($token['value'], '_ident');

    // No next token
    $token = $lexer->next_token();
    $this->assertNull($token['code']);
  }

  function testLexerCleanIdentUndescoreOnly() {
    $stream = new CSSStreamString('_');
    $lexer = new CSSLexer($stream);

    // IDENT token
    $token = $lexer->next_token();
    $this->assertEquals($token['code'], CSS_TOKEN_IDENT);
    $this->assertEquals($token['value'], '_');

    // No next token
    $token = $lexer->next_token();
    $this->assertNull($token['code']);
  }

  function testLexerCleanIdentNonascii() {
    $stream = new CSSStreamString('идентификатор');
    $lexer = new CSSLexer($stream);

    // IDENT token
    $token = $lexer->next_token();
    $this->assertEquals($token['code'], CSS_TOKEN_IDENT);
    $this->assertEquals($token['value'], 'идентификатор');

    // No next token
    $token = $lexer->next_token();
    $this->assertNull($token['code']);
  }

  function testLexerCleanIdentEscapeUnicode() {
    $stream = new CSSStreamString('\000');
    $lexer = new CSSLexer($stream);

    // IDENT token
    $token = $lexer->next_token();
    $this->assertEquals($token['code'], CSS_TOKEN_IDENT);
    $this->assertEquals($token['value'], '\000');

    // No next token
    $token = $lexer->next_token();
    $this->assertNull($token['code']);
  }

  function testLexerCleanIdentEscapeAlpha() {
    $stream = new CSSStreamString('\z');
    $lexer = new CSSLexer($stream);

    // IDENT token
    $token = $lexer->next_token();
    $this->assertEquals($token['code'], CSS_TOKEN_IDENT);
    $this->assertEquals($token['value'], '\z');

    // No next token
    $token = $lexer->next_token();
    $this->assertNull($token['code']);
  }

  function testLexerCleanAtKeyword() {
    $stream = new CSSStreamString('@page');
    $lexer = new CSSLexer($stream);

    // IDENT token
    $token = $lexer->next_token();
    $this->assertEquals($token['code'], CSS_TOKEN_ATKEYWORD);
    $this->assertEquals($token['value'], '@page');

    // No next token
    $token = $lexer->next_token();
    $this->assertNull($token['code']);
  }

  function testLexerCleanStringDQuote() {
    $stream = new CSSStreamString('"test"');
    $lexer = new CSSLexer($stream);

    // IDENT token
    $token = $lexer->next_token();
    $this->assertEquals($token['code'], CSS_TOKEN_STRING);
    $this->assertEquals($token['value'], '"test"');

    // No next token
    $token = $lexer->next_token();
    $this->assertNull($token['code']);
  }

  function testLexerCleanStringWithEscapedNlN() {
    $stream = new CSSStreamString("\"test\\\ntest\"");
    $lexer = new CSSLexer($stream);

    $tokens = $this->get_all_tokens($lexer);

    $this->match_tokens($tokens,
                        array(array('code' => CSS_TOKEN_STRING,
                                    'value' => "\"test\\\ntest\""),
                              array('code' => null,
                                    'value' => '')));
  }

  function testLexerCleanStringWithEscapedNlRN() {
    $stream = new CSSStreamString("\"test\\\r\ntest\"");
    $lexer = new CSSLexer($stream);

    $tokens = $this->get_all_tokens($lexer);

    $this->match_tokens($tokens,
                        array(array('code' => CSS_TOKEN_STRING,
                                    'value' => "\"test\\\r\ntest\""),
                              array('code' => null,
                                    'value' => '')));
  }

  function testLexerCleanStringSQuote() {
    $stream = new CSSStreamString('\'test\'');
    $lexer = new CSSLexer($stream);

    // IDENT token
    $token = $lexer->next_token();
    $this->assertEquals($token['code'], CSS_TOKEN_STRING);
    $this->assertEquals($token['value'], '\'test\'');

    // No next token
    $token = $lexer->next_token();
    $this->assertNull($token['code']);
  }

  function testLexerCleanInvalidDQuote() {
    $stream = new CSSStreamString('"test');
    $lexer = new CSSLexer($stream);

    // IDENT token
    $token = $lexer->next_token();
    $this->assertEquals($token['code'], CSS_TOKEN_INVALID);
    $this->assertEquals($token['value'], '"test');

    // No next token
    $token = $lexer->next_token();
    $this->assertNull($token['code']);
  }

  function testLexerCleanInvalidSQuote() {
    $stream = new CSSStreamString('\'test');
    $lexer = new CSSLexer($stream);

    // IDENT token
    $token = $lexer->next_token();
    $this->assertEquals($token['code'], CSS_TOKEN_INVALID);
    $this->assertEquals($token['value'], '\'test');

    // No next token
    $token = $lexer->next_token();
    $this->assertNull($token['code']);
  }

  function testLexerCleanHash() {
    $stream = new CSSStreamString('#page');
    $lexer = new CSSLexer($stream);

    // IDENT token
    $token = $lexer->next_token();
    $this->assertEquals($token['code'], CSS_TOKEN_HASH);
    $this->assertEquals($token['value'], '#page');

    // No next token
    $token = $lexer->next_token();
    $this->assertNull($token['code']);
  }

  function testLexerCleanNumber() {
    $stream = new CSSStreamString('123');
    $lexer = new CSSLexer($stream);

    // IDENT token
    $token = $lexer->next_token();
    $this->assertEquals($token['code'], CSS_TOKEN_NUMBER);
    $this->assertEquals($token['value'], '123');

    // No next token
    $token = $lexer->next_token();
    $this->assertNull($token['code']);
  }

  function testLexerCleanNumberWithFraction() {
    $stream = new CSSStreamString('123.321');
    $lexer = new CSSLexer($stream);

    // IDENT token
    $token = $lexer->next_token();
    $this->assertEquals($token['code'], CSS_TOKEN_NUMBER);
    $this->assertEquals($token['value'], '123.321');

    // No next token
    $token = $lexer->next_token();
    $this->assertNull($token['code']);
  }

  function testLexerCleanNumberWithFractionNoInteger() {
    $stream = new CSSStreamString('.321');
    $lexer = new CSSLexer($stream);

    // IDENT token
    $token = $lexer->next_token();
    $this->assertEquals($token['code'], CSS_TOKEN_NUMBER);
    $this->assertEquals($token['value'], '.321');

    // No next token
    $token = $lexer->next_token();
    $this->assertNull($token['code']);
  }

  function testLexerCleanPercentage() {
    $stream = new CSSStreamString('11%');
    $lexer = new CSSLexer($stream);

    // IDENT token
    $token = $lexer->next_token();
    $this->assertEquals($token['code'], CSS_TOKEN_PERCENTAGE);
    $this->assertEquals($token['value'], '11%');

    // No next token
    $token = $lexer->next_token();
    $this->assertNull($token['code']);
  }

  function testLexerCleanDimension() {
    $stream = new CSSStreamString('11px');
    $lexer = new CSSLexer($stream);

    // IDENT token
    $token = $lexer->next_token();
    $this->assertEquals($token['code'], CSS_TOKEN_DIMENSION);
    $this->assertEquals($token['value'], '11px');

    // No next token
    $token = $lexer->next_token();
    $this->assertNull($token['code']);
  }

  function testLexerCleanUri() {
    $stream = new CSSStreamString('url("test.html")');
    $lexer = new CSSLexer($stream);

    // IDENT token
    $token = $lexer->next_token();
    $this->assertEquals($token['code'], CSS_TOKEN_URI);
    $this->assertEquals($token['value'], 'url("test.html")');

    // No next token
    $token = $lexer->next_token();
    $this->assertNull($token['code']);
  }

  function testLexerCleanUnicodeRange() {
    $stream = new CSSStreamString('U+AAAA-BBBB');
    $lexer = new CSSLexer($stream);

    // IDENT token
    $token = $lexer->next_token();
    $this->assertEquals($token['code'], CSS_TOKEN_UNICODE_RANGE);
    $this->assertEquals($token['value'], 'U+AAAA-BBBB');

    // No next token
    $token = $lexer->next_token();
    $this->assertNull($token['code']);
  }

  function testLexerCleanCDO() {
    $stream = new CSSStreamString('<!--');
    $lexer = new CSSLexer($stream);

    // IDENT token
    $token = $lexer->next_token();
    $this->assertEquals($token['code'], CSS_TOKEN_CDO);
    $this->assertEquals($token['value'], '<!--');

    // No next token
    $token = $lexer->next_token();
    $this->assertNull($token['code']);
  }

  function testLexerCleanCDC() {
    $stream = new CSSStreamString('-->');
    $lexer = new CSSLexer($stream);

    // IDENT token
    $token = $lexer->next_token();
    $this->assertEquals($token['code'], CSS_TOKEN_CDC);
    $this->assertEquals($token['value'], '-->');

    // No next token
    $token = $lexer->next_token();
    $this->assertNull($token['code']);
  }

  function testLexerCleanSemicolon() {
    $stream = new CSSStreamString(';');
    $lexer = new CSSLexer($stream);

    // IDENT token
    $token = $lexer->next_token();
    $this->assertEquals($token['code'], CSS_TOKEN_SEMICOLON);
    $this->assertEquals($token['value'], ';');

    // No next token
    $token = $lexer->next_token();
    $this->assertNull($token['code']);
  }

  function testLexerCleanLBrace() {
    $stream = new CSSStreamString('{');
    $lexer = new CSSLexer($stream);

    // IDENT token
    $token = $lexer->next_token();
    $this->assertEquals($token['code'], CSS_TOKEN_LBRACE);
    $this->assertEquals($token['value'], '{');

    // No next token
    $token = $lexer->next_token();
    $this->assertNull($token['code']);
  }

  function testLexerCleanRBrace() {
    $stream = new CSSStreamString('}');
    $lexer = new CSSLexer($stream);

    // IDENT token
    $token = $lexer->next_token();
    $this->assertEquals($token['code'], CSS_TOKEN_RBRACE);
    $this->assertEquals($token['value'], '}');

    // No next token
    $token = $lexer->next_token();
    $this->assertNull($token['code']);
  }

  function testLexerCleanLParen() {
    $stream = new CSSStreamString('(');
    $lexer = new CSSLexer($stream);

    // IDENT token
    $token = $lexer->next_token();
    $this->assertEquals($token['code'], CSS_TOKEN_LPAREN);
    $this->assertEquals($token['value'], '(');

    // No next token
    $token = $lexer->next_token();
    $this->assertNull($token['code']);
  }

  function testLexerCleanRParen() {
    $stream = new CSSStreamString(')');
    $lexer = new CSSLexer($stream);

    // IDENT token
    $token = $lexer->next_token();
    $this->assertEquals($token['code'], CSS_TOKEN_RPAREN);
    $this->assertEquals($token['value'], ')');

    // No next token
    $token = $lexer->next_token();
    $this->assertNull($token['code']);
  }

  function testLexerCleanLBrack() {
    $stream = new CSSStreamString('[');
    $lexer = new CSSLexer($stream);

    // IDENT token
    $token = $lexer->next_token();
    $this->assertEquals($token['code'], CSS_TOKEN_LBRACK);
    $this->assertEquals($token['value'], '[');

    // No next token
    $token = $lexer->next_token();
    $this->assertNull($token['code']);
  }

  function testLexerCleanRBrack() {
    $stream = new CSSStreamString(']');
    $lexer = new CSSLexer($stream);

    // IDENT token
    $token = $lexer->next_token();
    $this->assertEquals($token['code'], CSS_TOKEN_RBRACK);
    $this->assertEquals($token['value'], ']');

    // No next token
    $token = $lexer->next_token();
    $this->assertNull($token['code']);
  }

  function testLexerCleanSpace() {
    $stream = new CSSStreamString('    ');
    $lexer = new CSSLexer($stream);

    // IDENT token
    $token = $lexer->next_token();
    $this->assertEquals($token['code'], CSS_TOKEN_SPACE);
    $this->assertEquals($token['value'], '    ');

    // No next token
    $token = $lexer->next_token();
    $this->assertNull($token['code']);
  }

  function testLexerCleanComment() {
    $stream = new CSSStreamString('/* test */');
    $lexer = new CSSLexer($stream);

    // IDENT token
    $token = $lexer->next_token();
    $this->assertEquals($token['code'], CSS_TOKEN_COMMENT);
    $this->assertEquals($token['value'], '/* test */');

    // No next token
    $token = $lexer->next_token();
    $this->assertNull($token['code']);
  }

  function testLexerCleanFunction() {
    $stream = new CSSStreamString('counter(');
    $lexer = new CSSLexer($stream);

    // IDENT token
    $token = $lexer->next_token();
    $this->assertEquals($token['code'], CSS_TOKEN_FUNCTION);
    $this->assertEquals($token['value'], 'counter(');

    // No next token
    $token = $lexer->next_token();
    $this->assertNull($token['code']);
  }

  function testLexerCleanIncludes() {
    $stream = new CSSStreamString('~=');
    $lexer = new CSSLexer($stream);

    // IDENT token
    $token = $lexer->next_token();
    $this->assertEquals($token['code'], CSS_TOKEN_INCLUDES);
    $this->assertEquals($token['value'], '~=');

    // No next token
    $token = $lexer->next_token();
    $this->assertNull($token['code']);
  }

  function testLexerCleanDashmatch() {
    $stream = new CSSStreamString('|=');
    $lexer = new CSSLexer($stream);

    // IDENT token
    $token = $lexer->next_token();
    $this->assertEquals($token['code'], CSS_TOKEN_DASHMATCH);
    $this->assertEquals($token['value'], '|=');

    // No next token
    $token = $lexer->next_token();
    $this->assertNull($token['code']);
  }

  function testLexerCleanDelim() {
    $stream = new CSSStreamString('.');
    $lexer = new CSSLexer($stream);

    // IDENT token
    $token = $lexer->next_token();
    $this->assertEquals($token['code'], CSS_TOKEN_DELIM);
    $this->assertEquals($token['value'], '.');

    // No next token
    $token = $lexer->next_token();
    $this->assertNull($token['code']);
  }

  function testLexerSelectorClass() {
    $stream = new CSSStreamString('.sample-class1');
    $lexer = new CSSLexer($stream);

    $tokens = $this->get_all_tokens($lexer);
    $this->match_tokens($tokens,
                        array(array('code' => CSS_TOKEN_DELIM,
                                    'value' => '.'),
                              array('code' => CSS_TOKEN_IDENT,
                                    'value' => 'sample-class1'),
                              array('code' => null,
                                    'value' => '')));
  }

  function testLexerSelectorIdClass() {
    $stream = new CSSStreamString('#page.sample-class1');
    $lexer = new CSSLexer($stream);

    $tokens = $this->get_all_tokens($lexer);
    $this->match_tokens($tokens,
                        array(array('code' => CSS_TOKEN_HASH,
                                    'value' => '#page'),
                              array('code' => CSS_TOKEN_DELIM,
                                    'value' => '.'),
                              array('code' => CSS_TOKEN_IDENT,
                                    'value' => 'sample-class1'),
                              array('code' => null,
                                    'value' => '')));
  }

  function testLexerSelectorIdClassSpace() {
    $stream = new CSSStreamString('#page .sample-class1');
    $lexer = new CSSLexer($stream);

    $tokens = $this->get_all_tokens($lexer);
    $this->match_tokens($tokens,
                        array(array('code' => CSS_TOKEN_HASH,
                                    'value' => '#page'),
                              array('code' => CSS_TOKEN_SPACE,
                                    'value' => ' '),
                              array('code' => CSS_TOKEN_DELIM,
                                    'value' => '.'),
                              array('code' => CSS_TOKEN_IDENT,
                                    'value' => 'sample-class1'),
                              array('code' => null,
                                    'value' => '')));
  }

  function testLexerSelectorIdDeclaraction() {
    $stream = new CSSStreamString('#page { color: red; }');
    $lexer = new CSSLexer($stream);

    $tokens = $this->get_all_tokens($lexer);

    $this->match_tokens($tokens,
                        array(array('code' => CSS_TOKEN_HASH,
                                    'value' => '#page'),
                              array('code' => CSS_TOKEN_SPACE,
                                    'value' => ' '),
                              array('code' => CSS_TOKEN_LBRACE,
                                    'value' => '{'),
                              array('code' => CSS_TOKEN_SPACE,
                                    'value' => ' '),
                              array('code' => CSS_TOKEN_IDENT,
                                    'value' => 'color'),
                              array('code' => CSS_TOKEN_DELIM,
                                    'value' => ':'),
                              array('code' => CSS_TOKEN_SPACE,
                                    'value' => ' '),
                              array('code' => CSS_TOKEN_IDENT,
                                    'value' => 'red'),
                              array('code' => CSS_TOKEN_SEMICOLON,
                                    'value' => ';'),
                              array('code' => CSS_TOKEN_SPACE,
                                    'value' => ' '),
                              array('code' => CSS_TOKEN_RBRACE,
                                    'value' => '}'),
                              array('code' => null,
                                    'value' => '')));
  }

  function testLexerSelectorIdDeclaractionWithComments() {
    $stream = new CSSStreamString('#page /* ID */ { color: red; /* text color */}');
    $lexer = new CSSLexer($stream);

    $tokens = $this->get_all_tokens($lexer);

    $this->match_tokens($tokens,
                        array(array('code' => CSS_TOKEN_HASH,
                                    'value' => '#page'),
                              array('code' => CSS_TOKEN_SPACE,
                                    'value' => ' '),
                              array('code' => CSS_TOKEN_COMMENT,
                                    'value' => '/* ID */'),
                              array('code' => CSS_TOKEN_SPACE,
                                    'value' => ' '),
                              array('code' => CSS_TOKEN_LBRACE,
                                    'value' => '{'),
                              array('code' => CSS_TOKEN_SPACE,
                                    'value' => ' '),
                              array('code' => CSS_TOKEN_IDENT,
                                    'value' => 'color'),
                              array('code' => CSS_TOKEN_DELIM,
                                    'value' => ':'),
                              array('code' => CSS_TOKEN_SPACE,
                                    'value' => ' '),
                              array('code' => CSS_TOKEN_IDENT,
                                    'value' => 'red'),
                              array('code' => CSS_TOKEN_SEMICOLON,
                                    'value' => ';'),
                              array('code' => CSS_TOKEN_SPACE,
                                    'value' => ' '),
                              array('code' => CSS_TOKEN_COMMENT,
                                    'value' => '/* text color */'),
                              array('code' => CSS_TOKEN_RBRACE,
                                    'value' => '}'),
                              array('code' => null,
                                    'value' => '')));
  }

  function testLexerSelectorIdDeclaractionWithStringAndSemicolon() {
    $stream = new CSSStreamString('#page { -html2ps-html-content: "test;test"; }');
    $lexer = new CSSLexer($stream);

    $tokens = $this->get_all_tokens($lexer);

    $this->match_tokens($tokens,
                        array(array('code' => CSS_TOKEN_HASH,
                                    'value' => '#page'),
                              array('code' => CSS_TOKEN_SPACE,
                                    'value' => ' '),
                              array('code' => CSS_TOKEN_LBRACE,
                                    'value' => '{'),
                              array('code' => CSS_TOKEN_SPACE,
                                    'value' => ' '),
                              array('code' => CSS_TOKEN_IDENT,
                                    'value' => '-html2ps-html-content'),
                              array('code' => CSS_TOKEN_DELIM,
                                    'value' => ':'),
                              array('code' => CSS_TOKEN_SPACE,
                                    'value' => ' '),
                              array('code' => CSS_TOKEN_STRING,
                                    'value' => '"test;test"'),
                              array('code' => CSS_TOKEN_SEMICOLON,
                                    'value' => ';'),
                              array('code' => CSS_TOKEN_SPACE,
                                    'value' => ' '),
                              array('code' => CSS_TOKEN_RBRACE,
                                    'value' => '}'),
                              array('code' => null,
                                    'value' => '')));
  }

  function testLexerSelectorIdDeclaractionWithStringAndEscapedQuote() {
    $stream = new CSSStreamString('#page { -html2ps-html-content: "test\"test"; }');
    $lexer = new CSSLexer($stream);

    $tokens = $this->get_all_tokens($lexer);

    $this->match_tokens($tokens,
                        array(array('code' => CSS_TOKEN_HASH,
                                    'value' => '#page'),
                              array('code' => CSS_TOKEN_SPACE,
                                    'value' => ' '),
                              array('code' => CSS_TOKEN_LBRACE,
                                    'value' => '{'),
                              array('code' => CSS_TOKEN_SPACE,
                                    'value' => ' '),
                              array('code' => CSS_TOKEN_IDENT,
                                    'value' => '-html2ps-html-content'),
                              array('code' => CSS_TOKEN_DELIM,
                                    'value' => ':'),
                              array('code' => CSS_TOKEN_SPACE,
                                    'value' => ' '),
                              array('code' => CSS_TOKEN_STRING,
                                    'value' => '"test\"test"'),
                              array('code' => CSS_TOKEN_SEMICOLON,
                                    'value' => ';'),
                              array('code' => CSS_TOKEN_SPACE,
                                    'value' => ' '),
                              array('code' => CSS_TOKEN_RBRACE,
                                    'value' => '}'),
                              array('code' => null,
                                    'value' => '')));
  }

  function testLexerSelectorIdDeclaractionWithStringAndEscapedSQuote() {
    $stream = new CSSStreamString('#page { -html2ps-html-content: \'test\\\'test\'; }');
    $lexer = new CSSLexer($stream);

    $tokens = $this->get_all_tokens($lexer);

    $this->match_tokens($tokens,
                        array(array('code' => CSS_TOKEN_HASH,
                                    'value' => '#page'),
                              array('code' => CSS_TOKEN_SPACE,
                                    'value' => ' '),
                              array('code' => CSS_TOKEN_LBRACE,
                                    'value' => '{'),
                              array('code' => CSS_TOKEN_SPACE,
                                    'value' => ' '),
                              array('code' => CSS_TOKEN_IDENT,
                                    'value' => '-html2ps-html-content'),
                              array('code' => CSS_TOKEN_DELIM,
                                    'value' => ':'),
                              array('code' => CSS_TOKEN_SPACE,
                                    'value' => ' '),
                              array('code' => CSS_TOKEN_STRING,
                                    'value' => '\'test\\\'test\''),
                              array('code' => CSS_TOKEN_SEMICOLON,
                                    'value' => ';'),
                              array('code' => CSS_TOKEN_SPACE,
                                    'value' => ' '),
                              array('code' => CSS_TOKEN_RBRACE,
                                    'value' => '}'),
                              array('code' => null,
                                    'value' => '')));
  }

  function testLexerSelectorIdDeclaractionImportant() {
    $stream = new CSSStreamString('#page { color: red !important }');
    $lexer = new CSSLexer($stream);

    $tokens = $this->get_all_tokens($lexer);

    $this->match_tokens($tokens,
                        array(array('code' => CSS_TOKEN_HASH,
                                    'value' => '#page'),
                              array('code' => CSS_TOKEN_SPACE,
                                    'value' => ' '),
                              array('code' => CSS_TOKEN_LBRACE,
                                    'value' => '{'),
                              array('code' => CSS_TOKEN_SPACE,
                                    'value' => ' '),
                              array('code' => CSS_TOKEN_IDENT,
                                    'value' => 'color'),
                              array('code' => CSS_TOKEN_DELIM,
                                    'value' => ':'),
                              array('code' => CSS_TOKEN_SPACE,
                                    'value' => ' '),
                              array('code' => CSS_TOKEN_IDENT,
                                    'value' => 'red'),
                              array('code' => CSS_TOKEN_SPACE,
                                    'value' => ' '),
                              array('code' => CSS_TOKEN_DELIM,
                                    'value' => '!'),
                              array('code' => CSS_TOKEN_IDENT,
                                    'value' => 'important'),
                              array('code' => CSS_TOKEN_SPACE,
                                    'value' => ' '),
                              array('code' => CSS_TOKEN_RBRACE,
                                    'value' => '}'),
                              array('code' => null,
                                    'value' => '')));
  }
}

?>