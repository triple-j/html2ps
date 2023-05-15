<?php

class GenericTest extends UnitTestCase {
  function runPipeline($html, $media = null) {
    $pipeline =  (new PipelineFactory())->create_default_pipeline("", "");
    $pipeline->configure(array('scalepoints' => false));

    $pipeline->fetchers = array(new MyFetcherMemory($html, ""));
    $pipeline->data_filters[] = new DataFilterHTML2XHTML();
    $pipeline->destination = new DestinationFile("test.pdf");

    parse_config_file('../html2ps.config');

    if (is_null($media)) {
      $media = (new Media())->predefined("A4");
    }

    $pipeline->_prepare($media);
    return $pipeline->_layout_item("", $media, 0, $context, $positioned_filter);
  }
}
?>