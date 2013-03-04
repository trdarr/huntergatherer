<?php

require_once 'Dase/DBO/Autogen/Languages.php';

class Dase_DBO_Languages extends Dase_DBO_Autogen_Languages {
  public function languages() {
    $sql = 'SELECT
        family.name AS family_name,
        language.family_id,
        language.id,
        language.name
      FROM languages AS language
      JOIN language_families AS family ON language.family_id = family.id
      ORDER BY family.name, language.name';
    $statement = $this->db->getDbh()->prepare($sql);
    if (!$statement->execute()) {
      $error = $statement->errorInfo();
      return array(false, $error[2]);
    } else return array(true, $statement->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_GROUP));
  }

  private function fetch($fetch, $sql, $language_id, $options=0) {
    $statement = $this->db->getDbh()->prepare($sql);
    if (!$statement->execute(array(':language_id' => $language_id))) {
      $error = $statement->errorInfo();
      return array(false, $error[2]);
    } else return array(true, $statement->$fetch(PDO::FETCH_ASSOC | $options));
  }

  private function fetchOne($sql, $language_id) {
    return $this->fetch('fetch', $sql, $language_id);
  }

  private function fetchAll($sql, $language_id) {
    return $this->fetch('fetchAll', $sql, $language_id);
  }

  private function fetchGroup($sql, $language_id) {
    return $this->fetch('fetchAll', $sql, $language_id, PDO::FETCH_GROUP);
  }

  public function language($language_id) {
    return $this->fetchOne('SELECT
        language.id AS language_id,
        language.name AS language_name,
        language.iso,
        language.latitude,
        language.longitude,
        language.notes,
        family.name AS family_name,
        region.name AS region_name
      FROM languages AS language
      JOIN language_families AS family ON language.family_id = family.id
      JOIN regions AS region ON language.region_id = region.id
      WHERE language.id = :language_id', $language_id);
  }

  public function grammar($language_id) {
    return $this->fetchGroup('SELECT
        category.name AS category,
        feature.name,
        feature.note,
        ftol.id,
        ftol.answer AS ftol_answer,
        ftol.source AS ftol_source
      FROM grammatical_features_to_languages AS ftol
      JOIN grammatical_features AS feature ON feature.id = ftol.feature_id
      JOIN languages AS language ON language.id = ftol.language_id
      JOIN grammatical_feature_categories AS category ON feature.category_id = category.id
      WHERE language.id = :language_id
      ORDER BY category.name, feature.name', $language_id);
  }

  public function vocabulary($language_id) {
    return $this->fetchAll('SELECT
        feature.english,
        field.name AS field_name,
        pos.name AS pos_name,
        ftol.id,
        ftol.original_form AS ftol_original_form,
        ftol.ipa_form AS ftol_ipa_form
      FROM vocabulary_features_to_languages AS ftol
      JOIN vocabulary_features AS feature ON feature.id = ftol.feature_id
      JOIN languages AS language ON language.id = ftol.language_id
      JOIN semantic_fields AS field ON field.id = feature.field_id
      JOIN parts_of_speech AS pos ON pos.id = feature.pos_id
      WHERE language.id = :language_id', $language_id);
  }
}

