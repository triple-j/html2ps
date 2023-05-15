<?php
// $Header: /cvsroot/html2ps/css.white-space.inc.php,v 1.8 2006/12/24 14:42:44 Konstantin Exp $

define('TABLE_LAYOUT_AUTO',   1);
define('TABLE_LAYOUT_FIXED',  2);

class CSSTableLayout extends CSSPropertyStringSet {
  function __construct() {
    CSSPropertyStringSet::__construct(false,
                                false,
                                array('auto'  => TABLE_LAYOUT_AUTO,
                                      'fixed' => TABLE_LAYOUT_FIXED)); 
  }

  function default_value() { 
    return TABLE_LAYOUT_AUTO; 
  }

  function getPropertyCode() {
    return CSS_TABLE_LAYOUT;
  }

  function getPropertyName() {
    return 'table-layout';
  }
}

(new CSS())->register_css_property(new CSSTableLayout());
  
?>