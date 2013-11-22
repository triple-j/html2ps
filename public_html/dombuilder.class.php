<?php

class DOMBuilder {
  function &build(&$dom_tree, &$pipeline) {
    $body =& traverse_dom_tree_pdf($dom_tree);
    $box =& create_pdf_box($body, $pipeline);   
    return $box;
  }
}

?>
