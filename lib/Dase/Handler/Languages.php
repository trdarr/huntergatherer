<?php

class Dase_Handler_Languages extends Dase_Handler {
  public $resource_map = array(
    '/' => 'index',
    '{id}' => 'language_by_id',
  );

  public function getIndex($r) {
    $languages = new Dase_DBO_Languages($this->db);
    $languages = $languages->findAll();

    foreach ($languages as $i => $language) {
      $languages[$i] = $language->name;
    }

    $r->assign('languages', $languages);
    $r->renderResponse(print_r($languages, true));
  }

  public function getLanguageById($r) {
    $language = new Dase_DBO_Languages($this->db);
    $language->id = $r->get('id');
    $language = $language->findOne();

    $r->renderResponse($language->name);
  }
}

