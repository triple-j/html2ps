<?php

// Just a simple'n'dumb recursive lexer.
// I love writing this manually (not really, but I'm into auto-training thing today).
// Corresponding LL(1) grammar - well, /mostly/ LL(1) grammer :) - is in tokens.php

require_once(HTML2PS_DIR.'css/interface.lexer.php');
require_once(HTML2PS_DIR.'css/tokens.php');

class CSSLexer extends ICSSLexer {
  var $_stream;
  var $_token_value;
  var $_line;

  function CSSLexer(&$stream) {
    $this->_stream =& $stream;
    $this->_line = 1;
  }

  function get_line() {
    return $this->_line;
  }

  function get_token_context_before() { 
    return $this->_stream->get_context_before(10);
  }

  function get_token_context_after() { 
    return $this->_stream->get_context_after(10);
  }

  function next_token() {
    $this->_token_value = '';
    $this->_token_code = null;

    $this->rule_start();

    if (is_null($this->_token_code) && 
        $this->_token_value != '') {
      $this->_token_code = CSS_TOKEN_DELIM;
    };

    $token = array('code' => $this->_token_code, 
                   'line' => $this->_line,
                   'value' => $this->_token_value);

    $this->_line += preg_match_all("/\r\n|\r|\n/", $this->_token_value, $matches);

    return $token;
  }

  function rule_start() {
    $char = $this->_stream->peek();

    if ($char === 'u' || $char === 'U') { // <IDENT_URI_FUNCTION_UNICODE> :: u<!IDENT_URI_FUNCTION_UNICODE2_EMPTY> :: u
      $this->_token_value .= $char;
      $this->_stream->next();

      if (!$this->rule_ident_uri_function_unicode2_empty()) { return false; };

    } elseif ($char === '-') { // <IDENT_URI_FUNCTION_UNICODE> :: -<!NMSTART><!IDENT_FUNCTION_EMPTY> :: -
      $this->_token_value .= $char;
      $this->_stream->next();

      if (!$this->rule_ident_function_cdc()) { return false; };

    } elseif (preg_match('/[_a-tv-z]/i', $char)) { // <IDENT_URI_FUNCTION_UNICODE> :: [_a-z][^u] <!IDENT_FUNCTION_EMPTY> :: [_a-z][^u] 
      $this->_token_value .= $char;
      $this->_stream->next();

      if (!$this->rule_ident_function_empty()) { return false; };

    } elseif (preg_match('/[^\0-\177]/i', $char)) { // <IDENT_URI_FUNCTION_UNICODE> :: [^\0-\177]<!IDENT_FUNCTION_EMPTY> :: [^\0-\177]
      $this->_token_value .= $char;
      $this->_stream->next();

      if (!$this->rule_ident_function_empty()) { return false; };

    } elseif ($char === '\\') { // <IDENT_URI_FUNCTION_UNICODE> :: <!ESCAPE><!IDENT_FUNCTION_EMPTY> :: \
      if (!$this->rule_escape()) { return false; };
      if (!$this->rule_ident_function_empty()) { return false; };

    } elseif ($char === '@') { // <ATKEYWORD> :: @<IDENT> :: @
      $this->_token_value .= $char;
      $this->_stream->next();

      if (!$this->rule_ident()) { return false; };

      $this->_token_code = CSS_TOKEN_ATKEYWORD;

    } elseif ($char === '"') { // <STRING_OR_INVALID> :: <!STRING1_START><!STRING_OR_INVALID_1_END> :: '
      if (!$this->rule_string1_start()) { return false; };
      if (!$this->rule_string_or_invalid_1_end()) { return false; };

    } elseif ($char === '\'') { // <STRING_OR_INVALID> :: <!STRING2_START><!STRING_OR_INVALID_2_END> :: "
      if (!$this->rule_string2_start()) { return false; };
      if (!$this->rule_string_or_invalid_2_end()) { return false; };

    } elseif ($char === '#') { // <HASH> :: #<!NAME> :: #
      $this->_token_value .= $char;
      $this->_stream->next();

      if (!$this->rule_name()) { return false; };

      $this->_token_code = CSS_TOKEN_HASH;

    } elseif (preg_match('/[0-9.]/', $char)) { // <NUM_PERC_DIM> :: <!NUM><!NUM_PERC_DIM_END> :: [0-9] | .
      if (!$this->rule_num()) { return false; };
      if (!$this->rule_num_perc_dim_end()) { return false; };

    } elseif ($char === '<') { // <CDO> :: <!--<?TOKEN_CDO> :: <
      if (!$this->_stream->read_expected('<!--', $this->_token_value)) { return false; };

      $this->_token_code = CSS_TOKEN_CDO;

    } elseif ($char === ';') { // <SEMICOLON> :: ;<?TOKEN_SEMICOLON> :: ;
      $this->_token_value .= $char;
      $this->_stream->next();

      $this->_token_code = CSS_TOKEN_SEMICOLON;

    } elseif ($char === '{') { // <LBRACE> :: {<?TOKEN_LBRACE> :: {
      $this->_token_value .= $char;
      $this->_stream->next();

      $this->_token_code = CSS_TOKEN_LBRACE;

    } elseif ($char === '}') { // <RBRACE> :: }<?TOKEN_RBRACE> :: }
      $this->_token_value .= $char;
      $this->_stream->next();

      $this->_token_code = CSS_TOKEN_RBRACE;

    } elseif ($char === '(') { // <LPAREN> :: (<?TOKEN_LPAREN> :: (
      $this->_token_value .= $char;
      $this->_stream->next();

      $this->_token_code = CSS_TOKEN_LPAREN;

    } elseif ($char === ')') { // <RPAREN> :: )<?TOKEN_RPAREN> :: )
      $this->_token_value .= $char;
      $this->_stream->next();

      $this->_token_code = CSS_TOKEN_RPAREN;

    } elseif ($char === '[') { // <LBRACK> :: [<?TOKEN_LBRACK> :: [
      $this->_token_value .= $char;
      $this->_stream->next();

      $this->_token_code = CSS_TOKEN_LBRACK;

    } elseif ($char === ']') { // <RBRACK> :: ]<?TOKEN_RBRACK> :: ]
      $this->_token_value .= $char;
      $this->_stream->next();

      $this->_token_code = CSS_TOKEN_RBRACK;

    } elseif (preg_match('/[ \t\r\n\f]/i', $char)) { // <SPACE> :: [ \t\r\n\f]<!W> :: [ \t\r\n\f]
      $this->_token_value .= $char;
      $this->_stream->next();

      if (!$this->rule_w()) { return false; };

      $this->_token_code = CSS_TOKEN_SPACE;

    } elseif ($char === '/') { // <COMMENT> :: /*<!NOT_STAR_SEQ_OR_EMPTY><!STAR_SEQ><!COMMENT_CONTENT_SEQ_OR_EMPTY>/ :: /
      if (!$this->_stream->read_expected('/*', $this->_token_value)) { return false; };
      if (!$this->rule_not_star_seq_or_empty()) { return false; };
      if (!$this->rule_star_seq()) { return false; };
      if (!$this->rule_comment_content_seq_or_empty()) { return false; };
      if (!$this->_stream->read_expected('/', $this->_token_value)) { return false; };

      $this->_token_code = CSS_TOKEN_COMMENT;

    } elseif ($char === '~') {
      if (!$this->_stream->read_expected('~=', $this->_token_value)) { return false; };

      $this->_token_code = CSS_TOKEN_INCLUDES;
      
    } elseif ($char === '|') {
      if (!$this->_stream->read_expected('|=', $this->_token_value)) { return false; };

      $this->_token_code = CSS_TOKEN_DASHMATCH;
    } else {
      $this->_token_value .= $char;
      $this->_stream->next();

      return false;
    };

    return true;
  }

  function rule_ident_function_cdc() {
    $char = $this->_stream->peek();

    if (preg_match('/[_a-z]|[^\0-\177]|\\\\/i', $char)) {
      if (!$this->rule_nmstart()) { return false; };
      if (!$this->rule_ident_function_empty()) { return false; };

    } elseif ($char === '-') { // <!IDENT_URI_FUNCTION_UNICODE2_EMPTY> :: +<!UNICODE_RANGE> :: +
      $this->rule_cdc2();

    } else {
      return false;
    };

    return true;
  }

  function rule_cdc2() {
    $char = $this->_stream->peek();

    if ($char === '-') { // <CDC2> :: -><?TOKEN_CDC> :: -
      if (!$this->_stream->read_expected('->', $this->_token_value)) { return false; };

      $this->_token_code = CSS_TOKEN_CDC;
    } else {
      return false;
    };

    return true;
  }

  function rule_ident_uri_function_unicode2_empty() {
    $char = $this->_stream->peek();

    if ($char === '+') { // <!IDENT_URI_FUNCTION_UNICODE2_EMPTY> :: +<!UNICODE_RANGE> :: +
      $this->_token_value .= $char;
      $this->_stream->next();

      if (!$this->rule_unicode_range()) { return false; };

    } elseif ($char === 'r' || $char === 'R') { // <!IDENT_URI_FUNCTION_UNICODE2_EMPTY> :: r<!IDENT_URI_FUNCTION_EMPTY> :: r
      $this->_token_value .= $char;
      $this->_stream->next();

      if (!$this->rule_ident_uri_function_empty()) { return false; };

    } elseif (preg_match('/[_a-qs-z0-9-]/i', $char)) { // <!IDENT_URI_FUNCTION_UNICODE2_EMPTY> :: [_a-z0-9-][^r]<!IDENT_FUNCTION_EMPTY> :: [_a-z0-9-][^r] 
      $this->_token_value .= $char;
      $this->_stream->next();

      if (!$this->rule_ident_function_empty()) { return false; };

    } elseif (preg_match('/[^\0-\177]/', $char)) { // <!IDENT_URI_FUNCTION_UNICODE2_EMPTY> :: [^\0-\177]<!IDENT_FUNCTION_EMPTY> :: [^\0-\177]
      $this->_token_value .= $char;
      $this->_stream->next();

      if (!$this->rule_ident_function_empty()) { return false; };

    } elseif ($char === '\\') { // <!IDENT_URI_FUNCTION_UNICODE2_EMPTY> :: <!ESCAPE><!IDENT_FUNCTION_EMPTY> :: \
      if (!$this->rule_escape()) { return false; };
      if (!$this->rule_ident_function_empty()) { return false; };

    } else { // <!IDENT_URI_FUNCTION_UNICODE2_EMPTY> :: <!FUNCTION_EMPTY> :: ( | END/OTHER
      if (!$this->rule_function_empty()) { return false; };
    };

    return true;
  }

  function rule_ident_uri_function_empty() {
    $char = $this->_stream->peek();

    if ($char === 'l' || $char === 'L') { // <!IDENT_URI_FUNCTION_EMPTY> :: l<!IDENT_URI_FUNCTION2_EMPTY> :: l
      $this->_token_value .= $char;
      $this->_stream->next();

      if (!$this->rule_ident_uri_function2_empty()) { return false; };
      
    } elseif (preg_match('/[_a-km-z0-9-]/i', $char)) { // <!IDENT_URI_FUNCTION_EMPTY> :: [_a-z0-9-][^l]<!IDENT_FUNCTION_EMPTY> :: [_a-z0-9-][^l]
      $this->_token_value .= $char;
      $this->_stream->next();

      if (!$this->rule_ident_function_empty()) { return false; };

    } elseif (preg_match('/[^\0-\177]/', $char)) { // <!IDENT_URI_FUNCTION_EMPTY> :: [^\0-\177]<!IDENT_FUNCTION_EMPTY> :: [^\0-\177]
      $this->_token_value .= $char;
      $this->_stream->next();

      if (!$this->rule_ident_function_empty()) { return false; };     

    } elseif ($char === '\\') { // <!IDENT_URI_FUNCTION_EMPTY> :: <!ESCAPE><!IDENT_FUNCTION_EMPTY> :: \
      if (!$this->rule_escape()) { return false; };     
      if (!$this->rule_ident_function_empty()) { return false; };     

    } else { // <!IDENT_URI_FUNCTION_EMPTY> :: <!FUNCTION_EMPTY> :: ( | END/OTHER
      if (!$this->rule_function_empty()) { return false; };     

    };

    return true;
  }

  function rule_ident_uri_function2_empty() {
    $char = $this->_stream->peek();

    if ($char === '(') { // <!IDENT_URI_FUNCTION2_EMPTY> :: (<URI> :: (
      $this->_token_value .= $char;
      $this->_stream->next();

      if (!$this->rule_w()) { return false; };
      if (!$this->rule_uri2()) { return false; };

    } elseif (preg_match('/[_a-z0-9-]|[^\0-\177]|\\\\/i', $char)) { // <!IDENT_URI_FUNCTION2_EMPTY> :: <!NMCHAR><!IDENT_FUNCTION_EMPTY> :: [_a-z0-9-] | [^\0-\177] | \
      if (!$this->rule_nmchar()) { return false; };
      if (!$this->rule_ident_function_empty()) { return false; };

    } else { // <!IDENT_URI_FUNCTION2_EMPTY> :: <!EMPTY><?TOKEN_IDENT> :: END/OTHER
      $this->_token_code = CSS_TOKEN_IDENT;
    };

    return true;
  }

  function rule_ident_function_empty() {
    $char = $this->_stream->peek();

    if (preg_match('/[_a-z0-9-]|[^\0-\177]|\\\\/i', $char)) { // <!IDENT_FUNCTION_EMPTY> :: <!IDENT><!FUNCTION_EMPTY> :: [_a-z0-9-] | [^\0-\177] | \
      if (!$this->rule_nmchar_seq()) { return false; };
      if (!$this->rule_function_empty()) { return false; };

    } else { // <!IDENT_FUNCTION_EMPTY> :: <!FUNCTION_EMPTY> :: ( | END/OTHER
      if (!$this->rule_function_empty()) { return false; };

    };

    return true;
  }

  function rule_function_empty() {
    $char = $this->_stream->peek();

    if ($char === '(') { // <!FUNCTION_EMPTY> :: (<?TOKEN_FUNCTION> :: (
      $this->_token_value .= $char;
      $this->_stream->next();

      $this->_token_code = CSS_TOKEN_FUNCTION;

    } else { // <!FUNCTION_EMPTY> :: <!EMPTY><?TOKEN_IDENT> :: END/OTHER
      $this->_token_code = CSS_TOKEN_IDENT;

    };

    return true;
  }

  function rule_unicode_range() {
    // <!UNICODE-RANGE> :: [0-9a-f?]{1,6}(-[0-9a-f]{1,6})?<?TOKEN_UNICODE_RANGE> :: [0-9a-f?]
    $str = $this->_stream->peek(13);
    if (!preg_match('/^([0-9a-f?]{1,6}(?:-[0-9a-f]{1,6})?)/i', $str, $matches)) {
      return false;
    };
    $this->_stream->next(strlen($matches[1]));
                   
    $this->_token_value .= $matches[1];
    $this->_token_code = CSS_TOKEN_UNICODE_RANGE;

    return true;
  }

  function rule_uri2() {
    $char = $this->_stream->peek();

    if (preg_match('/["\']/i', $char)) { // <!URI2> :: <!STRING><!W>)<?TOKEN_URI> :: "'
      if (!$this->rule_string()) { return false; };
      if (!$this->rule_w()) { return false; };
      if (!$this->_stream->read_expected(')', $this->_token_value)) { return false; }

      $this->_token_code = CSS_TOKEN_URI;

    } elseif (preg_match('/[!#$%&*~-]|[^\0-\177]|\\\\/i', $char)) { // <!URI2> :: <!URI2_SEQ_OR_EMPTY><!W>)<?TOKEN_URI> :: [!#$%&*-~] | [^\0-\177] | \
      if (!$this->rule_uri2_seq_or_empty()) { return false; };
      if (!$this->rule_w()) { return false; };     
      if (!$this->_stream->read_expected(')', $this->_token_value)) { return false; };

      $this->_token_code = CSS_TOKEN_URI;
   
    } else {
      if (!$this->rule_broken_uri_string()) { return false; };
      if (!$this->rule_w()) { return false; };
      if (!$this->_stream->read_expected(')', $this->_token_value)) { return false; }

      $this->_token_code = CSS_TOKEN_URI;
    }

    return true;
  }

  function rule_broken_uri_string() {
    $char = $this->_stream->peek();

    if (!preg_match('/[ \t\r\n\f)]/i', $char)) { // <!BROKEN_URI_STRING> :: [^)]<!BROKEN_URI_STRING> :: [^)]
      $this->_token_value .= $char;
      $this->_stream->next();

      if (!$this->rule_broken_uri_string()) { return false; };

    } else { // <!BROKEN_URI_STRING> :: <EMPTY> :: ) | [ \t\r\n\f]

    };

    return true;
  }

  function rule_uri2_seq_or_empty() {
    $char = $this->_stream->peek();

    if (preg_match('/[!#$%&*~-]/i', $char)) { // <!URI2_SEQ_OR_EMPTY> :: [!#$%&*-~]<!URI2_SEQ_OR_EMPTY> :: [!#$%&*-~]
      $this->_token_value .= $char;
      $this->_stream->next();

      if (!$this->rule_uri2_seq_or_empty()) { return false; };

    } elseif (preg_match('/[^\0-\177]/i', $char)) { // <!URI2_SEQ_OR_EMPTY> :: <!NONASCII><!URI2_SEQ_OR_EMPTY> :: [^\0-\177]
      if (!$this->rule_nonascii()) { return false; };
      if (!$this->rule_uri2_seq_or_empty()) { return false; };

    } elseif ($char === '\\') { // <!URI2_SEQ_OR_EMPTY> :: <!ESCAPE><!URI2_SEQ_OR_EMPTY> :: \
      if (!$this->rule_escape()) { return false; };
      if (!$this->rule_uri2_seq_or_empty()) { return false; };

    } elseif (preg_match('/[ \t\r\n\f]|\)|"\'|[!#$%&*~-]|[^\0-\177]|\\\\/i', $char)) { // <!URI2_SEQ_OR_EMPTY> :: <!EMPTY> :: [ \t\r\n\f] | ) | "' | [!#$%&*-~] | [^\0-\177] | \ 
      
    } else {
      return false;
    };

    return true;
  }

  function rule_comment_content_seq_or_empty() {
    $char = $this->_stream->peek();

    if (preg_match('/[^\/*]/i', $char)) { // <!COMMENT_CONTENT_SEQ_OR_EMPTY> :: [^/*]<!NOT_STAR_SEQ_OR_EMPTY><!STAR_SEQ><!COMMENT_CONTENT_SEQ_OR_EMPTY> :: [^/*]
      $this->_token_value .= $char;
      $this->_stream->next();

      if (!$this->rule_not_star_seq_or_empty()) { return false; };
      if (!$this->rule_star_seq()) { return false; };
      if (!$this->rule_comment_content_seq_or_empty()) { return false; };
      
    } elseif ($char === '/') { // <!COMMENT_CONTENT_SEQ_OR_EMPTY> :: <!EMPTY> :: /

    } else {
      return false;
    };

    return true;
  }

  function rule_not_star_seq_or_empty() {
    $char = $this->_stream->peek();

    // Originally  I used  the tail-recursive  code here  (see comment
    // below); though,  it proved itself not  as a good  idea, as long
    // comments  resulted in  more  that 400  levels  of call  nesting
    // causing xdebug  to blow up. Thus, I've  replacing recusion with
    // the loop here (it is simpler anyway).

    while ($char !== '*') {
      $this->_token_value .= $char;
      $this->_stream->next();
      $char = $this->_stream->peek();
    };

    // Original tail-recursive code:
    //     if (preg_match('/[^*]/i', $char)) { // <!NOT_STAR_SEQ_OR_EMPTY> :: [^*]<!NOT_STAR_SEQ_OR_EMPTY> :: [^*]
    //       $this->_token_value .= $char;
    //       $this->_stream->next();

    //       if (!$this->rule_not_star_seq_or_empty()) { return false; };     
    //     } elseif ($char === '*') { // <!NOT_STAR_SEQ_OR_EMPTY> :: <!EMPTY> :: *
      
    //     };

    return true;
  }

  function rule_star_seq() {
    $char = $this->_stream->peek();

    if ($char === '*') { // <!STAR_SEQ> :: *<STAR_SEQ2> :: *
      $this->_token_value .= $char;
      $this->_stream->next();

      if (!$this->rule_star_seq2()) { return false; };     
    } else {
      return false;
    };

    return true;
  }

  function rule_star_seq2() {
    $char = $this->_stream->peek();

    if ($char === '*') { // <!STAR_SEQ2> :: *<STAR_SEQ2> :: *
      $this->_token_value .= $char;
      $this->_stream->next();

      if (!$this->rule_star_seq2()) { return false; };     
    } else { // <!STAR_SEQ2> :: <!EMPTY> :: [^/*] | /

    };

    return true;
  }

  function rule_w() {
    $char = $this->_stream->peek();

    if (preg_match('/[ \t\r\n\f]/i', $char)) { // <!W> :: [ \t\r\n\f]<!W> :: [ \t\r\n\f]
      $this->_token_value .= $char;
      $this->_stream->next();

      if (!$this->rule_w()) { return false; };     

    } else { 
      return true;
    };

    return true;
  }

  function rule_num_perc_dim_end() {
    $char = $this->_stream->peek();

    if (preg_match('/[-_a-z0-9\\\\]|[^\0-\177]/i', $char)) { // <!NUM_PERC_DIM_END> :: <!IDENT><?TOKEN_DIMENSION> :: - | [_a-z] | [^\0-\177] | \
      if (!$this->rule_ident()) { return false; };

      $this->_token_code = CSS_TOKEN_DIMENSION;

    } elseif ($char === '%') { // <!NUM_PERC_DIM_END> :: %<?TOKEN_PERCENTAGE> :: %
      $this->_token_value .= $char;
      $this->_stream->next();

      $this->_token_code = CSS_TOKEN_PERCENTAGE;

    } else { // <!NUM_PERC_DIM_END> :: <!EMPTY><?TOKEN_NUMBER> :: END/OTHER
      $this->_token_code = CSS_TOKEN_NUMBER;

    };

    return true;    
  }

  function rule_string() {
    $char = $this->_stream->peek();

    if ($char == '"') { // <!STRING> :: <!STRING1_START><!STRING1_END> :: "
      if (!$this->rule_string1_start()) { return false; };
      if (!$this->rule_string1_end()) { return false; };

    } elseif ($char == '\'') { // <!STRING> :: <!STRING2_START><!STRING2_END> :: '
      if (!$this->rule_string2_start()) { return false; };
      if (!$this->rule_string2_end()) { return false; };

    } else {
      return false;
    };

    return true;
  }

  function rule_string1_start() {
    $char = $this->_stream->peek();

    if ($char == '"') { // <!STRING1_START> :: \"<!STRING_CONTENT> :: "
      $this->_token_value .= $char;
      $this->_stream->next();

      if (!$this->rule_string_content()) { return false; };
    } else {
      return false;
    };

    return true;
  }

  function rule_string2_start() {
    $char = $this->_stream->peek();

    if ($char == '\'') { // <!STRING2_START> :: \"<!STRING_CONTENT> :: "
      $this->_token_value .= $char;
      $this->_stream->next();

      if (!$this->rule_string_content()) { return false; };
    } else {
      return false;
    };

    return true;
  }

  function rule_string_content() {
    $char = $this->_stream->peek();

    if (preg_match('/[^\n\r\f\\\\"\']/i', $char)) { // <!STRINT_CONTENT> :: [^\n\r\f\\"]<!STRING_CONTENT> :: [^\n\r\f\\"]
      $this->_token_value .= $char;
      $this->_stream->next();

      if (!$this->rule_string_content()) { return false; };

    } elseif ($char === '\\') { // <!STRINT_CONTENT> :: \<!STRING_ESCAPE_OR_NL><!STRING_CONTENT> :: \
      $this->_token_value .= $char;
      $this->_stream->next();

      if (!$this->rule_string_escape_or_nl()) { return false; };
      $this->rule_string_content();

    } else { // <!STRINT_CONTENT> :: <!EMPTY> :: " | ' | END/OTHER

    };

    return true;
  }

  function rule_string_or_invalid_1_end() {
    $char = $this->_stream->peek();

    if ($char === '"') { // <!STRING_OR_INVALID_1_END> :: <!STRING1_END><?TOKEN_STRING> :: "
      if (!$this->rule_string1_end()) { return false; };

    } else { // <!STRING_OR_INVALID_1_END> :: <!EMPTY><?TOKEN_INVALID> :: END/OTHER
      $this->_token_code = CSS_TOKEN_INVALID;

    };

    return true;
  }

  function rule_string1_end() {
    if ($this->_stream->read_expected('"', $this->_token_value)) { // <!STRING1_END> :: "<?TOKEN_STRING> :: "
      $this->_token_code = CSS_TOKEN_STRING;

    } else {
      return false;
    };

    return true;
  }

  function rule_string_or_invalid_2_end() {
    $char = $this->_stream->peek();

    if ($char === '\'') { // <!STRING_OR_INVALID_2_END> :: <!STRING2_END> :: '
      if (!$this->rule_string2_end()) { return false; };

    } else { // <!STRING_OR_INVALID_2_END> :: <!EMPTY><?TOKEN_INVALID> :: END/OTHER
      $this->_token_code = CSS_TOKEN_INVALID;

    };

    return true;
  }

  function rule_string2_end() {
    if ($this->_stream->read_expected('\'', $this->_token_value)) { // <!STRING2_END> :: '<?TOKEN_STRING> :: '
      $this->_token_code = CSS_TOKEN_STRING;

    } else {
      return false;
    };

    return true;
  }
  
  function rule_string_escape_or_nl() {
    $char = $this->_stream->peek();

    if (preg_match('/[\n\r\f]/i', $char)) { // <!STRING_ESCAPE_OR_NL> :: <!NL> :: \n|\r|\f
      if (!$this->rule_nl()) { return false; };

    } elseif (preg_match('/[^\n\r\f]/i', $char)) { // <!STRING_ESCAPE_OR_NL> :: <!ESCAPE2> :: [^\n\r\f0-9a-f] | [0-9a-f]
      if (!$this->rule_escape2()) { return false; };

    } else { // <!STRING_ESCAPE_OR_NL> :: <!EMPTY> :: ' | " | END/OTHER

    }    

    return true;
  }

  function rule_name() {
    $char = $this->_stream->peek();

    if (preg_match('/[_a-z0-9-]|[^\0-\177]|\\\\/i', $char)) { // <!NAME> :: <!NMCHAR>+ :: [_a-z0-9-] | [^\0-\177] | \
      if (!$this->rule_nmchar_seq()) { return false; };
    } else {
      return false;
    };

    return true;
  }

  function rule_ident() {
    $char = $this->_stream->peek();

    if (preg_match('/[_a-z]|[^\0-\177]|\\\\/i', $char)) { // <!IDENT> :: <!NMSTART><!NMCHAR_SEQ_EMPTY> :: [_a-z] | [^\0-\177] | \
      if (!$this->rule_nmstart()) { return false; };
      if (!$this->rule_nmchar_seq_empty()) { return false; };

    } else {
      return false;
    };

    return true;    
  }

  function rule_nmstart() {
    $char = $this->_stream->peek();

    if (preg_match('/[_a-z]/i', $char)) { // <!NMSTART> :: [_a-z] :: [_a-z]
      $this->_token_value .= $char;
      $this->_stream->next();
    } elseif (preg_match('/[^\0-\177]/i', $char)) { // <!NMSTART> :: <!NONASCII> :: [^\0-\177]
      if (!$this->rule_nonascii()) { return false; };
      
    } elseif ($char === '\\') { // <!NMSTART> :: <!ESCAPE> :: \
      if (!$this->rule_escape()) { return false; };
      
    } else {
      return false;
    };
    
    return true;        
  }

  function rule_nmchar_seq() {
    $char = $this->_stream->peek();

    if (preg_match('/[_a-z0-9-]|[^\0-\177]|\\\\/i', $char)) { // <!NMCHAR_SEQ> :: <!NMCHAR><!NMCHAR_SEQ_EMPTY> :: [_a-z0-9-] | [^\0-\177] | \
      if (!$this->rule_nmchar()) { return false; };
      if (!$this->rule_nmchar_seq_empty()) { return false; };

    } else {
      return false;
    };

    return true;    
  }

  function rule_nmchar_seq_empty() {
    $char = $this->_stream->peek();

    if (preg_match('/[_a-z0-9-]|[^\0-\177]|\\\\/i', $char)) { // <!NMCHAR_SEQ> :: <!NMCHAR><!NMCHAR_SEQ_EMPTY> :: [_a-z0-9-] | [^\0-\177] | \
      if (!$this->rule_nmchar()) { return false; };
      if (!$this->rule_nmchar_seq_empty()) { return false; };

    } else {
      
    };

    return true;    
  }

  function rule_nmchar() {
    $char = $this->_stream->peek();

    if (preg_match('/[_a-z0-9-]/i', $char)) { // <!NMCHAR> :: [_a-z0-9-] :: [_a-z0-9-]
      $this->_token_value .= $char;
      $this->_stream->next();

    } elseif (preg_match('/[^\0-\177]/i', $char)) { // <!NMCHAR> :: <!NONASCII> :: [^\0-\177]
        if (!$this->rule_nonascii()) { return false; };

    } elseif ($char === '\\') { // <!NMCHAR> :: <!ESCAPE> :: \
      if (!$this->rule_escape()) { return false; };
      
    } else {
      return false;
    };
    
    return true;        
  }

  function rule_nonascii() {
    $char = $this->_stream->peek();

    if (preg_match('/[^\0-\177]/i', $char)) { // <!NONASCII> :: [^\0-\177] :: [^\0-\177]
      $this->_token_value .= $char;
      $this->_stream->next();

    } else {
      return false;
    };

    return true;
  }

  function rule_escape() {
    $char = $this->_stream->peek();

    if ($char === '\\') { // <!ESCAPE> :: \<!ESCAPE2> :: \
      $this->_token_value .= $char;
      $this->_stream->next();

      if (!$this->rule_escape2()) { return false; };
    } else {
      return false;
    };

    return true;
  }

  function rule_escape2() {
    $char = $this->_stream->peek();
    
    if (preg_match('/[^\n\r\f0-9a-f]/i', $char)) { // <!ESCAPE2> :: [^\n\r\f0-9a-f] :: [^\n\r\f0-9a-f] 
      $this->_token_value .= $char;
      $this->_stream->next();      

    } else {
      $str = $this->_stream->peek(8);
      if (!preg_match('/^([0-9a-f?]{1,6}(?:\r\n|[ \n\r\t\f])?)/i', $str, $matches)) {
        return false;
      };

      $this->_stream->next(strlen($matches[1]));     
      $this->_token_value .= $matches[1];
    };

    return true;
  }

  function rule_nl() {
    $char = $this->_stream->peek();
    
    if ($char === "\n") { // <!NL> :: \n :: \n
      $this->_token_value .= $char;
      $this->_stream->next();      

    } elseif ($char === "\r") { // <!NL> :: <!NL> :: \r<!NL2> :: \r
      $this->_token_value .= $char;
      $this->_stream->next();      

      if (!$this->rule_nl2()) { return false; };

    } elseif ($char === "\f") { // <!NL> :: \f :: \f
      $this->_token_value .= $char;
      $this->_stream->next();      

    } else {
      return false;
    };

    return true;
  }

  function rule_nl2() {
    $char = $this->_stream->peek();

    if ($char === "\n") { // <!NL2> :: \n :: \n
      $this->_token_value .= $char;
      $this->_stream->next();      

    } else { // <!NL2> :: <!EMPTY> :: [^\n\r\f\\"] | \ | ' | " | END/OTHER

    };

    return true;
  }

  function rule_num() {
    $char = $this->_stream->peek();

    if (preg_match('/[0-9]/i', $char)) { // <!NUM> :: [0-9]<!NUM2> :: [0-9]
      $this->_token_value .= $char;
      $this->_stream->next();
      
      if (!$this->rule_num2()) { return false; };
      
    } elseif ($char === '.') { // <!NUM> :: .[0-9]<!NUM_END> :: .
      $this->_token_value .= $char;
      $this->_stream->next();      

      $char = $this->_stream->peek();
      if (!preg_match('/[0-9]/i', $char)) {
        return false;
      };
    
      $this->_token_value .= $char;
      $this->_stream->next();      

      if (!$this->rule_num_end()) { return false; };

    } else { 
      return false;
    };

    return true;
  }

  function rule_num2() {
    $char = $this->_stream->peek();

    if (preg_match('/[0-9]/i', $char)) { // <!NUM2> :: [0-9]<!NUM2> :: [0-9]
      $this->_token_value .= $char;
      $this->_stream->next();
      
      if (!$this->rule_num2()) { return false; };
      
    } elseif ($char === '.') { // <!NUM2> :: .[0-9]<!NUM_END> :: .
      $this->_token_value .= $char;
      $this->_stream->next();      

      $char = $this->_stream->peek();
      if (!preg_match('/[0-9]/i', $char)) {
        return false;
      };
    
      $this->_token_value .= $char;
      $this->_stream->next();      

      if (!$this->rule_num_end()) { return false; };

    } else { // <!NUM2> :: <!EMPTY> :: - | [_a-z] | [^\0-\177] | \ | % | END\OTHER
      
    };

    return true;
  }

  function rule_num_end() {
    $char = $this->_stream->peek();

    if (preg_match('/[0-9]/i', $char)) { // <!NUM_END> :: [0-9]<!NUM_END> :: [0-9]
      $this->_token_value .= $char;
      $this->_stream->next();
      
      if (!$this->rule_num_end()) { return false; };

    } else { // <!NUM_END> :: <!EMPTY> :: - | [_a-z] | [^\0-\177] | \ | % | END\OTHER
      
    };

    return true;
  }
}

?>