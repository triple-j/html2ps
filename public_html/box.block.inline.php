<?php
// $Header: /cvsroot/html2ps/box.block.inline.php,v 1.21 2007/04/07 11:16:33 Konstantin Exp $

/**
 * @package HTML2PS
 * @subpackage Document
 *
 * Describes document elements with 'display: inline-block'.
 *
 * @link http://www.w3.org/TR/CSS21/visuren.html#value-def-inline-block CSS 2.1 description of 'display: inline-block'
 */
class InlineBlockBox extends GenericInlineBox {
  /**
   * Create new 'inline-block' element; add content from the parsed HTML tree automatically.
   *
   * @see InlineBlockBox::InlineBlockBox()
   * @see GenericContainerBox::create_content()
   */
  function &create(&$root, &$pipeline) {
    $box = new InlineBlockBox();
    $box->readCSS($pipeline->get_current_css_state());
    $box->create_content($root, $pipeline);
    return $box;
  }

  function InlineBlockBox() {
    $this->GenericInlineBox();
  }


  /**
   * Layout current 'inline-block' element assument it has 'position: static'
   *
   * @param GenericContainerBox $parent The document element which should
   * be treated as the parent of current element
   *
   * @param FlowContext $context The flow context containing the additional layout data
   *
   * @see FlowContext
   * @see GenericContainerBox
   *
   * @todo re-check this layout routine; it seems that 'inline-block' boxes have
   * their width calculated incorrectly
   */
  function reflow_static(&$parent, &$context) {
    GenericFormattedBox::reflow($parent, $context);

    // Check if we need a line break here
    $this->maybe_line_break($parent, $context);

    /**
     * Calculate margin values if they have been set as a percentage
     */
    $this->_calc_percentage_margins($parent);
    $this->_calc_percentage_padding($parent);

    /**
     * Calculate width value if it had been set as a percentage
     */
    $this->_calc_percentage_width($parent, $context);

    /**
     * Calculate 'auto' values of width and margins
     */
    $this->_calc_auto_width_margins($parent);

    /**
     * add current box to the parent's line-box (alone)
     */
    $parent->append_line($this);

    /**
     * Calculate position of the upper-left corner of the current box
     */
    $this->guess_corner($parent);

    /**
     * By default, child block box will fill all available parent width;
     * note that actual content width will be smaller because of non-zero padding, border and margins
     */
    $this->put_full_width($parent->get_width());

    /**
     * Layout element's children
     */
    $this->reflow_content($context);

    /**
     * Calculate element's baseline, as it should be aligned inside the
     * parent's line box vertically
     */
    $font = $this->get_css_property(CSS_FONT);
    $this->default_baseline = $this->get_height() + $font->size->getPoints();

    /**
     * Extend parent's height to fit current box
     */
    $parent->extend_height($this->get_bottom_margin());

    /**
     * Offset current x coordinate of parent box
     */
    $parent->_current_x = $this->get_right_margin();
  }
}
?>
