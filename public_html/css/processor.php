<?php

require_once(HTML2PS_DIR.'css/interface.processor.php');

class CSSProcessor extends ICSSProcessor {
  var $_pipeline;
  var $_order;
  var $_base_url;

  function CSSProcessor() {
    $this->_pipeline = null;
    $this->_order = 0;
    $this->_base_url = array();
  }

  function push_base_url($url) {
    array_unshift($this->_base_url, $url);
  }

  function pop_base_url() {
    array_shift($this->_base_url);
  }

  function peek_base_url() {
    return $this->_base_url[0];
  }

  function make_next_order() {
    $this->_order++;
    return $this->_order;
  }

  function get_pipeline() {
    return $this->_pipeline;
  }

  function set_pipeline(&$pipeline) {
    $this->_pipeline =& $pipeline;
  }

  /**
   * Traverse DOM tree recursively starting from the $node.
   * 'style' and 'link' nodes are handled separately.
   */
  function &scan_node(&$node, &$ruleset) {
    // Handle 'style' and 'link' tags specifically
    if ($node->node_type() == XML_ELEMENT_NODE) {
      $tagname = strtolower($node->tagname());

      switch ($tagname) {
      case 'style':
        $ruleset->merge($this->process_style_node($node));
        break;
      case 'link':
        $ruleset->merge($this->process_link_node($node));
        break;
      };
    };

    // Scan all child nodes of "normal" and document nodes
    if (in_array($node->node_type(),
                 array(XML_ELEMENT_NODE, 
                       XML_DOCUMENT_NODE))) {
      $child = $node->first_child();
      while ($child) {
        $this->scan_node($child, $ruleset);
        $child = $child->next_sibling();
      };
    };    

    return $css_ruleset;
  }

  function process_link_node(&$node) {
    $ruleset =& new CSSRuleset();

    $rel = strtolower($node->get_attribute('rel'));
    $type = strtolower($node->get_attribute('type'));

    // See HTML 4.01 p.14.2.3: This attribute (read: 'media') specifies the intended destination medium for style information. 
    // It may be a single media descriptor or a comma-separated list. The default value for this attribute is "screen".
    if ($node->has_attribute('media')) {
      $media = explode(',', strtolower($node->get_attribute('media')));
    } else {
      $media = array('screen');
    };

    // Subconditions for the large condition below
    // 1. This is a stylesheet
    $is_stylesheet = ($rel == 'stylesheet');

    // 2. This is a CSS stylesheet
    // Though HTML 4.01 standard says in p.14.2.3 "Authors must supply a value for this attribute; there is no default value for this attribute",
    // a lot of coders do not obey the standard; let's assume that if 'type' is omitted, we're dealing with CSS
    $is_css = ($type == 'text/css' || $type == ''); 

    // 3. This stylesheet should be processed with current media
    // HTML 4.01 p.14.2.3 "This attribute specifies the intended destination medium for style information. 
    // It may be a single media descriptor or a comma-separated list. The default value for this attribute is "screen".
    // Yet again, let's be tolerant: if coder did not specify anything in the 'media' attribute value, 
    // we assume that this stylesheet should be processed. 
    // Note that if 'media' is omitted, we're using its default value - 'screent' (see above)
    $is_media_to_be_processed = (count($media) == 0 || $this->is_allowed_media($media));

    if ($is_stylesheet && 
        $is_css &&
        $is_media_to_be_processed) {
      // Attempt to escape URL automaticaly
      $url_autofix = new AutofixUrl();
      $src = $url_autofix->apply(trim($node->get_attribute('href')));

      if ($src) {
        $ruleset = $this->import($src);
      };
    };
    
    return $ruleset;
  }

  function &process_style_node(&$node) {
    $pipeline =& $this->get_pipeline();
    $ruleset = $this->import_source($node->get_content(), 
                                    $pipeline->get_base_url());
    return $ruleset;
  }

  function import($url) {
    // Import the referenced file
    $pipeline =& $this->get_pipeline();
    $data =& $pipeline->fetch($url);
    
    if (!is_null($data)) {
      return $this->import_source($data->content, $url);
    };

    return new CSSRuleset();
  }

  function import_source($css, $url) {
    $this->push_base_url($url);

    $ruleset = new CSSRuleset();

    $stream = new CSSStreamString($css);
    $lexer = new CSSLexer($stream);
    $parser = new CSSParser($lexer);
    $result = $parser->parse();  
    
    // Log CSS parse errors
    $errors = $parser->get_errors();
    foreach ($errors as $error) {
      $error->log($url);
    };

    if (!$result) {
      error_log(sprintf('Unrecoverable syntax error while parsing stylesheet at "%s"',
                        $url));
    };

    $stylesheet = $parser->get_context();

    // Process @import
    $imports = $stylesheet->get_imports();

    foreach ($imports as $import) {
      $media_list = $import->get_media();
      if (empty($media_list) ||
          in_array('all', $media_list) ||
          $this->is_allowed_media($import->get_media())) {
        $pipeline = $this->get_pipeline();
        $full_url = $pipeline->guess_url(css_remove_value_quotes($import->get_url()));

        $ruleset->merge($this->import($full_url));
      };
    };

    // Process @media 
    $media = $stylesheet->get_media();
    foreach ($media as $media_item) {
      if ($this->is_allowed_media($media_item->get_media())) {
        $ruleset->merge($this->import_rulesets($media_item->get_rulesets()));
      };
    }

    // Process usual rules
    $ruleset->merge($this->import_rulesets($stylesheet->get_rulesets()));

    $this->pop_base_url();
    return $ruleset;
  }

  function import_source_ruleset($css, $url) {
    $this->push_base_url($url);

    $property_collection = new CSSPropertyCollection();

    $stream = new CSSStreamString(sprintf('* { %s }', $css));
    $lexer = new CSSLexer($stream);
    $parser = new CSSParser($lexer);
    $result = $parser->parse_ruleset();  

    if (!$result) {
      error_log(sprintf('Unrecoverable syntax error while parsing stylesheet at "%s"',
                        $url));
    };

    $ruleset = $parser->get_context();
    return $this->import_ruleset_body($ruleset);
  }

  function import_rulesets($syntax_rulesets_collection) {
    $ruleset = new CSSRuleset();

    $syntax_rulesets = $syntax_rulesets_collection->get();
    foreach ($syntax_rulesets as $syntax_ruleset) {
      $selectors = $this->import_ruleset_selectors($syntax_ruleset);
      $body = $this->import_ruleset_body($syntax_ruleset);

      foreach ($selectors as $selector) {
        $rule =& new CSSRule($selector,
                             $body,
                             $this->peek_base_url(),
                             $this->make_next_order());
      };

      $ruleset->add_rule($rule);
    };

    return $ruleset;
  }

  function import_ruleset_selector($syntax_selector) {
    $selector = array();

    $syntax_selectors_simple = $syntax_selector->get_selectors();
    $syntax_combinators = $syntax_selector->get_combinators();

    $selector = $this->import_ruleset_selector_simple($syntax_selectors_simple[0]);

    for ($i = 0, $size = count($syntax_selectors_simple); $i < $size-1; $i++) {
      $syntax_selector_simple = $syntax_selectors_simple[$i+1];
      $syntax_combinator = $syntax_combinators[$i];

      switch ($syntax_combinator->get_type()) {
      case COMBINATOR_PLUS:
        $selector = array(SELECTOR_SEQUENCE,
                          array($this->import_ruleset_selector_simple($syntax_selector_simple),
                                array(SELECTOR_SIBLING,
                                      $selector)));
        break;
      case COMBINATOR_GREATER:
        $selector = array(SELECTOR_SEQUENCE,
                          array($this->import_ruleset_selector_simple($syntax_selector_simple),
                                array(SELECTOR_DIRECT_PARENT,
                                      $selector)));
        break;
      case COMBINATOR_EMPTY:
        $selector = array(SELECTOR_SEQUENCE,
                          array($this->import_ruleset_selector_simple($syntax_selector_simple),
                                array(SELECTOR_PARENT,
                                      $selector)));
        break;
      };
    };

    return $selector;
  }

  function import_ruleset_selector_simple($syntax_selector) {
    $selector = array(SELECTOR_ANY);
    
    if (!is_null($syntax_selector->get_element())) {
      $selector = array(SELECTOR_SEQUENCE,
                        array(array(SELECTOR_TAG, $syntax_selector->get_element()),
                              $selector));
    };
    
    $ids = $syntax_selector->get_ids();
    foreach ($ids as $id) {
      $selector = array(SELECTOR_SEQUENCE,
                        array(array(SELECTOR_ID, substr($id, 1)), // Strip # from the beginning of the id string
                              $selector));
    };

    $classes = $syntax_selector->get_classes();
    foreach ($classes as $class) {
      $selector = array(SELECTOR_SEQUENCE,
                        array(array(SELECTOR_CLASS, $class),
                              $selector));
    };

    $attribs = $syntax_selector->get_attribs();
    foreach ($attribs as $attrib) {
      switch ($attrib->get_op()) {
      case ATTRIB_OP_EQUAL:
        $selector = array(SELECTOR_SEQUENCE,
                          array(array(SELECTOR_ATTR_VALUE, 
                                      $attrib->get_name(), 
                                      css_remove_value_quotes($attrib->get_value())),
                                $selector));
        break;
      case ATTRIB_OP_DASHMATCH:
        $selector = array(SELECTOR_SEQUENCE,
                          array(array(SELECTOR_ATTR_VALUE_WORD_HYPHEN, 
                                      $attrib->get_name(), 
                                      css_remove_value_quotes($attrib->get_value())),
                                $selector));
        break;
      case ATTRIB_OP_INCLUDES:
        $selector = array(SELECTOR_SEQUENCE,
                          array(array(SELECTOR_ATTR_VALUE_WORD, 
                                      $attrib->get_name(), 
                                      css_remove_value_quotes($attrib->get_value())),
                                $selector));
        break;
      default:
        $selector = array(SELECTOR_SEQUENCE,
                          array(array(SELECTOR_ATTR, 
                                      $attrib->get_name()),
                                $selector));
        break;
      };
    };

    $pseudo = $syntax_selector->get_pseudo();
    foreach ($pseudo as $pseudo_item) {
      switch ($pseudo_item->get_name()) {
      case 'link':
        $selector = array(SELECTOR_SEQUENCE,
                          array(array(SELECTOR_PSEUDOCLASS_LINK),
                                $selector));
        break;
      case 'before':
        $selector = array(SELECTOR_SEQUENCE,
                          array(array(SELECTOR_PSEUDOELEMENT_BEFORE),
                                $selector));
        break;
      case 'after':
        $selector = array(SELECTOR_SEQUENCE,
                          array(array(SELECTOR_PSEUDOELEMENT_AFTER),
                                $selector));
        break;
      case 'visited':
      case 'active':
      case 'hover':
        // No nothing, as html2ps is not interactive user-agent
        break;
      default:
        error_log(sprintf('Unknown pseudo selector: "%s"', $pseudo_item->get_name()));
      };
    };

    return $selector;
  }

  function import_ruleset_selectors($syntax_ruleset) {
    $selectors = array();

    $syntax_selectors_collection = $syntax_ruleset->get_selectors();
    $syntax_selectors = $syntax_selectors_collection->get();
    foreach ($syntax_selectors as $syntax_selector) {
      $selectors[] = $this->import_ruleset_selector($syntax_selector);
    };

    return $selectors;
  }

  function import_ruleset_body($syntax_ruleset) {
    $body = new CSSPropertyCollection();

    $declarations_obj = $syntax_ruleset->get_declarations();
    $declarations = $declarations_obj->get();
    foreach ($declarations as $declaration) {
      $property = $this->import_declaration($declaration);
      if ($property) {
        $body->add_property($property);
      };
    };

    return $body;
  }

  function import_declaration($declaration) {
    $syntax_property = $declaration->get_property();
    $code = CSS::name2code($syntax_property->get_name());
    if (is_null($code)) {
      return null;
    };

    $handler = CSS::get_handler($code);

    $property = new CSSPropertyDeclaration();
    $property->set_code($code);
    $expr = $declaration->get_expr();

    $property->set_value($handler->parse($expr->to_string(), 
                                         $this->get_pipeline(),
                                         $expr));
    $property->set_important($declaration->get_important());
    return $property;
  }

  function is_allowed_media($media_list) {
    // Now we've got the list of media this style can be applied to;
    // check if at least one of this media types is being used by the script
    //
    $allowed_media = config_get_allowed_media();
    $allowed_found = false;

    // Note that media names should be case-insensitive;
    // it is not guaranteed that $media_list contains lower-case variants,
    // as well as it is not guaranteed that configuration data contains them.
    // Thus, media name lists should be explicitly converted to lowercase
    $media_list = array_map('strtolower', $media_list);
    $allowed_media = array_map('strtolower', $allowed_media);

    foreach ($media_list as $media) {
      $allowed_found |= (array_search($media, $allowed_media) !== false);
    };
  
    return $allowed_found;
  }
}

?>