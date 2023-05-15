<?php
class DestinationDownload extends DestinationHTTP {
  function __construct($filename) {
	DestinationHTTP::__construct($filename);
  }

  function headers($content_type) {
	global $sPDFName;
	  if(!empty($sPDFName)){
		return array(
				 "Content-Disposition: attachment; filename=".$sPDFName.".".$content_type->default_extension,
				 "Content-Transfer-Encoding: binary",
				 "Cache-Control: must-revalidate, post-check=0, pre-check=0",
				 "Pragma: public"
				 );
	  } else {
		return array(
				 "Content-Disposition: attachment; filename=".$this->filename_escape($this->get_filename()).".".$content_type->default_extension,
				 "Content-Transfer-Encoding: binary",
				 "Cache-Control: must-revalidate, post-check=0, pre-check=0",
				 "Pragma: public"
				 );
	  }
  }
}
?>