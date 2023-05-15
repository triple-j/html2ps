<?php

require_once(HTML2PS_DIR.'box.note-call.class.php');

/**
 * Support for CSS 3 position: footnote.
 *
 * Scans for elements having position: footnote and replaces them with
 * BoxNoteCall object (which contains reference to original data and
 * handles footnote rendering)
 */
class PreTreeFilterFootnotes extends PreTreeFilter {
  function process(&$tree, $data, &$pipeline) {
    if (is_a($tree, 'GenericContainerBox')) {
      $size = is_countable($tree->content) ? count((array) $tree->content) : 0;
      for ($i=0; $i<$size; $i++) {
        /**
         * No need to check this conition for text boxes, as they do not correspond to 
         * HTML elements 
         */
        if (!is_a($tree->content[$i], "TextBox")) {
          if ($tree->content[$i]->getCSSProperty(CSS_POSITION) == POSITION_FOOTNOTE) {
            $tree->content[$i]->setCSSProperty(CSS_POSITION, POSITION_STATIC);
            
            $note_call =& BoxNoteCall::create($tree->content[$i], $pipeline);
            $tree->content[$i] =& $note_call;
            
            $pipeline->_addFootnote($note_call);
          } else {
            $this->process($tree->content[$i], $data, $pipeline);
          }
        }
      }
    }

    return true;
  }
}
?>