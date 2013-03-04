<?php

require_once BASE_PATH . '/Importer.php';

class LanguagesImporter extends Importer {
  private function region($region) {
    $cache_key = $region;
    list($cache_get, $cache_set, $is_cached) =
      $this->cache_functions(__FUNCTION__, $cache_key);

    // Build the array of parameters.
    $params = array(':name' => $region);

    // Check to see if it exists in the database.
    if (!$is_cached()) {
      $sql = 'SELECT id, name FROM regions WHERE name = :name';
      $results = $this->query($sql, $params);
      foreach ($results as $result) $cache_set($result['id']);
    }

    // Make it exist in the database if it doesn't.
    if (!$is_cached()) {
      $sql = 'INSERT INTO grammatical_features
        (created_at, name) VALUES (NOW(), :name)';
      $this->query($sql, $params);
      $cache_set($this->pdo->lastInsertId());
    }

    return $cache_get();
  }

  private function language_family($family) {
    $cache_key = $family;
    list($cache_get, $cache_set, $is_cached) =
      $this->cache_functions(__FUNCTION__, $cache_key);

    // Build the array of parameters.
    $params = array(':name' => $family);
    $this->cache_functions(__FUNCTION__, $cache_key);

    // Check to see if it exists in the database.
    if (!$is_cached()) {
      $sql = 'SELECT id, name FROM language_families WHERE name = :name';
      $results = $this->query($sql, $params);
      foreach ($results as $result) $cache_set($result['id']);
    }

    // Make it exist in the database if it doesn't.
    if (!$is_cached()) {
      $sql = 'INSERT INTO language_families
        (created_at, name) VALUES (NOW(), :name)';
      $this->query($sql, $params);
      $cache_set($this->pdo->lastInsertId());
    }

    return $cache_get();
  }

  public function language($feature) {
    if (!isset($feature['languageName'])) {
      print_r($feature);
    }
    $cache_key = $language = $feature['languageName'];
    list($cache_get, $cache_set, $is_cached) =
      $this->cache_functions(__FUNCTION__, $cache_key);

    // Build the array of parameters.
    $params = array(
      ':name' => $language,
      ':family_id' => $this->language_family($feature['family']),
      ':region_id' => $this->region($feature['caseStudyRegion']),
      ':iso' => $feature['isoCode'],
      ':latitude' => $feature['latitude'],
      ':longitude' => $feature['longitude'],
      ':notes' => $feature['notes'],
    );

    // Check to see if it exists in the database.
    if (!$is_cached()) {
      $sql = 'SELECT id, name FROM languages WHERE name = :name';
      $results = $this->query($sql, array_slice($params, 0, 1));
      foreach ($results as $result) $cache_set($result['id']);
    }

    // Make it exist in the database if it doesn't.
    if (!$is_cached()) {
      $sql = 'INSERT INTO languages (created_at, name, family_id, region_id,
        iso, latitude, longitude, notes) VALUES (NOW(), :name, :family_id,
        :region_id, :iso, :latitude, :longitude, :notes)';
      $this->query($sql, $params);
      $cache_set($this->pdo->lastInsertId());
    }

    return $cache_get();
  }
}

