<?php
/**
 * Runs the HTML->PDF conversion with default settings
 *
 * Warning: if you have any files (like CSS stylesheets and/or images referenced by this file,
 * use absolute links (like http://my.host/image.gif).
 *
 * @param $path_to_html String path to source html file.
 * @param $path_to_pdf  String path to file to save generated PDF to.
 */
 
 
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

function convert_to_pdf($path_to_html, $path_to_pdf) {
	global $aAllgEinstellungen;
    
	#ini_set("display_errors", 1);
	#error_reporting(E_ALL);
  $pipeline =  (new PipelineFactory())->create_default_pipeline("", // Attempt to auto-detect encoding
													   "");

  // Override HTML source
  $pipeline->fetchers[] = new MyFetcherLocalFile($path_to_html);

  // Override destination to local file
  $pipeline->destination = new MyDestinationFile($path_to_pdf);

  $leftmargin = 0;
  $rightmargin = 0;
  $topmargin = 0;
  $bottommargin = 0;
  
	if(!empty($aAllgEinstellungen['pdf_border_left'])) $leftmargin = $aAllgEinstellungen['pdf_border_left'];
	if(!empty($aAllgEinstellungen['pdf_border_right'])) $rightmargin = $aAllgEinstellungen['pdf_border_right'];
	if(!empty($aAllgEinstellungen['pdf_border_top'])) $topmargin = $aAllgEinstellungen['pdf_border_top'];
	if(!empty($aAllgEinstellungen['pdf_border_bottom'])) $bottommargin = $aAllgEinstellungen['pdf_border_bottom'];
  
  
  
  $baseurl = "";
  $media = (new Media())->predefined("A4");
  $media->set_landscape(false);
  $media->set_margins(array('left'   => $leftmargin,
							'right'  => $rightmargin,
							'top'    => $topmargin,
							'bottom' => $bottommargin));
  $media->set_pixels(1024);

  global $g_config;
  $g_config = array(
					'cssmedia'     => 'screen',
					'renderimages' => true,
					'renderlinks'  => true,
					'renderfields' => true,
					'renderforms'  => false,
					'mode'         => 'html',
					'encoding'     => '',
					'debugbox'     => false,
					'pdfversion'    => '1.5',
					'scalepoints'    => '1',
					'pixels'    => '800',
					'draw_page_border' => false
					);

  global $g_px_scale;
  $g_px_scale = mm2pt($media->width() - $media->margins['left'] - $media->margins['right']) / $media->pixels;
  global $g_pt_scale;
  $g_pt_scale = $g_px_scale * 1.43;

  $pipeline->process($baseurl, $media);
  unset($pipeline);
}

?>