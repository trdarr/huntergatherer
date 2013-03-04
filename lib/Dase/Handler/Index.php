<?php

class Dase_Handler_Index extends Dase_Handler {
  public $resource_map = array(
    '/' => 'index',
  );

  public function getIndex($r) {
    $bacon = file_get_contents('http://baconipsum.com/api?type=all-meat');
    $r->assign('bacon', implode("\n", array_map(
      function ($e) { return "<p>$e</p>"; },
      json_decode($bacon, true))));
    $r->renderTemplate('index.tpl');
  }
}

