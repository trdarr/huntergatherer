<?php

require_once 'Dase/DBO/Autogen/Languages.php';

class Dase_DBO_Languages extends Dase_DBO_Autogen_Languages {
  public function get($language_id) {
    $sql = 'SELECT
        language.name AS language_name,
        family.name AS family_name
      FROM languages AS language
      JOIN language_families AS family ON language.family_id = family.id
      WHERE language.id = :language_id';
    $statement = $this->db->getDbh()->prepare($sql);
    if (!$statement->execute(array(':language_id' => $language_id))) {
      $error = $statement->errorInfo();
      return array(false, $error[2]);
    } else return array(true, $statement->fetch());
  }

  public function grammar($language_id) {
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
      return array(false, $error[2]);
    } else return array(true, $statement->fetchAll());
  }

  public function vocabulary($language_id) {
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
      return array(false, $error[2]);
    } else return array(true, $statement->fetchAll());
  }
}

