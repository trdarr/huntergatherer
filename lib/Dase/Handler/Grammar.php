<?php

class Dase_Handler_Grammar extends Dase_Handler {
  public $resource_map = array(
    '/' => 'index',
    '{id}' => 'grammar_by_id',
  );

  protected function setup($r) {
    $r->assign('handler', 'grammar');
  }

  public function getIndex($r) {
    $errors = array();

    $dbo = new Dase_DBO_GrammaticalFeatures($this->db);
    list($success, $response) = $dbo->features();
    if ($success) {
      $r->assign('features', $response);
      $r->assign('features_count', array_reduce($response,
        function ($sum, $e) { return $sum + count($e); }, 0));
    } else $errors[] = $response;

    if (!empty($errors))
      $r->renderError(500, print_r($errors, true));
    $r->renderTemplate('grammar_index.tpl');
  }

  public function getGrammarById($r) {
    $errors = array();
    $feature_id = $r->get('id');

    $dbo = new Dase_DBO_GrammaticalFeatures($this->db);

    list($success, $response) = $dbo->feature($feature_id);
    if (empty($response)) $r->renderError(404);
    if ($success) $feature = $response;
    else $errors[] = $response;

    list($success, $response) = $dbo->languages($feature_id);
    if ($success) $feature['languages'] = $response;
    else $errors[] = $response;

    if (!empty($errors))
      $r->renderError(500, print_r($errors, true));
    $r->assign('feature', $feature);
    $r->renderTemplate('grammar_view.tpl');
  }
}

