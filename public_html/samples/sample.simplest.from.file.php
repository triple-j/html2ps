<?php

require_once('../config.inc.php');
require_once(HTML2PS_DIR.'pipeline.factory.class.php');

@set_time_limit(10000);
parse_config_file(HTML2PS_DIR.'html2ps.config');

/**
 * Handles the saving generated PDF to user-defined output file on server
 */
class MyDestinationFile extends Destination {
  /**
   * @var String result file name / path
   * @access private
   */
  var $_dest_filename;

  function __construct($dest_filename) {
    $this->_dest_filename = $dest_filename;
  }

  function process($tmp_filename, $content_type) {
    copy($tmp_filename, $this->_dest_filename);
  }
}

class MyFetcherLocalFile extends Fetcher {
  var $_content;

  function __construct($file) {
    $this->_content = file_get_contents($file);
  }

  function get_data($dummy1) {
    return new FetchedDataURL($this->_content, array(), "");
  }

  function get_base_url() {
    return "";
  }
}

/**
 * Runs the HTML->PDF conversion with default settings
 *
 * Warning: if you have any files (like CSS stylesheets and/or images referenced by this file,
 * use absolute links (like http://my.host/image.gif).
 *
 * @param $path_to_html String path to source html file.
 * @param $path_to_pdf  String path to file to save generated PDF to.
 */
function convert_to_pdf($path_to_html, $path_to_pdf) {
  $pipeline =  (new PipelineFactory())->create_default_pipeline("", // Attempt to auto-detect encoding
                                                       "");
  // Override HTML source 
  $pipeline->fetchers[] = new MyFetcherLocalFile($path_to_html);

  $filter = new PreTreeFilterHeaderFooter("HEADER", "FOOTER");
  $pipeline->pre_tree_filters[] = $filter;

  // Override destination to local file
  $pipeline->destination = new MyDestinationFile($path_to_pdf);

  $baseurl = "";
  $media = (new Media())->predefined("A4");
  $media->set_landscape(false);
  $media->set_margins(array('left'   => 0,
                            'right'  => 0,
                            'top'    => 10,
                            'bottom' => 10));
  $media->set_pixels(1024); 

  global $g_config;
  $g_config = array(
                    'cssmedia'     => 'screen',
                    'scalepoints'  => '1',
                    'renderimages' => true,
                    'renderlinks'  => true,
                    'renderfields' => true,
                    'renderforms'  => false,
                    'mode'         => 'html',
                    'encoding'     => '',
                    'debugbox'     => false,
                    'pdfversion'    => '1.4',
                    'draw_page_border' => false
                    );

  $pipeline->process($baseurl, $media);
}

convert_to_pdf("../testsuite/encoding/iso-8859-2.html", "../data/testing/test.pdf");

?>