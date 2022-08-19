<?php

class TestCSSPageBreakBefore extends GenericTest {
  function testCSSPageBreakBefore1() {
    $tree = $this->runPipeline('
<html>
<head>
<style type="text/css">
#div { page-break-before: avoid; }
</style>
</head>
<body>
<div id="div">&nbsp;</div>
</body>
</html>
');

    $div = $tree->getElementById('div');

    $this->assertEqual(PAGE_BREAK_AVOID, $div->getCSSProperty(CSS_PAGE_BREAK_BEFORE));
  }
}

?>