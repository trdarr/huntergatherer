<?php

class Dase_Handler_Index extends Dase_Handler {
  public $resource_map = array(
    '/' => 'index',
  );

  public function getIndex($r) {
    $r->renderTemplate('index.tpl');
  }
}

