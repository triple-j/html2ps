<?php

class CSSRuleset {
  var $rules;
  var $tag_filtered;

  function CSSRuleset() {
    $this->rules = array();
    $this->tag_filtered = array();
  }
  
  function add_rule(&$rule) {
    $this->rules[] =& $rule;

    $tag = $this->detect_applicable_tag($rule->get_selector());
    if (is_null($tag)) { 
      $tag = '*'; 
    }
    $this->tag_filtered[$tag][] = $rule;
  }

  function apply(&$root, &$state, &$pipeline) {
    $local_css = array();

    if (isset($this->tag_filtered[strtolower($root->tagname())])) {
      $local_css = $this->tag_filtered[strtolower($root->tagname())];
    };

    if (isset($this->tag_filtered['*'])) {
      $local_css = array_merge($local_css, $this->tag_filtered['*']);
    };

    $applicable = array();

    foreach ($local_css as $rule) {
      if ($rule->match($root)) {
        $applicable[] = $rule;
      };
    };

    usort($applicable, 'cmp_rule_objs');

    foreach ($applicable as $rule) {
      switch ($rule->get_pseudoelement()) {
      case SELECTOR_PSEUDOELEMENT_BEFORE:
        $handler =& CSS::get_handler(CSS_HTML2PS_PSEUDOELEMENTS);
        $handler->replace($handler->get($state->getState()) | CSS_HTML2PS_PSEUDOELEMENTS_BEFORE, $state);
        break;
      case SELECTOR_PSEUDOELEMENT_AFTER:
        $handler =& CSS::get_handler(CSS_HTML2PS_PSEUDOELEMENTS);
        $handler->replace($handler->get($state->getState()) | CSS_HTML2PS_PSEUDOELEMENTS_AFTER, $state);
        break;
      default:
        $rule->apply($root, $state, $pipeline);
        break;
      };
    };
  }

  function apply_pseudoelement($element_type, &$root, &$state, &$pipeline) {
    $local_css = array();

    if (isset($this->tag_filtered[strtolower($root->tagname())])) {
      $local_css = $this->tag_filtered[strtolower($root->tagname())];
    };

    if (isset($this->tag_filtered['*'])) {
      $local_css = array_merge($local_css, $this->tag_filtered['*']);
    };

    $applicable = array();

    for ($i=0; $i<count($local_css); $i++) {
      $rule =& $local_css[$i];
      if ($rule->get_pseudoelement() == $element_type) {
        if ($rule->match($root)) {
          $applicable[] =& $rule;
        };
      };
    };

    usort($applicable, 'cmp_rule_objs');

    // Note that filtered rules already have pseudoelement mathing (see condition above)

    foreach ($applicable as $rule) {
      $rule->apply($root, $state, $pipeline);
    };
  }
  
  // Check if only tag with a specific name can match this selector
  //
  function detect_applicable_tag($selector) {
    switch (selector_get_type($selector)) {
    case SELECTOR_TAG:
      return $selector[1];
    case SELECTOR_SEQUENCE:
      foreach ($selector[1] as $subselector) {
        $tag = $this->detect_applicable_tag($subselector);
        if ($tag) { return $tag; };
      };
      return null;
    default: 
      return null;
    }
  }

  function merge(&$ruleset) {
    for ($i = 0, $size = count($ruleset->rules); $i < $size; $i++) {
      $rule =& $ruleset->rules[$i];
      $this->add_rule($rule);
    };
  }
}

?>