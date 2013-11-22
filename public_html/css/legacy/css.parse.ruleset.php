<?php

function parse_style_node($root, &$pipeline) {
    // Check if this style node have 'media' attribute
    // and if we're using this media;
    //
    // Note that, according to the HTML 4.01 p.14.2.3
    // This attribute specifies the intended destination medium for style information.
    // It may be a single media descriptor or a comma-separated list.
    // The default value for this attribute is "screen".
    //
    $media_list = array("screen");
    if ($root->has_attribute("media")) {
      // Note that there may be whitespace symbols around commas, so we should not just use 'explode' function
      $media_list = preg_split("/\s*,\s*/",trim($root->get_attribute("media")));
    };

    if (!is_allowed_media($media_list)) {
      if (defined('DEBUG_MODE')) {
        error_log(sprintf('No allowed (%s) media types found in CSS stylesheet media types (%s). Stylesheet ignored.',
                          join(',', config_get_allowed_media()),
                          join(',', $media_list)));
      };
      return;
    };

    if (!isset($GLOBALS['g_stylesheet_title']) ||
        $GLOBALS['g_stylesheet_title'] === "") {
      $GLOBALS['g_stylesheet_title'] = $root->get_attribute("title");
    };

    if (!$root->has_attribute("title") || $root->get_attribute("title") === $GLOBALS['g_stylesheet_title']) {
      /**
       * Check if current node is empty (then, we don't need to parse its contents)
       */
      $content = trim($root->get_content());
      if ($content != "") {
        $this->parse_css($content, $pipeline);
      };
    };
  }

  function parse_css($css, &$pipeline, $baseindex = 0) {
    $allowed_media = implode("|",config_get_allowed_media());

    // remove the UTF8 byte-order mark from the beginning of the file (several high-order symbols at the beginning)
    $pos = 0;
    $len = strlen($css);
    while (ord($css{$pos}) > 127 && $pos < $len) { $pos ++; };
    $css = substr($css, $pos);

    // Process @media rules;
    // basic syntax is:
    // @media <media>(,<media>)* { <rules> }
    //

    while (preg_match("/^(.*?)@media([^{]+){(.*)$/s",$css,$matches)) {
      $head  = $matches[1];
      $media = $matches[2];
      $rest  = $matches[3];

      // Process CSS rules placed before the first @media declaration - they should be applied to
      // all media types
      //
      $this->parse_css_media($head, $pipeline, $baseindex);

      // Extract the media content
      if (!preg_match("/^((?:[^{}]*{[^{}]*})*)[^{}]*\s*}(.*)$/s", $rest, $matches)) {
        die("CSS media syntax error\n");
      } else {
        $content = $matches[1];
        $tail    = $matches[2];
      };

      // Check if this media is to be processed
      if (preg_match("/".$allowed_media."/i", $media)) {
        $this->parse_css_media($content, $pipeline, $baseindex);
      };

      // Process the rest of CSS file
      $css = $tail;
    };

    // The rest of CSS file belogs to common media, process it too
    $this->parse_css_media($css, $pipeline, $baseindex);
  }

  function css_import($src, &$pipeline) {
    // Update the base url;
    // all urls will be resolved relatively to the current stylesheet url
    $url = $pipeline->guess_url($src);
    $data = $pipeline->fetch($url);

    /**
     * If referred file could not be fetched return immediately
     */
    if (is_null($data)) { return; };

    $css = $data->get_content();
    if (!empty($css)) {
      /**
       * Sometimes, external stylesheets contain <!-- and --> at the beginning and
       * at the end; we should remove these characters, as they may break parsing of
       * first and last rules
       */
      $css = preg_replace('/^\s*<!--/', '', $css);
      $css = preg_replace('/-->\s*$/', '', $css);

      $this->parse_css($css, $pipeline);
    };

    $pipeline->pop_base_url();
  }

  function parse_css_import($import, &$pipeline) {
    if (preg_match("/@import\s+[\"'](.*)[\"'];/",$import, $matches)) {
      // @import "<url>"
      $this->css_import(trim($matches[1]), $pipeline);
    } elseif (preg_match("/@import\s+url\((.*)\);/",$import, $matches)) {
      // @import url()
      $this->css_import(trim(css_remove_value_quotes($matches[1])), $pipeline);
    } elseif (preg_match("/@import\s+(.*);/",$import, $matches)) {
      // @import <url>
      $this->css_import(trim(css_remove_value_quotes($matches[1])), $pipeline);
    };
  }

  function parse_css_media($css, &$pipeline, $baseindex = 0) {
    // Remove comments
    $css = preg_replace("#/\*.*?\*/#is","",$css);

    // Extract @page rules
    $css = parse_css_atpage_rules($css, $pipeline);

    // Extract @import rules
    if ($num = preg_match_all("/@import[^;]+;/",$css, $matches, PREG_PATTERN_ORDER)) {
      for ($i=0; $i<$num; $i++) {
        $this->parse_css_import($matches[0][$i], $pipeline);
      }
    };

    // Remove @import rules so they will not break further processing
    $css = preg_replace("/@import[^;]+;/","", $css);

    while (preg_match("/([^{}]*){(.*?)}(.*)/is", $css, $matches)) {
      // Drop extracted part
      $css = $matches[3];

      // Save extracted part
      $raw_selectors  = $matches[1];
      $raw_properties = $matches[2];

      $selectors  = parse_css_selectors($raw_selectors);

      $properties = parse_css_properties($raw_properties, $pipeline);

      foreach ($selectors as $selector) {
        $this->_lastId ++;
        $rule = array($selector,
                      $properties,
                      $pipeline->get_base_url(),
                      $this->_lastId + $baseindex);
        $this->add_rule($rule,
                        $pipeline);
      };
    };
  }
?>