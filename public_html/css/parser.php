<?php

require_once(HTML2PS_DIR.'css/interface.parser.php');

require_once(HTML2PS_DIR.'css/parser.error.php');

require_once(HTML2PS_DIR.'css/class.stylesheet.attrib.php');
require_once(HTML2PS_DIR.'css/class.stylesheet.combinator.php');
require_once(HTML2PS_DIR.'css/class.stylesheet.declaration.collection.php');
require_once(HTML2PS_DIR.'css/class.stylesheet.declaration.php');
require_once(HTML2PS_DIR.'css/class.stylesheet.expr.php');
require_once(HTML2PS_DIR.'css/class.stylesheet.import.php');
require_once(HTML2PS_DIR.'css/class.stylesheet.media.php');
require_once(HTML2PS_DIR.'css/class.stylesheet.operator.php');
require_once(HTML2PS_DIR.'css/class.stylesheet.page.php');
require_once(HTML2PS_DIR.'css/class.stylesheet.php');
require_once(HTML2PS_DIR.'css/class.stylesheet.property.php');
require_once(HTML2PS_DIR.'css/class.stylesheet.pseudo.php');
require_once(HTML2PS_DIR.'css/class.stylesheet.ruleset.collection.php');
require_once(HTML2PS_DIR.'css/class.stylesheet.ruleset.php');
require_once(HTML2PS_DIR.'css/class.stylesheet.selector.collection.php');
require_once(HTML2PS_DIR.'css/class.stylesheet.selector.php');
require_once(HTML2PS_DIR.'css/class.stylesheet.simple.selector.php');
require_once(HTML2PS_DIR.'css/class.stylesheet.term.php');
require_once(HTML2PS_DIR.'css/class.stylesheet.unary.operator.php');

// Error recovery modes
define('SKIP_TO_SEMICOLON', 1);
define('SKIP_TO_BLOCK', 2);
define('SKIP_TO_SEMICOLON_OR_BLOCK', 3);
define('SKIP_TO_BLOCK_END', 4);

class CSSParser extends ICSSParser {
  var $_lexer;
  var $_context;
  var $_current_token;
  var $_errors;

  function CSSParser(&$lexer) {
    $this->_lexer =& $lexer;
  }

  function add_error(&$error) {
    $this->_errors[] =& $error;
  }

  function get_errors() {
    return $this->_errors;
  }

  function expect($expected_tokens, $skip_mode) {
    foreach ($expected_tokens as $expected_token) {
      if ($this->_current_token['code'] === $expected_token['code'] &&
          (!isset($expected_token['value']) || 
           $this->_current_token['value'] === $expected_token['value'])) {
        return true;
      };
    };

    $line = $this->_current_token['line'];

    $skipped_content = $this->skip_by_mode($skip_mode);
    $this->error_expected($line, $expected_tokens, $skipped_content);

    $this->get_next_token();
    return false;
  }

  function skip_by_mode($skip_mode) {
    $skipped_content = $this->_current_token['value'];
    switch ($skip_mode) {
    case SKIP_TO_SEMICOLON:
      $skipped_content .= $this->skip_to(CSS_TOKEN_SEMICOLON);
      break;
    case SKIP_TO_BLOCK:
      $skipped_content .= $this->skip_to(CSS_TOKEN_LBRACE);
      $skipped_content .= $this->skip_to(CSS_TOKEN_RBRACE);
      break;
    case SKIP_TO_SEMICOLON_OR_BLOCK:
      $skipped_content .= $this->skip_to(array(CSS_TOKEN_LBRACE,
                                               CSS_TOKEN_SEMICOLON));
      if ($this->_current_token['code'] == CSS_TOKEN_LBRACE) {
        $this->get_next_token();
        $skipped_content .= $this->skip_to(array(CSS_TOKEN_RBRACE));
      };
      break;
    case SKIP_TO_BLOCK_END:
      $skipped_content .= $this->skip_to(CSS_TOKEN_RBRACE);
      break;
    default:
      die('Invalid value passed to skip_by_mode');
    };

    return $skipped_content;
  }

  function error_expected($line, $expected_tokens, $skipped_content) {
    $error =& new CSSParserError($line,
                                 $this->_lexer->get_token_context_before(),
                                 $this->_lexer->get_token_context_after(),
                                 $skipped_content);
    $this->add_error($error);
  }

  function get_next_token() {
    do {
      $this->_current_token = $this->_lexer->next_token();
    } while ($this->_current_token['code'] === CSS_TOKEN_COMMENT);

    return $this->_current_token;
  }

  function &get_context() {
    return $this->_context[0];
  }

  function parse() {
    $this->_context = array();
    $this->_errors = array();

    $this->get_next_token();
    while (!$this->rule_stylesheet()) { 
      // Empty loop; error handling done inside the rule_stylesheet1
    };

    return true;
  }

  function parse_ruleset() {
    $this->_context = array();
    $this->push_context(new CSSStylesheetRuleset());

    $this->get_next_token();
    if (!$this->rule_ruleset()) { 
      return false; 
    };

    return true;
  }

  function push_context(&$item) {
    $this->_context[] =& $item;
  }

  function &peek_context() {
    if (count($this->_context) == 0) {
      $null = null;
      return $null;
    };

    return $this->_context[count($this->_context) - 1];
  }

  function &pop_context() {
    $item =& $this->peek_context();
    array_pop($this->_context);
    return $item;
  }

  function rule_stylesheet() {
    $this->push_context(new CSSStylesheet());

    if ($this->_current_token['code'] === CSS_TOKEN_SPACE) { 
      if (!$this->rule_s_seq_empty()) {
        return false;
      };

      if (!$this->rule_stylesheet1()) {
        return false;
      };
    } else {
      if (!$this->rule_stylesheet1()) {
        return false;
      };
    };

    return true;
  }

  function rule_stylesheet1() {
    if ($this->_current_token['code'] === CSS_TOKEN_ATKEYWORD && 
        $this->_current_token['value'] === '@charset') { // <STYLESHEET> :: ATKEYWORD["@charset"] <S_SEQ_EMPTY> STRING SEMICOLON (*) <S_CDO_CDC_SEQ_EMPTY> <STYLESHEET2> 
      $this->get_next_token();
      $this->rule_s_seq_empty();

      if (!$this->expect(array(array('code' => CSS_TOKEN_STRING)),
                         SKIP_TO_SEMICOLON_OR_BLOCK)) {
        return false;
      };
      //* 
      $charset_name = $this->_current_token['value'];

      $this->get_next_token();
      $this->rule_s_seq_empty();

      if (!$this->expect(array(array('code' => CSS_TOKEN_SEMICOLON)),
                         SKIP_TO_SEMICOLON_OR_BLOCK)) {
        return false;
      };
      $this->get_next_token();

      // *
      $stylesheet =& $this->peek_context();
      $stylesheet->set_charset($charset_name);
      
      if (!$this->rule_s_cdo_cdc_seq_empty()) { return false; };
      if (!$this->rule_stylesheet2()) { return false; };

    } elseif ($this->_current_token['code'] === CSS_TOKEN_ATKEYWORD && 
              in_array($this->_current_token['value'], 
                       array('@import', '@media', '@page')) ||
              $this->_current_token['code'] === CSS_TOKEN_DELIM && 
              in_array($this->_current_token['value'], 
                       array('.', ':', '*')) || 
              in_array($this->_current_token['code'], 
                       array(CSS_TOKEN_HASH,
                             CSS_TOKEN_IDENT,
                             CSS_TOKEN_LBRACK,
                             CSS_TOKEN_SPACE,
                             null))) { // <STYLESHEET> :: <S_CDO_CDC_SEQ_EMPTY> <STYLESHEET2>
      if (!$this->rule_s_cdo_cdc_seq_empty()) { return false; };
      if (!$this->rule_stylesheet2()) { return false; };
      
    } else {
      $this->error_expected($this->_current_token['line'],
                            array(/* todo */), 
                            $this->skip_by_mode(SKIP_TO_SEMICOLON_OR_BLOCK));
      $this->get_next_token();

      return false;
    };

    return true;
  }

  function rule_stylesheet2() {
    if ($this->_current_token['code'] === CSS_TOKEN_ATKEYWORD && 
        $this->_current_token['value'] === '@import') { // <STYLESHEET2> :: <IMPORT> <S_CDO_CDC_SEQ_EMPTY> <STYLESHEET2>
      // *
      $this->push_context(new CSSStylesheetImport);

      if (!$this->rule_import()) { 
        $this->pop_context();
        return false; 
      };

      // *
      $import =& $this->pop_context();
      $stylesheet =& $this->peek_context();
      $stylesheet->add_import($import);

      if (!$this->rule_s_cdo_cdc_seq_empty()) { return false; };
      if (!$this->rule_stylesheet2()) { return false; };

    } elseif ($this->_current_token['code'] === CSS_TOKEN_ATKEYWORD && 
              in_array($this->_current_token['value'], 
                       array('@media', '@page')) ||
              $this->_current_token['code'] === CSS_TOKEN_DELIM && 
              in_array($this->_current_token['value'], 
                       array('.', ':', '*')) || 
              in_array($this->_current_token['code'], 
                       array(CSS_TOKEN_HASH,
                             CSS_TOKEN_IDENT,
                             CSS_TOKEN_LBRACK,
                             null))) { // <STYLESHEET2> :: <STYLESHEET3>
      if (!$this->rule_stylesheet3()) { return false; };
      
    } else {
      if (is_null($this->skip_to(CSS_TOKEN_LBRACE))) { 
        return false;
      };
      $this->get_next_token();

      if (is_null($this->skip_to(CSS_TOKEN_RBRACE))) {
        return false;
      };
      $this->get_next_token();

      if (!$this->rule_stylesheet2()) {
        return false;
      };
    };

    return true;
  }

  function rule_stylesheet3() {
    if ($this->_current_token['code'] === CSS_TOKEN_DELIM && 
        in_array($this->_current_token['value'], 
                 array('.', ':', '*')) ||
        in_array($this->_current_token['code'],
                 array(CSS_TOKEN_HASH,
                       CSS_TOKEN_IDENT,
                       CSS_TOKEN_LBRACK))) { // <STYLESHEET3> :: <RULESET> <S_CDO_CDC_SEQ_EMPTY> <STYLESHEET3>
      // *
      $this->push_context(new CSSStylesheetRuleset());

      if (!$this->rule_ruleset()) { 
        $this->pop_context();

        if (is_null($this->skip_to(CSS_TOKEN_RBRACE))) {
          return false; 
        };
        $this->get_next_token();
      } else {
        // *
        $ruleset =& $this->pop_context();
        $stylesheet =& $this->peek_context();
        $stylesheet->add_ruleset($ruleset);
      };

      if (!$this->rule_s_cdo_cdc_seq_empty()) { return false; };
      if (!$this->rule_stylesheet3()) { return false; };

    } elseif ($this->_current_token['code'] === CSS_TOKEN_ATKEYWORD && 
              $this->_current_token['value'] === '@media') { // <STYLESHEET3> :: <MEDIA> <S_CDO_CDC_SEQ_EMPTY> <STYLESHEET3>
      // *
      $this->push_context(new CSSSTylesheetMedia());

      if (!$this->rule_media()) { 
        $this->pop_context();
        return false; 
      };

      // *
      $media =& $this->pop_context();
      $stylesheet =& $this->peek_context();
      $stylesheet->add_media($media);
      
      if (!$this->rule_s_cdo_cdc_seq_empty()) { return false; };
      if (!$this->rule_stylesheet3()) { return false; };
      
    } elseif ($this->_current_token['code'] === CSS_TOKEN_ATKEYWORD && 
              $this->_current_token['value'] === '@page') { // <STYLESHEET3> :: <PAGE> <S_CDO_CDC_SEQ_EMPTY> <STYLESHEET3>
      // *
      $this->push_context(new CSSStylesheetPage());

      if (!$this->rule_page()) { 
        $this->pop_context();
        return false; 
      };

      // *
      $page =& $this->pop_context();
      $stylesheet =& $this->peek_context();
      $stylesheet->add_page($page);

      if (!$this->rule_s_cdo_cdc_seq_empty()) { return false; };
      if (!$this->rule_stylesheet3()) { return false; };

    } elseif ($this->_current_token['code'] === null) { // <STYLESHEET3> :: <EMPTY>

    } else {
      if (is_null($this->skip_to(CSS_TOKEN_LBRACE))) { 
        return false;
      };
      $this->get_next_token();

      if (is_null($this->skip_to(CSS_TOKEN_RBRACE))) {
        return false;
      };
      $this->get_next_token();

      if (!$this->rule_stylesheet2()) {
        return false;
      };
    };

    return true;
  }

  function rule_import() {
    if ($this->_current_token['code'] === CSS_TOKEN_ATKEYWORD && 
        $this->_current_token['value'] === '@import') { // <IMPORT> :: ATKEYWORD["@import"] <S_SEQ_EMPTY> <IMPORT2>
      $this->get_next_token();

      if (!$this->rule_s_seq_empty()) { return false; };
      if (!$this->rule_import2()) { return false; };

    } else {
      return false;
    };

    return true;
  }

  function rule_import2() {
    if ($this->_current_token['code'] === CSS_TOKEN_STRING) { // <IMPORT2> :: STRING <S_SEQ_EMPTY> <IMPORT3>
      // *
      $url = $this->_current_token['value'];
      $import =& $this->peek_context();
      $import->set_url($url);

      $this->get_next_token();

      if (!$this->rule_s_seq_empty()) { return false; };
      if (!$this->rule_import3()) { return false; };

    } elseif ($this->_current_token['code'] === CSS_TOKEN_URI) { // <IMPORT2> :: URI <S_SEQ_EMPTY> <IMPORT3>
      // *
      $url = $this->_current_token['value'];
      $import =& $this->peek_context();
      $import->set_url($url);

      $this->get_next_token();

      if (!$this->rule_s_seq_empty()) { return false; };
      if (!$this->rule_import3()) { return false; };

    } else {
      return false;
    };

    return true;
  }

  function rule_import3() {
    if ($this->_current_token['code'] === CSS_TOKEN_IDENT) { // <IMPORT3> :: <MEDIUM_SEQ> <IMPORT4>
      if (!$this->rule_medium_seq()) { return false; };
      if (!$this->rule_import4()) { return false; };

    } elseif ($this->_current_token['code'] === CSS_TOKEN_SEMICOLON) { // <IMPORT3> :: <IMPORT4>
      if (!$this->rule_import4()) { return false; };

    } else {
      return false;
    };

    return true;
  }

  function rule_import4() {
    if ($this->_current_token['code'] === CSS_TOKEN_SEMICOLON) { // <IMPORT4> :: SEMICOLON <S_SEQ_EMPTY>
      $this->get_next_token();

      if (!$this->rule_s_seq_empty()) { return false; };

    } else {
      return false;
    };

    return true;
  }

  function rule_media() {
    if ($this->_current_token['code'] === CSS_TOKEN_ATKEYWORD &&
        $this->_current_token['value'] === '@media') { // <MEDIA> :: ATKEYWORD["@media"] <S_SEQ_EMPTY> <MEDIUM_SEQ> LBRACE <S_SEQ_EMPTY> <RULESET_SEQ_EMPTY> RBRACE <S_SEQ_EMPTY>
      $this->get_next_token();

      if (!$this->rule_s_seq_empty()) { return false; };

      if (!$this->rule_medium_seq()) { 
        return false; 
      };

      if ($this->_current_token['code'] !== CSS_TOKEN_LBRACE) {
        return false;
      };
      $this->get_next_token();

      if (!$this->rule_s_seq_empty()) { 
        return false; 
      };

      // *
      $this->push_context(new CSSStylesheetRulesetCollection());
      
      if (!$this->rule_ruleset_seq_empty()) { 
        $this->pop_context();
        return false; 
      };

      // *
      $ruleset_collection =& $this->pop_context();
      $media =& $this->peek_context();
      $media->add_rulesets($ruleset_collection);

      if ($this->_current_token['code'] !== CSS_TOKEN_RBRACE) {
        return false;
      };
      $this->get_next_token();

      if (!$this->rule_s_seq_empty()) { 
        return false; 
      };

    } else {
      return false;
    };

    return true;
  }

  function rule_medium() {
    if ($this->_current_token['code'] === CSS_TOKEN_IDENT) { // <MEDIUM> :: IDENT <S_SEQ_EMPTY>
      // *
      $medium_name = $this->_current_token['value'];

      $this->get_next_token();

      if (!$this->rule_s_seq_empty()) { return false; };

      // *
      $context =& $this->peek_context();
      $context->add_medium($medium_name);

    } else {
      return false;
    };

    return true;
  }

  function rule_page() {
    if ($this->_current_token['code'] === CSS_TOKEN_ATKEYWORD &&
        $this->_current_token['value'] === '@page') { // <PAGE> :: ATKEYWORD["@page"] <S_SEQ_EMPTY> <PAGE2>
      $this->get_next_token();

      if (!$this->rule_s_seq_empty()) { 
        return false; 
      };

      if (!$this->rule_page2()) { 
        return false; 
      };

    } else {
      return false;
    };

    return true;
  }

  function rule_page2() {
    if ($this->_current_token['code'] === CSS_TOKEN_DELIM &&
        $this->_current_token['value'] === ':') { // <PAGE2> :: DELIM[":"] IDENT <S_SEQ_EMPTY> <PAGE3>
      $this->get_next_token();

      if ($this->_current_token['code'] !== CSS_TOKEN_IDENT) {
        return false;
      };

      // *
      $page_name = $this->_current_token['value'];
      $page =& $this->peek_context();
      $page->set_name($page_name);

      $this->get_next_token();
      
      if (!$this->rule_s_seq_empty()) { return false; };
      if (!$this->rule_page3()) { return false; };

    } elseif ($this->_current_token['code'] === CSS_TOKEN_LBRACE) { // <PAGE2> :: <PAGE3>
      if (!$this->rule_page3()) { return false; };

    } else {
      return false;
    };

    return true;
  }

  function rule_page3() {
    if ($this->_current_token['code'] === CSS_TOKEN_LBRACE) { // <PAGE3> :: LBRACE <S_SEQ_EMPTY> <DECLARATION_SEQ> RBRACE <S_SEQ_EMPTY>
      $this->get_next_token();

      if (!$this->rule_s_seq_empty()) { return false; };

      // *
      $this->push_context(new CSSStylesheetDeclarationCollection());

      if (!$this->rule_declaration_seq()) { 
        $this->pop_context();
        return false; 
      };
      
      if ($this->_current_token['code'] !== CSS_TOKEN_RBRACE) {
        $this->pop_context();
        return false;
      };
      $this->get_next_token();

      if (!$this->rule_s_seq_empty()) { 
        $this->pop_context();
        return false; 
      };

      // *
      $declarations =& $this->pop_context();
      $page =& $this->peek_context();
      $page->add_declarations($declarations);

    } else {
      return false;
    };

    return true;
  }

  function rule_operator() {
    if ($this->_current_token['code'] === CSS_TOKEN_DELIM &&
        $this->_current_token['value'] === '/') { // <OPERATOR> :: DELIM["/"] <S_SEQ_EMPTY>
      $this->get_next_token();

      // *
      $operator =& $this->peek_context();
      $operator->set_type(OPERATOR_SLASH);

      if (!$this->rule_s_seq_empty()) { return false; };

    } elseif ($this->_current_token['code'] === CSS_TOKEN_DELIM &&
              $this->_current_token['value'] === ',') { // <OPERATOR> :: DELIM[","] <S_SEQ_EMPTY>
      $this->get_next_token();

      // *
      $operator =& $this->peek_context();
      $operator->set_type(OPERATOR_COMMA);

      if (!$this->rule_s_seq_empty()) { return false; };

    } elseif ($this->_current_token['code'] === CSS_TOKEN_DELIM &&
              in_array($this->_current_token['value'],
                       array('-', '+')) ||
              in_array($this->_current_token['code'],
                       array(CSS_TOKEN_NUMBER,
                             CSS_TOKEN_PERCENTAGE,
                             CSS_TOKEN_DIMENSION,
                             CSS_TOKEN_STRING,
                             CSS_TOKEN_IDENT,
                             CSS_TOKEN_URI,
                             CSS_TOKEN_HASH,
                             CSS_TOKEN_FUNCTION))) { // <OPERATOR> :: <EMPTY>

      // *
      $operator =& $this->peek_context();
      $operator->set_type(OPERATOR_EMPTY);
      
    } else {
      return false;
    };

    return true;
  }

  function rule_combinator() {
    if ($this->_current_token['code'] === CSS_TOKEN_DELIM &&
        $this->_current_token['value'] === '+') { // <COMBINATOR> :: DELIM["+"] <S_SEQ_EMPTY>
      $this->get_next_token();

      // *
      $operator =& $this->peek_context();
      $operator->set_type(COMBINATOR_PLUS);

      if (!$this->rule_s_seq_empty()) { return false; };

    } elseif ($this->_current_token['code'] === CSS_TOKEN_DELIM &&
              $this->_current_token['value'] === '>') { // <COMBINATOR> :: DELIM[">"] <S_SEQ_EMPTY>
      $this->get_next_token();

      // *
      $operator =& $this->peek_context();
      $operator->set_type(COMBINATOR_GREATER);

      if (!$this->rule_s_seq_empty()) { return false; };

    } elseif ($this->_current_token['code'] === CSS_TOKEN_DELIM && 
              in_array($this->_current_token['value'],
                       array('*', '.', ':')) ||
              in_array($this->_current_token['code'],
                       array(CSS_TOKEN_HASH,
                             CSS_TOKEN_LBRACK,
                             CSS_TOKEN_IDENT))) { // <COMBINATOR> :: <EMPTY>
      // *
      $operator =& $this->peek_context();
      $operator->set_type(COMBINATOR_EMPTY);

    } else {
      return false;
    };

    return true;
  }

  function rule_unary_operator() {
    if ($this->_current_token['code'] === CSS_TOKEN_DELIM &&
        $this->_current_token['value'] === '+') { // <UNARY_OPERATOR> :: DELIM["-"]
      $this->get_next_token();

      // *
      $operator =& $this->peek_context();
      $operator->set_type(UNARY_OPERATOR_PLUS);

    } elseif ($this->_current_token['code'] === CSS_TOKEN_DELIM &&
              $this->_current_token['value'] === '-') { // <UNARY_OPERATOR> :: DELIM["+"]
      $this->get_next_token();

      // *
      $operator =& $this->peek_context();
      $operator->set_type(UNARY_OPERATOR_MINUS);

    } else {
      return false;
    };

    return true;
  }

  function rule_property() {
    if ($this->_current_token['code'] === CSS_TOKEN_IDENT) { // <PROPERTY> :: IDENT <S_SEQ_EMPTY>
      // *
      $property_name =& $this->_current_token['value'];
      $property =& $this->peek_context();
      $property->set_name($property_name);
      
      $this->get_next_token();
      if (!$this->rule_s_seq_empty()) { return false; };

    } else {
      return false;
    };

    return true;
  }

  function rule_ruleset() {
    if ($this->_current_token['code'] === CSS_TOKEN_DELIM && 
        in_array($this->_current_token['value'],
                 array('.', ':', '*')) ||
        in_array($this->_current_token['code'],
                 array(CSS_TOKEN_HASH,
                       CSS_TOKEN_IDENT,
                       CSS_TOKEN_LBRACE))) { // <RULESET> :: <SELECTOR_SEQ> LBRACE <S_SEQ_EMPTY> <DECLARATION_SEQ> RBRACE <S_SEQ_EMPTY>

      // *
      $this->push_context(new CSSStylesheetSelectorCollection());

      if (!$this->rule_selector_seq()) { 
        $this->pop_context();
        
        $this->skip_to(CSS_TOKEN_LBRACE);
        $this->get_next_token();

        return false; 
      };

      // *
      $selectors =& $this->pop_context();
      $ruleset =& $this->peek_context();
      $ruleset->add_selectors($selectors);

      if ($this->_current_token['code'] !== CSS_TOKEN_LBRACE) {
        return false;
      };
      $this->get_next_token();

      if (!$this->rule_s_seq_empty()) { return false; };

      // *
      $this->push_context(new CSSStylesheetDeclarationCollection());

      if (!$this->rule_declaration_seq()) { 
        $this->pop_context();
        return false; 
      };

      // *
      $declarations =& $this->pop_context();
      $ruleset =& $this->peek_context();
      $ruleset->add_declarations($declarations);
     
      if ($this->_current_token['code'] !== CSS_TOKEN_RBRACE) {
        return false;
      };
      $this->get_next_token();

      if (!$this->rule_s_seq_empty()) { return false; };

    } else {
      return false;
    };

    return true;
  }

  function rule_selector() {
    if ($this->_current_token['code'] === CSS_TOKEN_DELIM && 
        in_array($this->_current_token['value'],
                 array('.', ':', '*')) ||
        in_array($this->_current_token['code'],
                 array(CSS_TOKEN_HASH,
                       CSS_TOKEN_IDENT,
                       CSS_TOKEN_LBRACE))) { // <SELECTOR> :: <SIMPLE_SELECTOR> <SELECTOR2>
      // *
      $this->push_context(new CSSStylesheetSimpleSelector);

      if (!$this->rule_simple_selector()) { 
        $this->pop_context();
        return false; 
      };

      // *
      $simple_selector =& $this->pop_context();
      $selector =& $this->peek_context();
      $selector->add_selector($simple_selector);

      if (!$this->rule_selector2()) { return false; };

    } else {
      return false;
    };

    return true;
  }

  function rule_selector2() {
    if ($this->_current_token['code'] === CSS_TOKEN_DELIM && 
        in_array($this->_current_token['value'],
                 array('+', '>', '*', '.', ':')) ||
        in_array($this->_current_token['code'],
                 array(CSS_TOKEN_HASH,
                       CSS_TOKEN_LBRACK,
                       CSS_TOKEN_IDENT))) { // <SELECTOR2> :: <COMBINATOR> <SIMPLE_SELECTOR> <SELECTOR2>

      // *
      $this->push_context(new CSSStylesheetCombinator);

      if (!$this->rule_combinator()) { 
        return false; 
      };

      // *
      $combinator =& $this->pop_context();
      $selector =& $this->peek_context();
      $selector->add_combinator($combinator);

      // *
      $this->push_context(new CSSStylesheetSimpleSelector);

      if (!$this->rule_simple_selector()) { 
        $this->pop_context();
        return false; 
      };

      // *
      $simple_selector =& $this->pop_context();
      $selector =& $this->peek_context();
      $selector->add_selector($simple_selector);

      if (!$this->rule_selector2()) { return false; };

    } elseif ($this->_current_token['code'] === CSS_TOKEN_DELIM && 
        in_array($this->_current_token['value'],
                 array(',')) ||
        in_array($this->_current_token['code'],
                 array(CSS_TOKEN_LBRACE))) { // <SELECTOR2> :: <EMPTY>

    } else {
      return false;
    };

    return true;
  }

  function rule_simple_selector() {
    if ($this->_current_token['code'] === CSS_TOKEN_DELIM && 
        in_array($this->_current_token['value'],
                 array('*')) ||
        in_array($this->_current_token['code'],
                 array(CSS_TOKEN_IDENT))) { // <SIMPLE_SELECTOR> :: <ELEMENT_NAME> <SIMPLE_SELECTOR3>
      if (!$this->rule_element_name()) { return false; };
      if (!$this->rule_simple_selector3()) { return false; };

    } elseif ($this->_current_token['code'] === CSS_TOKEN_DELIM && 
        in_array($this->_current_token['value'],
                 array('.', ':')) ||
        in_array($this->_current_token['code'],
                 array(CSS_TOKEN_HASH,
                       CSS_TOKEN_LBRACK))) { // <SIMPLE_SELECTOR> :: <SIMPLE_SELECTOR_2>
      if (!$this->rule_simple_selector2()) { return false; };

    } else {
      return false;
    };

    return true;
  }

  function rule_simple_selector2() {
    if ($this->_current_token['code'] === CSS_TOKEN_HASH) { // <SIMPLE_SELECTOR2> :: HASH <SIMPLE_SELECTOR3>
      // *
      $selector =& $this->peek_context();
      $selector->add_id($this->_current_token['value']);

      $this->get_next_token();
      if (!$this->rule_simple_selector3()) { return false; };

    } elseif ($this->_current_token['code'] === CSS_TOKEN_DELIM && 
        in_array($this->_current_token['value'],
                 array('.'))) { // <SIMPLE_SELECTOR2> :: <CLASS> <SIMPLE_SELECTOR3>
      if (!$this->rule_class()) { return false; };
      if (!$this->rule_simple_selector3()) { return false; };

    } elseif ($this->_current_token['code'] === CSS_TOKEN_LBRACK) { // <SIMPLE_SELECTOR2> :: <ATTRIB> <SIMPLE_SELECTOR3>
      // *
      $this->push_context(new CSSStylesheetAttrib());

      if (!$this->rule_attrib()) { 
        $this->pop_context();
        return false; 
      };

      // *
      $attrib =& $this->pop_context();
      $selector =& $this->peek_context();
      $selector->add_attrib($attrib);

      if (!$this->rule_simple_selector3()) { return false; };

    } elseif ($this->_current_token['code'] === CSS_TOKEN_DELIM && 
        in_array($this->_current_token['value'],
                 array(':'))) { // <SIMPLE_SELECTOR2> :: <PSEUDO> <SIMPLE_SELECTOR3>
      // *
      $this->push_context(new CSSStylesheetPseudo());

      if (!$this->rule_pseudo()) { 
        $this->pop_context();
        return false; 
      };

      // *
      $pseudo =& $this->pop_context();
      $selector =& $this->peek_context();
      $selector->add_pseudo($pseudo);

      if (!$this->rule_simple_selector3()) { return false; };

    } else {
      return false;
    };    

    return true;
  }

  function rule_simple_selector3() {
    if ($this->_current_token['code'] === CSS_TOKEN_HASH) { // <SIMPLE_SELECTOR3> :: HASH <SIMPLE_SELECTOR3>
      // *
      $selector =& $this->peek_context();
      $selector->add_id($this->_current_token['value']);

      $this->get_next_token();

      if (!$this->rule_simple_selector3()) { return false; };

    } elseif ($this->_current_token['code'] === CSS_TOKEN_DELIM && 
              in_array($this->_current_token['value'],
                       array('.'))) { // <SIMPLE_SELECTOR3> :: <CLASS> <SIMPLE_SELECTOR3>
      if (!$this->rule_class()) { return false; };
      if (!$this->rule_simple_selector3()) { return false; };

    } elseif ($this->_current_token['code'] === CSS_TOKEN_LBRACK) { // <SIMPLE_SELECTOR3> :: <ATTRIB> <SIMPLE_SELECTOR3>
      // *
      $this->push_context(new CSSStylesheetAttrib());

      if (!$this->rule_attrib()) { 
        $this->pop_context();
        return false; 
      };

      // *
      $attrib =& $this->pop_context();
      $selector =& $this->peek_context();
      $selector->add_attrib($attrib);

      if (!$this->rule_simple_selector3()) { return false; };

    } elseif ($this->_current_token['code'] === CSS_TOKEN_DELIM && 
              in_array($this->_current_token['value'],
                       array(':'))) { // <SIMPLE_SELECTOR3> :: <PSEUDO> <SIMPLE_SELECTOR3>
      // *
      $this->push_context(new CSSStylesheetPseudo());

      if (!$this->rule_pseudo()) { 
        $this->pop_context();
        return false; 
      };

      // *
      $pseudo =& $this->pop_context();
      $selector =& $this->peek_context();
      $selector->add_pseudo($pseudo);

      if (!$this->rule_simple_selector3()) { return false; };

    } elseif ($this->_current_token['code'] === CSS_TOKEN_DELIM && 
              in_array($this->_current_token['value'],
                       array('+', '>', ',')) ||
              in_array($this->_current_token['code'], 
                       array(CSS_TOKEN_SPACE,
                             CSS_TOKEN_LBRACE))) { // <SIMPLE_SELECTOR3> :: <S_SEQ_EMPTY>
      $this->rule_s_seq_empty();

    } else {
      return false;
    };    

    return true;
  }

  function rule_class() {
    if ($this->_current_token['code'] === CSS_TOKEN_DELIM && 
        in_array($this->_current_token['value'],
                 array('.'))) { // <CLASS> :: DELIM["."] IDENT
      $this->get_next_token();

      if ($this->_current_token['code'] !== CSS_TOKEN_IDENT) {
        return false;
      };

      // *
      $selector =& $this->peek_context();
      $selector->add_class($this->_current_token['value']);

      $this->get_next_token();

    } else {
      return false;
    };
    
    return true;
  }

  function rule_element_name() {
    if ($this->_current_token['code'] === CSS_TOKEN_IDENT) { // <ELEMENT_NAME> :: IDENT
      // *
      $selector =& $this->peek_context();
      $selector->set_element($this->_current_token['value']);

      $this->get_next_token();

    } elseif ($this->_current_token['code'] === CSS_TOKEN_DELIM && 
              in_array($this->_current_token['value'],
                       array('*'))) { // <ELEMENT_NAME> :: DELIM["*"]
      // *
      $selector =& $this->peek_context();
      $selector->set_element('*');

      $this->get_next_token();

    } else {
      return false;
    };

    return true;
  }

  function rule_attrib() {
    if ($this->_current_token['code'] === CSS_TOKEN_LBRACK) { // <ATTRIB> :: LBRACK <S_SEQ_EMPTY> IDENT <S_SEQ_EMPTY> <ATTRIB2>
      $this->get_next_token();

      if (!$this->rule_s_seq_empty()) { return false; };
      
      if ($this->_current_token['code'] !== CSS_TOKEN_IDENT) {
        return false;
      };

      // * 
      $attrib =& $this->peek_context();
      $attrib->set_name($this->_current_token['value']);

      $this->get_next_token();

      if (!$this->rule_s_seq_empty()) { return false; };
      if (!$this->rule_attrib2()) { return false; };

    } else {
      return false;
    };

    return true;
  }

  function rule_attrib2() {
    if ($this->_current_token['code'] === CSS_TOKEN_DELIM && 
        in_array($this->_current_token['value'],
                 array('=')) ||
        in_array($this->_current_token['code'],
                 array(CSS_TOKEN_INCLUDES,
                       CSS_TOKEN_DASHMATCH))) { // <ATTRIB2> :: <ATTRIB_OP> <S_SEQ_EMPTY> <ATTRIB_VALUE> <S_SEQ_EMPTY> RBRACK 
      if (!$this->rule_attrib_op()) { return false; };
      if (!$this->rule_s_seq_empty()) { return false; };
      if (!$this->rule_attrib_value()) { return false; };
      if (!$this->rule_s_seq_empty()) { return false; };

      if ($this->_current_token['code'] !== CSS_TOKEN_RBRACK) {
        return false;
      };
      $this->get_next_token();

    } elseif ($this->_current_token['code'] == CSS_TOKEN_RBRACK) { // <ATTRIB2> :: RBRACK 
      $this->get_next_token();

    } else {
      return false;
    }
    
    return true;
  }

  function rule_pseudo() {
    if ($this->_current_token['code'] === CSS_TOKEN_DELIM && 
        in_array($this->_current_token['value'],
                 array(':'))) { // <PSEUDO> :: DELIM[":"] <PSEUDO2>
      $this->get_next_token();

      if (!$this->rule_pseudo2()) { return false; };
      
    } else {
      return false;
    }

    return true;
  }

  function rule_pseudo2() {
    if ($this->_current_token['code'] === CSS_TOKEN_IDENT) { // <PSEUDO2> :: IDENT
      // *
      $pseudo =& $this->peek_context();
      $pseudo->set_name($this->_current_token['value']);

      $this->get_next_token();
      
    } elseif ($this->_current_token['code'] === CSS_TOKEN_FUNCTION) { // <PSEUDO2> :: FUNCTION <S_SEQ_EMPTY> <PSEUDO3>
      // *
      $pseudo =& $this->peek_context();
      $pseudo->set_function_name($this->_current_token['value']);

      $this->get_next_token();

      if (!$this->rule_s_seq_empty()) { return false; };
      if (!$this->rule_pseudo3()) { return false; };

    } else {
      return false;
    }

    return true;
  }

  function rule_pseudo3() {
    if ($this->_current_token['code'] === CSS_TOKEN_IDENT) { // <PSEUDO3> :: IDENT <S_SEQ_EMPTY> RPAREN
      // *
      $pseudo =& $this->peek_context();
      $pseudo->set_function_param($this->_current_token['value']);

      $this->get_next_token();

      if (!$this->rule_s_seq_empty()) { return false; };

      if ($this->_current_token['code'] !== CSS_TOKEN_RPAREN) {
        return false;
      };
      $this->get_next_token();
      
    } elseif ($this->_current_token['code'] === CSS_TOKEN_SPACE) { // <PSEUDO3> :: <S_SEQ_EMPTY> RPAREN
      if (!$this->rule_s_seq_empty()) { return false; };

      if ($this->_current_token['code'] !== CSS_TOKEN_RPAREN) {
        return false;
      };
      $this->get_next_token();

    } else {
      return false;
    }

    return true;
  }

  function rule_declaration() {
    if ($this->_current_token['code'] === CSS_TOKEN_IDENT) { // <DECLARATION> :: <PROPERTY> DELIM[":"] <S_SEQ_EMPTY> <EXPR> <DECLARATION2>
      // *
      $this->push_context(new CSSStylesheetProperty());

      if (!$this->rule_property()) { 
        $this->pop_context();
        return false; 
      };

      // *
      $property =& $this->pop_context();
      $declaration =& $this->peek_context();
      $declaration->set_property($property);
     
      if ($this->_current_token['code'] !== CSS_TOKEN_DELIM ||
          $this->_current_token['value'] !== ':') {
        return false;
      };
      $this->get_next_token();

      if (!$this->rule_s_seq_empty()) { 
        return false; 
      };

      // *
      $this->push_context(new CSSStylesheetExpr());

      if (!$this->rule_expr()) { 
        $this->pop_context();
        return false; 
      };

      // *
      $expr =& $this->pop_context();
      $declaration =& $this->peek_context();
      $declaration->set_expr($expr);

      if (!$this->rule_declaration2()) { return false; };

    } elseif (in_array($this->_current_token['code'],
                       array(CSS_TOKEN_SEMICOLON,
                             CSS_TOKEN_RBRACE))) { // <DECLARATION> :: <EMPTY>

    } else {
      return false;
    };

    return true;
  }

  function rule_declaration2() {
    if ($this->_current_token['code'] === CSS_TOKEN_DELIM && 
        in_array($this->_current_token['value'], 
                 array('!'))) { // <DECLARATION2> :: <PRIO>
      if (!$this->rule_prio()) { return false; };

    } elseif (in_array($this->_current_token['code'],
                       array(CSS_TOKEN_SEMICOLON,
                             CSS_TOKEN_RBRACE))) { // <DECLARATION2> :: <EMPTY>
      
    } else {
      return false;
    };

    return true;
  }

  function rule_prio() {
    if ($this->_current_token['code'] === CSS_TOKEN_DELIM && 
        in_array($this->_current_token['value'], 
                 array('!'))) { // <PRIO> :: DELIM["!"] IDENT["important"] <S_SEQ_EMPTY>
      $this->get_next_token();

      if ($this->_current_token['code'] !== CSS_TOKEN_IDENT ||
          $this->_current_token['value'] !== 'important') {
        return false;
      };

      // *
      $declaration =& $this->peek_context();
      $declaration->set_important(true);

      $this->get_next_token();

      if (!$this->rule_s_seq_empty()) { return false; };

    } else {
      return false;
    };    

    return true;
  }

  function rule_expr() {
    if ($this->_current_token['code'] === CSS_TOKEN_DELIM && 
        in_array($this->_current_token['value'], 
                 array('-', '+')) ||
        in_array($this->_current_token['code'],
                       array(CSS_TOKEN_NUMBER,
                             CSS_TOKEN_PERCENTAGE,
                             CSS_TOKEN_DIMENSION,
                             CSS_TOKEN_STRING,
                             CSS_TOKEN_IDENT,
                             CSS_TOKEN_URI,
                             CSS_TOKEN_HASH,
                             CSS_TOKEN_FUNCTION))) { // <EXPR> :: <TERM> <EXPR2>
      // *
      $this->push_context(new CSSStylesheetTerm());

      if (!$this->rule_term()) { 
        $this->pop_context();
        return false; 
      };

      // *
      $term =& $this->pop_context();
      $expr =& $this->peek_context();
      $expr->add_term($term);

      if (!$this->rule_expr2()) { return false; };

    } else {
      return false;
    };

    return true;
  }

  function rule_expr2() {
    if ($this->_current_token['code'] === CSS_TOKEN_DELIM && 
        in_array($this->_current_token['value'], 
                 array('-', '+', '/', ',')) ||
        in_array($this->_current_token['code'],
                       array(CSS_TOKEN_NUMBER,
                             CSS_TOKEN_PERCENTAGE,
                             CSS_TOKEN_DIMENSION,
                             CSS_TOKEN_STRING,
                             CSS_TOKEN_IDENT,
                             CSS_TOKEN_URI,
                             CSS_TOKEN_HASH,
                             CSS_TOKEN_FUNCTION))) { // <EXPR2> :: <OPERATOR> <TERM> <EXPR2>

      // *
      $this->push_context(new CSSStylesheetOperator());

      if (!$this->rule_operator()) { 
        $this->pop_context();
        return false; 
      };

      // *
      $operator =& $this->pop_context();
      $expr =& $this->peek_context();
      $expr->add_operator($operator);

      // *
      $this->push_context(new CSSStylesheetTerm());

      if (!$this->rule_term()) { 
        $this->pop_context();
        return false; 
      };

      // *
      $term =& $this->pop_context();
      $expr =& $this->peek_context();
      $expr->add_term($term);

      if (!$this->rule_expr2()) { return false; };

    } elseif ($this->_current_token['code'] === CSS_TOKEN_DELIM && 
              in_array($this->_current_token['value'], 
                       array('!')) ||
              in_array($this->_current_token['code'],
                       array(CSS_TOKEN_SEMICOLON,
                             CSS_TOKEN_RBRACE,
                             CSS_TOKEN_RPAREN))) { // <EXPR2> :: <EMPTY>
      
    } else {
      return false;
    };

    return true;
  }

  function rule_term() {
    if ($this->_current_token['code'] === CSS_TOKEN_DELIM && 
        in_array($this->_current_token['value'], 
                 array('-', '+'))) { // <TERM> :: <UNARY_OPERATOR> <TERM2>
      // *
      $this->push_context(new CSSStylesheetUnaryOperator());
      
      if (!$this->rule_unary_operator()) { 
        $this->pop_context();
        return false; 
      };

      // *
      $operator =& $this->pop_context();
      $term =& $this->peek_context();
      $term->set_unary_operator($operator);

      if (!$this->rule_term2()) { return false; };

    } elseif (in_array($this->_current_token['code'],
                       array(CSS_TOKEN_NUMBER,
                             CSS_TOKEN_PERCENTAGE,
                             CSS_TOKEN_DIMENSION))) { // <TERM> :: <TERM2>
      if (!$this->rule_term2()) { return false; };

    } elseif (in_array($this->_current_token['code'],
                       array(CSS_TOKEN_STRING))) { // <TERM> :: STRING <S_SEQ_EMPTY>
      // *
      $term =& $this->peek_context();
      $term->set_value($this->_current_token['value']);

      $this->get_next_token();

      if (!$this->rule_s_seq_empty()) { return false; };
      
    } elseif (in_array($this->_current_token['code'],
                       array(CSS_TOKEN_IDENT))) { // <TERM> :: IDENT <S_SEQ_EMPTY>
      // *
      $term =& $this->peek_context();
      $term->set_value($this->_current_token['value']);

      $this->get_next_token();

      if (!$this->rule_s_seq_empty()) { return false; };

    } elseif (in_array($this->_current_token['code'],
                       array(CSS_TOKEN_URI))) { // <TERM> :: URI <S_SEQ_EMPTY>
      // *
      $term =& $this->peek_context();
      $term->set_value($this->_current_token['value']);

      $this->get_next_token();

      if (!$this->rule_s_seq_empty()) { return false; };
      
    } elseif (in_array($this->_current_token['code'],
                       array(CSS_TOKEN_HASH))) { // <TERM> :: <HEXCOLOR>

      if (!$this->rule_hexcolor()) { return false; };
      
    } elseif (in_array($this->_current_token['code'],
                       array(CSS_TOKEN_FUNCTION))) { // <TERM> :: <FUNCTION>

      if (!$this->rule_function()) { return false; };
      
    } else {
      return false;
    };

    return true;
  }

  function rule_term2() {
    if (in_array($this->_current_token['code'],
                 array(CSS_TOKEN_NUMBER))) { // <TERM2> :: NUMBER <S_SEQ_EMPTY>
      // *
      $term =& $this->peek_context();
      $term->set_value($this->_current_token['value']);

      $this->get_next_token();

      if (!$this->rule_s_seq_empty()) { return false; };
      
    } elseif (in_array($this->_current_token['code'],
                       array(CSS_TOKEN_PERCENTAGE))) { // <TERM2> :: PERCENTAGE <S_SEQ_EMPTY>
      // *
      $term =& $this->peek_context();
      $term->set_value($this->_current_token['value']);

      $this->get_next_token();

      if (!$this->rule_s_seq_empty()) { return false; };

    } elseif (in_array($this->_current_token['code'],
                       array(CSS_TOKEN_DIMENSION))) { // <TERM2> :: DIMENSION <S_SEQ_EMPTY>
      // *
      $term =& $this->peek_context();
      $term->set_value($this->_current_token['value']);

      $this->get_next_token();

      if (!$this->rule_s_seq_empty()) { return false; };

    } else {
      return false;
    };

    return true;
  }

  function rule_function() {
    if (in_array($this->_current_token['code'],
                 array(CSS_TOKEN_FUNCTION))) { // <FUNCTION> :: FUNCTION <S_SEQ_EMPTY> <EXPR> RPAREN <S_SEQ_EMPTY>
      // *
      $term =& $this->peek_context();
      $term->set_function_name($this->_current_token['value']);

      $this->get_next_token();

      if (!$this->rule_s_seq_empty()) { return false; };

      // *
      $this->push_context(new CSSStylesheetExpr());
      
      if (!$this->rule_expr()) { 
        $this->pop_context();
        return false; 
      };

      // *
      $expr =& $this->pop_context();
      $term =& $this->peek_context();
      $term->set_function_param($expr);
      
      if ($this->_current_token['code'] !== CSS_TOKEN_RPAREN) {
        return false;
      };
      $this->get_next_token();

      if (!$this->rule_s_seq_empty()) { return false; };

    } else {
      return false;
    };

    return true;
  }

  function rule_hexcolor() {
    if (in_array($this->_current_token['code'],
                 array(CSS_TOKEN_HASH))) { // <HEXCOLOR> :: HASH <S_SEQ_EMPTY>
      // *
      $term =& $this->peek_context();
      $term->set_value($this->_current_token['value']);

      $this->get_next_token();

      if (!$this->rule_s_seq_empty()) { return false; };

    } else {
      return false;
    };

    return true;
  }

  function rule_s_cdo_cdc_seq() {
    if (in_array($this->_current_token['code'],
                 array(CSS_TOKEN_SPACE))) { // <S_CDO_CDC_SEQ> :: SPACE <S_CDO_CDC_SEQ_EMPTY>
      $this->get_next_token();

      if (!$this->rule_s_cdo_cdc_seq_empty()) { return false; };

    } elseif (in_array($this->_current_token['code'],
                       array(CSS_TOKEN_CDO))) { // <S_CDO_CDC_SEQ> :: CDO <S_CDO_CDC_SEQ_EMPTY>
      $this->get_next_token();

      if (!$this->rule_s_cdo_cdc_seq_empty()) { return false; };

    } elseif (in_array($this->_current_token['code'],
                       array(CSS_TOKEN_CDC))) { // <S_CDO_CDC_SEQ> :: CDC <S_CDO_CDC_SEQ_EMPTY>
      $this->get_next_token();

      if (!$this->rule_s_cdo_cdc_seq_empty()) { return false; };

    } else {
      return false;
    };

    return true;
  }

  function rule_s_cdo_cdc_seq_empty() {
    if (in_array($this->_current_token['code'],
                 array(CSS_TOKEN_SPACE))) { // <S_CDO_CDC_SEQ_EMPTY> :: SPACE <S_CDO_CDC_SEQ_EMPTY>
      $this->get_next_token();

      if (!$this->rule_s_cdo_cdc_seq_empty()) { return false; };
      
    } elseif (in_array($this->_current_token['code'],
                       array(CSS_TOKEN_CDO))) { // <S_CDO_CDC_SEQ_EMPTY> :: CDO <S_CDO_CDC_SEQ_EMPTY>
      $this->get_next_token();

      if (!$this->rule_s_cdo_cdc_seq_empty()) { return false; };

    } elseif (in_array($this->_current_token['code'],
                       array(CSS_TOKEN_CDC))) { // <S_CDO_CDC_SEQ_EMPTY> :: CDC <S_CDO_CDC_SEQ_EMPTY>
      $this->get_next_token();

      if (!$this->rule_s_cdo_cdc_seq_empty()) { return false; };

    } elseif (in_array($this->_current_token['code'],
                       array(CSS_TOKEN_CDC))) { // <S_CDO_CDC_SEQ_EMPTY> :: CDC <S_CDO_CDC_SEQ_EMPTY>
      $this->get_next_token();

      if (!$this->rule_s_cdo_cdc_seq_empty()) { return false; };

    } elseif ($this->_current_token['code'] === CSS_TOKEN_ATKEYWORD &&
              in_array($this->_current_token['value'],
                       array('@import', '@media', '@page')) ||
              $this->_current_token['code'] === CSS_TOKEN_DELIM &&
              in_array($this->_current_token['value'],
                       array('.', ':', '*')) ||
              in_array($this->_current_token['code'],
                       array(CSS_TOKEN_HASH,
                             CSS_TOKEN_LBRACK,
                             CSS_TOKEN_IDENT,
                             null))) { // <S_CDO_CDC_SEQ_EMPTY> :: <EMPTY>
    } elseif ($this->_current_token['code'] === CSS_TOKEN_ATKEYWORD) {
      // Handle syntax error: invalid @keyword
      if (!$this->skip_to(array(CSS_TOKEN_LBRACE,
                                CSS_TOKEN_SEMICOLON))) {
        return false;
      };

      if ($this->_current_token['code'] == CSS_TOKEN_LBRACE) {
        $this->get_next_token();
        if (is_null($this->skip_to(array(CSS_TOKEN_RBRACE)))) {
          return false;
        };
      };

      $this->get_next_token();

      if (!$this->rule_s_cdo_cdc_seq_empty()) {
        return false;
      };
      
    } else {
      return false;
    };

    return true;
  }

  function rule_s_seq_empty() {
    if (in_array($this->_current_token['code'],
                 array(CSS_TOKEN_SPACE))) { // <S_SEQ_EMPTY> :: SPACE <S_SEQ_EMPTY>
      $this->get_next_token();

      if (!$this->rule_s_seq_empty()) { return false; };
      
    } else { // <S_SEQ_EMPTY> :: <EMPTY>
      return true;
    };

    return true;
  }

  function rule_medium_seq() {
    if (in_array($this->_current_token['code'],
                 array(CSS_TOKEN_IDENT))) { // <MEDIUM_SEQ> :: <MEDIUM> <MEDIUM_SEQ_END> 
      if (!$this->rule_medium()) { return false; };
      if (!$this->rule_medium_seq_end()) { return false; };

    } else { 
      return false;
    };

    return true;
  }

  function rule_medium_seq_end() {
    if ($this->_current_token['code'] === CSS_TOKEN_DELIM && 
        in_array($this->_current_token['value'],
                 array(','))) { // <MEDIUM_SEQ_END> :: DELIM[","] <S_SEQ_EMPTY> <MEDIUM> <MEDIUM_SEQ_END>
      $this->get_next_token();

      if (!$this->rule_s_seq_empty()) { return false; };
      if (!$this->rule_medium()) { return false; };
      if (!$this->rule_medium_seq_end()) { return false; };

    } elseif (in_array($this->_current_token['code'],
                       array(CSS_TOKEN_SEMICOLON,
                             CSS_TOKEN_LBRACE,
                             CSS_TOKEN_SPACE))) { // <MEDIUM_SEQ_END> :: <S_SEQ_EMPTY>
      $this->rule_s_seq_empty();

    } else {
      return false;
    };

    return true;
  }

  function rule_ruleset_seq_empty() {
    if ($this->_current_token['code'] === CSS_TOKEN_DELIM && 
        in_array($this->_current_token['value'],
                 array('.', ':', '*')) ||
        in_array($this->_current_token['code'], 
                 array(CSS_TOKEN_HASH,
                       CSS_TOKEN_LBRACK,
                       CSS_TOKEN_IDENT))) { // <RULESET_SEQ_EMPTY> :: <RULESET> <RULESET_SEQ_EMPTY>
      // * 
      $this->push_context(new CSSStylesheetRuleset());

      if (!$this->rule_ruleset()) { 
        $this->pop_context();

        if (is_null($this->skip_to(CSS_TOKEN_RBRACE))) {
          return false; 
        };
        $this->get_next_token();
      } else {
        // *
        $ruleset =& $this->pop_context();
        $ruleset_collection =& $this->peek_context();
        $ruleset_collection->add($ruleset);
      };

      if (!$this->rule_ruleset_seq_empty()) { 
        return false; 
      };

    } elseif (in_array($this->_current_token['code'],
                       array(CSS_TOKEN_RBRACE))) { // <RULESET_SEQ_EMPTY> :: <EMPTY>

    } else {
      return false;
    };
    
    return true;
  }

  function rule_declaration_seq() {
    if (in_array($this->_current_token['code'],
                 array(CSS_TOKEN_IDENT,
                       CSS_TOKEN_SEMICOLON,
                       CSS_TOKEN_RBRACE))) { // <DECLARATION_SEQ> :: <DECLARATION> <DECLARATION_SEQ_END>
      // *
      $this->push_context(new CSSStylesheetDeclaration());

      while (!$this->rule_declaration()) { 
        $this->pop_context();
        $this->push_context(new CSSStylesheetDeclaration());
        
        if (is_null($this->skip_to(CSS_TOKEN_SEMICOLON))) {
          $this->pop_context();
          return false;
        };
      };

      // *
      $declaration =& $this->pop_context();
      $declaration_collection =& $this->peek_context();
      $declaration_collection->add($declaration);

      if (!$this->rule_declaration_seq_end()) { return false; };

    } else {
      return false;
    };

    return true;
  }

  function rule_declaration_seq_end() {
    if (in_array($this->_current_token['code'],
                 array(CSS_TOKEN_SEMICOLON))) { // <DECLARATION_SEQ_END> :: SEMICOLON <S_SEQ_EMPTY> <DECLARATION> <DECLARATION_SEQ_END>
      $this->get_next_token();

      if (!$this->rule_s_seq_empty()) { return false; };

      // *
      $this->push_context(new CSSStylesheetDeclaration());

      while (!$this->rule_declaration()) { 
        $this->pop_context();
        $this->push_context(new CSSStylesheetDeclaration());

        if (is_null($this->skip_to(CSS_TOKEN_SEMICOLON))) {
          $this->pop_context();
          return false;
        };
      };

      // *
      $declaration =& $this->pop_context();
      $declaration_collection =& $this->peek_context();
      $declaration_collection->add($declaration);

      if (!$this->rule_declaration_seq_end()) { return false; };
      
    } elseif (in_array($this->_current_token['code'],
                       array(CSS_TOKEN_RBRACE))) { // <DECLARATION_SEQ_END> :: <EMPTY>
      
    } else {
      return false;
    };
              
    return true;
  }

  function rule_selector_seq() {
    if ($this->_current_token['code'] === CSS_TOKEN_DELIM && 
        in_array($this->_current_token['value'],
                 array('.', ':', '*')) ||
        in_array($this->_current_token['code'], 
                 array(CSS_TOKEN_HASH,
                       CSS_TOKEN_LBRACK,
                       CSS_TOKEN_IDENT))) { // <SELECTOR_SEQ> :: <SELECTOR> <SELECTOR_SEQ_END>
      // *
      $this->push_context(new CSSStylesheetSelector());

      if (!$this->rule_selector()) { 
        $this->pop_context();
        return false; 
      };

      // *
      $selector =& $this->pop_context();
      $selector_collection =& $this->peek_context();
      $selector_collection->add($selector);

      if (!$this->rule_selector_seq_end()) { return false; };

    } else {
      return false;
    };

    return true;
  }

  function rule_selector_seq_end() {
    if ($this->_current_token['code'] === CSS_TOKEN_DELIM && 
        in_array($this->_current_token['value'], 
                 array(','))) { // <SELECTOR_SEQ_END> :: DELIM[","] <S_SEQ_EMPTY> <SELECTOR> <SELECTOR_SEQ_END>
      $this->get_next_token();

      if (!$this->rule_s_seq_empty()) { return false; };

      // *
      $this->push_context(new CSSStylesheetSelector());
      
      if (!$this->rule_selector()) { return false; };

      // *
      $selector =& $this->pop_context();
      $selector_collection =& $this->peek_context();
      $selector_collection->add($selector);

      if (!$this->rule_selector_seq_end()) { return false; };

    } elseif (in_array($this->_current_token['code'],
                       array(CSS_TOKEN_LBRACE))) { // <SELECTOR_SEQ_END> :: <EMPTY>

    } else {
      return false;
    };
    
    return true;
  }
  
  function rule_attrib_op() {
    if ($this->_current_token['code'] === CSS_TOKEN_DELIM && 
        in_array($this->_current_token['value'], 
                 array('='))) { // <ATTRIB_OP> :: DELIM["="]
      // * 
      $attrib =& $this->peek_context();
      $attrib->set_op(ATTRIB_OP_EQUAL);

      $this->get_next_token();

    } elseif (in_array($this->_current_token['code'],
                       array(CSS_TOKEN_INCLUDES))) { // <ATTRIB_OP> :: INCLUDES
      // * 
      $attrib =& $this->peek_context();
      $attrib->set_op(ATTRIB_OP_INCLUDES);

      $this->get_next_token();

    } elseif (in_array($this->_current_token['code'],
                       array(CSS_TOKEN_DASHMATCH))) { // <ATTRIB_OP> :: DASHMATCH
      // * 
      $attrib =& $this->peek_context();
      $attrib->set_op(ATTRIB_OP_DASHMATCH);

      $this->get_next_token();

    } else {
      return false;
    };

    return true;
  }

  function rule_attrib_value() {
    if ($this->_current_token['code'] === CSS_TOKEN_IDENT) { // <ATTRIB_VALUE> :: IDENT
      // *
      $attrib =& $this->peek_context();
      $attrib->set_value($this->_current_token['value']);

      $this->get_next_token();

    } elseif (in_array($this->_current_token['code'],
                       array(CSS_TOKEN_STRING))) { // <ATTRIB_VALUE> :: STRING
      // *
      $attrib =& $this->peek_context();
      $attrib->set_value($this->_current_token['value']);

      $this->get_next_token();

    } else {
      return false;
    };
    
    return true;
  }

  function skip_to($token_code, $token_value = null) {
    if (!is_array($token_code)) {
      $token_code = array($token_code);
    };

    $token_stack = array();

    $skipped_content = '';

    do {   
      $codes_matched = in_array($this->_current_token['code'], $token_code);
      $values_matched = $this->_current_token['value'] === $token_value;
      $value_present = !is_null($token_value);
      $eof = is_null($this->_current_token['code']);
      $token_stack_empty = (count($token_stack) == 0);
      $loop_terminated = 
        $eof ||
        ($codes_matched && 
         ($values_matched || !$value_present) && 
         $token_stack_empty);

      if (in_array($this->_current_token['code'],
                   array(CSS_TOKEN_LBRACE,
                         CSS_TOKEN_LPAREN,
                         CSS_TOKEN_LBRACK))) {
        array_unshift($token_stack, $this->_current_token['code']);
      } elseif ($this->_current_token['code'] === CSS_TOKEN_RBRACE &&
                count($token_stack) > 0 && 
                $token_stack[0] == CSS_TOKEN_LBRACE) {
        array_shift($token_stack);
      } elseif ($this->_current_token['code'] === CSS_TOKEN_RPAREN &&
                count($token_stack) > 0 && 
                $token_stack[0] == CSS_TOKEN_LPAREN) {
        array_shift($token_stack);
      } elseif ($this->_current_token['code'] === CSS_TOKEN_RBRACK &&
                count($token_stack) > 0 && 
                $token_stack[0] == CSS_TOKEN_LBRACK) {
        array_shift($token_stack);
      }

      if (!$loop_terminated) {
        $this->get_next_token();
        $skipped_content .= $this->_current_token['value'];
      };
    } while (!$loop_terminated);

    if (is_null($this->_current_token['code'])) {
      return null;
    };

    return $skipped_content;
  }
}

?>