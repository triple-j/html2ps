<?php

class PostTreeFilterPositioned extends PreTreeFilter {
  var $_context;

  function __construct(&$context) {
    $this->_context =& $context;
  }

  function process(&$tree, $data, &$pipeline) {
    if (is_a($tree, 'GenericContainerBox')) {
      $size = is_countable($tree->content) ? count((array) $tree->content) : 0;
      for ($i=0; $i<$size; $i++) {
        $position = $tree->content[$i]->getCSSProperty(CSS_POSITION);
        $float    = $tree->content[$i]->getCSSProperty(CSS_FLOAT);

        if ($position == POSITION_ABSOLUTE) {
          $this->_context->add_absolute_positioned($tree->content[$i]);
        } elseif ($position == POSITION_FIXED) {
          $this->_context->add_fixed_positioned($tree->content[$i]);
        }

        $this->process($tree->content[$i], $data, $pipeline);
      }
    }

    return true;
  }
}
?>