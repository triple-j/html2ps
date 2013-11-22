<?php

require_once(HTML2PS_DIR.'css.constants.inc.php');

class CSSPageSelector {
  var $_type;

  function CSSPageSelector($type) {
    $this->set_type($type);
  }

  function get_type() {
    return $this->_type;
  }

  function set_type($type) {
    $this->_type = $type;
  }
}

class CSSPageSelectorAll extends CSSPageSelector {
  function CSSPageSelectorAll() {
    $this->CSSPageSelector(CSS_PAGE_SELECTOR_ALL);
  }
}

class CSSPageSelectorNamed extends CSSPageSelector  {
  var $_name;

  function CSSPageSelectorNamed($name) {
    $this->CSSPageSelector(CSS_PAGE_SELECTOR_NAMED);
    $this->set_name($name);
  }

  function get_name() {
    return $this->_name;
  }

  function set_name($name) {
    $this->_name = $name;
  }
}

class CSSPageSelectorFirst extends CSSPageSelector {
  function CSSPageSelectorFirst() {
    $this->CSSPageSelector(CSS_PAGE_SELECTOR_FIRST);
  }
}

class CSSPageSelectorLeft extends CSSPageSelector {
  function CSSPageSelectorLeft() {
    $this->CSSPageSelector(CSS_PAGE_SELECTOR_LEFT);
  }
}

class CSSPageSelectorRight extends CSSPageSelector {
  function CSSPageSelectorRight() {
    $this->CSSPageSelector(CSS_PAGE_SELECTOR_RIGHT);
  }
}

class CSSAtRulePage {
  var $selector;
  var $margin_boxes;
  var $css;

  function CSSAtRulePage($selector, &$pipeline) {
    $this->selector = $selector;
    $this->margin_boxes = array();

    $this->css =& new CSSPropertyCollection();
  }

  function &getSelector() {
    return $this->selector;
  }

  function getAtRuleMarginBoxes() {
    return $this->margin_boxes;
  }

  /**
   * Note that only one margin box rule could be added; subsequent adds 
   * will overwrite existing data
   */
  function addAtRuleMarginBox($rule) {
    $this->margin_boxes[$rule->getSelector()] = $rule;
  }

  function setCSSProperty($property) {
    $this->css->add_property($property);
  }
}

class CSSAtRuleMarginBox {
  var $selector;
  var $css;

  /**
   * TODO: CSS_TEXT_ALIGN should get  top/bottom values by default for
   * left-top, left-bottom, right-top and right-bottom boxes
   */
  function CSSAtRuleMarginBox($selector, &$pipeline) {
    $this->selector = $selector;

    $css = "-html2ps-html-content: ''; content: ''; width: auto; height: auto; margin: 0; border: none; padding: 0; font: auto;";
    $css = $css . $this->_getCSSDefaults($selector);

    $css_processor =& new CSSProcessor(); 
    $css_processor->set_pipeline($pipeline);
    $property_collection = $css_processor->import_source_ruleset($css, 
                                                                 $pipeline->get_base_url());

    $this->css = new CSSRule(array(SELECTOR_ANY),
                             $property_collection,
                             '',
                             null);
  }

  function getSelector() {
    return $this->selector;
  }

  function _getCSSDefaults($selector) {
    $text_align_handler =& CSS::get_handler(CSS_TEXT_ALIGN);
    $vertical_align_handler =& CSS::get_handler(CSS_VERTICAL_ALIGN);
    
    switch ($selector) {
    case CSS_MARGIN_BOX_SELECTOR_TOP:
      return 'text-align: left; vertical-align: middle';
    case CSS_MARGIN_BOX_SELECTOR_TOP_LEFT_CORNER:
      return 'text-align: right; vertical-align: middle';
    case CSS_MARGIN_BOX_SELECTOR_TOP_LEFT:
      return 'text-align: left; vertical-align: middle';
    case CSS_MARGIN_BOX_SELECTOR_TOP_CENTER:
      return 'text-align: center; vertical-align: middle';
    case CSS_MARGIN_BOX_SELECTOR_TOP_RIGHT:
      return 'text-align: right; vertical-align: middle';
    case CSS_MARGIN_BOX_SELECTOR_TOP_RIGHT_CORNER:
      return 'text-align: left; vertical-align: middle';
    case CSS_MARGIN_BOX_SELECTOR_BOTTOM:
      return 'text-align: left; vertical-align: middle';
    case CSS_MARGIN_BOX_SELECTOR_BOTTOM_LEFT_CORNER:
      return 'text-align: right; vertical-align: middle';
    case CSS_MARGIN_BOX_SELECTOR_BOTTOM_LEFT:
      return 'text-align: left; vertical-align: middle';
    case CSS_MARGIN_BOX_SELECTOR_BOTTOM_CENTER:
      return 'text-align: center; vertical-align: middle';
    case CSS_MARGIN_BOX_SELECTOR_BOTTOM_RIGHT:
      return 'text-align: right; vertical-align: middle';
    case CSS_MARGIN_BOX_SELECTOR_BOTTOM_RIGHT_CORNER:
      return 'text-align: left; vertical-align: middle';
    case CSS_MARGIN_BOX_SELECTOR_LEFT_TOP:
      return 'text-align: center; vertical-align: top';
    case CSS_MARGIN_BOX_SELECTOR_LEFT_MIDDLE:
      return 'text-align: center; vertical-align: middle';
    case CSS_MARGIN_BOX_SELECTOR_LEFT_BOTTOM:
      return 'text-align: center; vertical-align: bottom';
    case CSS_MARGIN_BOX_SELECTOR_RIGHT_TOP:
      return 'text-align: center; vertical-align: top';
    case CSS_MARGIN_BOX_SELECTOR_RIGHT_MIDDLE:
      return 'text-align: center; vertical-align: middle';
    case CSS_MARGIN_BOX_SELECTOR_RIGHT_BOTTOM:
      return 'text-align: center; vertical-align: bottom';
    };
  }

  function setCSSProperty($property) {
    $this->css->add_property($property);
  }

  function &get_css_property($code) {
    return $this->css->get_property($code);
  }
}

?>