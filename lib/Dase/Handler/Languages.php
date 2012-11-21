<?php

class Dase_Handler_Languages extends Dase_Handler {
  public $resource_map = array(
    '/' => 'index',
    '{id}' => 'language_by_id',
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

    // Look up information about the language.
    $sql = 'SELECT
        language.name AS language_name,
        family.name AS family_name
      FROM languages AS language
      JOIN language_families AS family ON language.family_id = family.id
      WHERE language.id = :language_id';
    $statement = $this->db->getDbh()->prepare($sql);
    if (!$statement->execute(array(':language_id' => $language_id))) {
      $error = $statement->errorInfo();
      $errors[] = $error[2];
    } else {
      $language = $statement->fetch();
      $r->assign('language', $language);
    }

    // Look up grammatical features that the language possesses.
    $sql = 'SELECT
        category.name AS category,
        feature.name,
        feature.note,
        ftol.answer,
        ftol.source
      FROM grammatical_features_to_languages AS ftol
      JOIN grammatical_features AS feature ON feature.id = ftol.feature_id
      JOIN languages AS language ON language.id = ftol.language_id
      JOIN grammatical_feature_categories AS category ON feature.category_id = category.id
      WHERE language.id = :language_id';
    $statement = $this->db->getDbh()->prepare($sql);
    if (!$statement->execute(array(':language_id' => $language_id))) {
      $error = $statement->errorInfo();
      $errors[] = $error[2];
    } else {
      $grammatical_features = $statement->fetchAll();
      $r->assign('grammatical_features', $grammatical_features);
    }

    // Look up vocabulary features that the language possesses.
    $sql = 'SELECT
        feature.english,
        field.name AS field_name,
        pos.name AS pos_name,
        ftol.original_form,
        ftol.ipa_form
      FROM vocabulary_features_to_languages AS ftol
      JOIN vocabulary_features AS feature ON feature.id = ftol.feature_id
      JOIN languages AS language ON language.id = ftol.language_id
      JOIN semantic_fields AS field ON field.id = feature.field_id
      JOIN parts_of_speech AS pos ON pos.id = feature.pos_id
      WHERE language.id = :language_id';
    $statement = $this->db->getDbh()->prepare($sql);
    if (!$statement->execute(array(':language_id' => $language_id))) {
      $error = $statement->errorInfo();
      $errors[] = $error[2];
    } else {
      $vocabulary_features = $statement->fetchAll();
      $r->assign('vocabulary_features', $vocabulary_features);
    }

    if (count($errors)) $r->assign('errors', $errors);
    $r->renderTemplate('languages_view.tpl');
  }
}

