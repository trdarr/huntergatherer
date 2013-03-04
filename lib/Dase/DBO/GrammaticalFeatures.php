<?php

require_once 'Dase/DBO/Autogen/GrammaticalFeatures.php';

class Dase_DBO_GrammaticalFeatures extends Dase_DBO_Autogen_GrammaticalFeatures {
  public function feature($feature_id) {
    $sql = 'SELECT
        feature.id,
        feature.name,
        feature.note,
        category.name AS category
      FROM grammatical_features AS feature
      JOIN grammatical_feature_categories AS category ON feature.category_id = category.id
      WHERE feature.id = :feature_id';
    $statement = $this->db->getDbh()->prepare($sql);
    if (!$statement->execute(array(':feature_id' => $feature_id))) {
      $error = $statement->errorInfo();
      return array(false, $error[2]);
    } else return array(true, $statement->fetch(PDO::FETCH_ASSOC));
  }

  public function features() {
    $sql = 'SELECT
        category.name AS category_name,
        feature.category_id,
        feature.id,
        feature.name
      FROM grammatical_features AS feature
      JOIN grammatical_feature_categories AS category ON feature.category_id = category.id
      ORDER BY category.name, feature.name';
    $statement = $this->db->getDbh()->prepare($sql);
    if (!$statement->execute()) {
      $error = $statement->errorInfo();
      return array(false, $error[2]);
    } else return array(true, $statement->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_GROUP));
  }

  public function languages($feature_id) {
    $sql = 'SELECT
        family.name AS family_name,
        language.id,
        language.name AS language_name,
        ftol.answer,
        ftol.source
      FROM grammatical_features AS feature
      JOIN grammatical_features_to_languages AS ftol ON ftol.feature_id = feature.id
      JOIN languages AS language ON language.id = ftol.language_id
      JOIN language_families AS family on family.id = language.family_id
      WHERE feature.id = :feature_id
      ORDER BY language.name';
    $statement = $this->db->getDbh()->prepare($sql);
    if (!$statement->execute(array(':feature_id' => $feature_id))) {
      $error = $statement->errorInfo();
      return array(false, $error[2]);
    } else return array(true, $statement->fetchAll(PDO::FETCH_ASSOC));
  }
}

