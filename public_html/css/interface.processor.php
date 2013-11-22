<?php

require_once(HTML2PS_DIR.'css/interface.processor.php');

class ICSSProcessor {
  function &scan_node(&$node) { die('ICSSProcessor::scan_node - implement me!'); }
  function process_link_node(&$node) { die('ICSSProcessor::process_link_node - implement me!'); }
  function process_style_node(&$node) { die('ICSSProcessor::process_style_node - implement me!'); }
}

?>