<?php

require_once BASE_PATH . '/lib/Markdown.php';

class Dase_Handler_About extends Dase_Handler {
  protected $resource_map = array(
    '/' => 'index',
  );

  public function getIndex($r) {
    $about = file_get_contents(BASE_PATH . '/README.md');
    $about = implode("\n", array_slice(explode("\n", $about), 2));
    $r->assign('about', Markdown($about));
    $r->renderTemplate('about.tpl');
  }
}

