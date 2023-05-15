<?php

class TestCSSPageBreakAfter extends GenericTest {
  function testCSSPageBreakAfter1() {
    $tree = $this->runPipeline('
<html>
<head>
<style type="text/css">
#div { page-break-after: avoid; }
</style>
</head>
<body>
<div id="div">&nbsp;</div>
</body>
</html>
');

    $div = $tree->getElementById('div');

    $this->assertEqual(PAGE_BREAK_AVOID, $div->getCSSProperty(CSS_PAGE_BREAK_AFTER));
  }
}

?>