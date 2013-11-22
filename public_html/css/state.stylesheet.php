<?php

class CSSParserStateStylesheet extends ParserState {
  function CSSParserStateStylesheet() {
    $this->ParserState();

    $this->register_handler(CSS_TOKEN_CDO, array(&$this, 'on_css_token_cdo'));
    $this->register_handler(CSS_TOKEN_CDC, array(&$this, 'on_css_token_cdc'));
    $this->register_handler(CSS_TOKEN_S, array(&$this, 'on_css_token_s'));
  }
}

?>