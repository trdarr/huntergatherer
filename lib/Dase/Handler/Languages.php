<?php

class Dase_Handler_Languages extends Dase_Handler {
  public $resource_map = array(
    '/' => 'index',
    '{id}' => 'language_by_id',
    '{id}/edit' => 'edit_language_by_id',
  );

  public function getIndex($r) {
    $languages = new Dase_DBO_Languages($this->db);
    $languages = $languages->findAll();
    $r->assign('languages', $languages);
    $r->renderTemplate('languages_index.tpl');
  }

  public function getLanguageById($r) {
    $errors = array();
    $language_id = $r->get('id');

    $dbo = new Dase_DBO_Languages($this->db);

    // PHP metaprogramming is delicious.
    foreach (array(
      'language' => 'get',
      'grammatical_features' => 'grammar',
      'vocabulary_features' => 'vocabulary',
    ) as $property => $method) {
      list($success, $response) = $dbo->$method($language_id);
      if ($success)
        $r->assign($property, $response);
      else $errors[] = $response;
    }

    if (count($errors)) $r->assign('errors', $errors);
    $r->renderTemplate('languages_view.tpl');
  }

  public function getEditLanguageById($r) {
    $errors = array();
    $language_id = $r->get('id');

    $r->renderTemplate('languages_edit.tpl');
  }
}

