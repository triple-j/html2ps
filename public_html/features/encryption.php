<?php

define('FEATURE_ENCRYPTION_COPY', 1);
define('FEATURE_ENCRYPTION_PRINT', 2); 
define('FEATURE_ENCRYPTION_MODIFY', 4);
define('FEATURE_ENCRYPTION_MODIFY_ANNOTATIONS', 8);

class FeatureEncryption {
  var $_mode = 0;

  function FeatureEncryption() {
  }

  function install(&$pipeline, $params) {
    $dispatcher =& $pipeline->get_dispatcher();
    $dispatcher->add_observer('after-driver-init', array(&$this, 'handle_driver_init'));

    $this->_mode = $params['mode'];
  }

  function handle_driver_init($params) {
    $pipeline =& $params['pipeline'];

    if (is_a($pipeline->output_driver, 'OutputDriverFPDF')) {
      require_once(HTML2PS_DIR.'pdf.fpdf.encryption.php');
      $old_pdf =& $pipeline->output_driver->pdf;

      $pipeline->output_driver->pdf =& new FPDF_Protection('P', 
                                                           'pt', 
                                                           array($old_pdf->fw,
                                                                 $old_pdf->fh));
      $pipeline->output_driver->pdf->SetProtection($this->make_fpdf_protection_mode());
    } elseif (is_a($pipeline->output_driver, 'OutputDriverPDFLIB')) {
      $pipeline->output_driver->set_permissions($this->make_pdflib_protection_mode());
      $pipeline->output_driver->reset( $pipeline->output_driver->get_media() );
    };
  }

  function make_fpdf_protection_mode() {
    $result = array();

    if ($this->_mode & FEATURE_ENCRYPTION_COPY) { 
      $result[] = 'copy';
    };

    if ($this->_mode & FEATURE_ENCRYPTION_PRINT) { 
      $result[] = 'print';
    };

    if ($this->_mode & FEATURE_ENCRYPTION_MODIFY) { 
      $result[] = 'modify';
    };

    if ($this->_mode & FEATURE_ENCRYPTION_MODIFY_ANNOTATIONS) { 
      $result[] = 'annot-forms';
    };

    return $result;
  }

  function make_pdflib_protection_mode() {
    $result = array();

    if ($this->_mode & FEATURE_ENCRYPTION_COPY) { 
      $result[] = 'nocopy';
    };

    if ($this->_mode & FEATURE_ENCRYPTION_PRINT) { 
      $result[] = 'noprint';
    };

    if ($this->_mode & FEATURE_ENCRYPTION_MODIFY) { 
      $result[] = 'nomodify';
    };

    if ($this->_mode & FEATURE_ENCRYPTION_MODIFY_ANNOTATIONS) { 
      $result[] = 'noannots';
    };

    return 'permissions {'.join(' ', $result).'} masterpassword 1234';
  }
}

?>