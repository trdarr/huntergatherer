<?php

class Importer {
  // The number of database queries by type, for debugging.
  public static $queries = array();

  public $methods; // An array of the names of this object's public methods.
  private $cache;  // An array for caching the results of SQL queries.
  private $pdo;    // The PDO object, required by the constructor.

  public function __construct($pdo) {
    $methods = new ReflectionClass(__CLASS__);
    $methods = array_filter($methods->getMethods(ReflectionMethod::IS_PUBLIC),
      function ($e) { return substr($e->name, 0, 2) !== '__'; });
    $methods = array_map(function ($e) { return $e->name; }, $methods);

    $this->cache = array_fill_keys($methods, array());
    $this->methods = $methods;
    $this->pdo = $pdo;
  }

  // Generate cache functions to save some typing.
  // TODO, maybe: move this into an object if you don't like functional PHP.
  // TODO, maybe: if $cache_key is null, make functions require keys.
  private function cache_functions($function, $cache_key) {
    // Dynamically select which cache to pull from.
    $cache = &$this->cache[$function];

    // Generate functions for working with that specific cache.
    // All functions handle an arbitrary key or the provided cache_key.
    return array(
      // cache_get: retrieve the value for a key.
      function ($key=null) use (&$cache, $cache_key) {
        return $cache[$key !== null ? $key : $cache_key];
      },

      // cache_set: cache a key/value pair. $value defaults to true.
      function ($value=true, $key=null) use (&$cache, $cache_key) {
        $cache[$key !== null ? $key : $cache_key] = $value;
      },

      // is_cached: does this key exist in the cache?
      function ($key=null) use (&$cache, $cache_key) {
        return isset($cache[$key !== null ? $key : $cache_key]);
      },
    );
  }

  // Query the database given some SQL and the parameters.
  // Returns a PDOStatement::fetchAll result set, if applicable.
  private function query($sql, $params=null) {
    $type = explode(' ', $sql); $type = $type[0];
    if (isset(self::$queries[$type]))
      self::$queries[$type] += 1;
    else self::$queries[$type] = 1;

    $statement = $this->pdo->prepare($sql);
    $success = $params !== null
      ? $statement->execute($params)
      : $statement->execute();

    // PDOStatement::fetchAll throws an exception for some statements.
    if (!in_array($type, array('DELETE', 'INSERT', 'UPDATE')))
      return $statement->fetchAll(PDO::FETCH_ASSOC);
  }

  // Delegate to one of the private methods with similar names.
  private function grammatical_feature_category($feature) {
    $category_string = $feature['GrammarFeatures::category'];
    if (false === strpos($category_string, ' - '))
      return $this->grammatical_feature_category_one($category_string);
    else {
      list($category, $subcategory) = explode(' - ', $category_string);
      return $this->grammatical_feature_category_heirarchy(
        $this->grammatical_feature_category_two($category, $subcategory)
      );
    }
  }

  // Handle the case of a single category (vs. a category/subcategory pair).
  private function grammatical_feature_category_one($category) {
    $cache_key = $category;
    list($cache_get, $cache_set, $is_cached) =
      $this->cache_functions(__FUNCTION__, $cache_key);

    // Build the array of parameters.
    $params = array(':category' => $category);

    // 1. If it's cached, don't query for it again. Otherwise, query for it.
    // 2. If the query returns something, cache it. If not, insert it first.
    // To avoid deep nesting, keep checking is_cached.
    // For convenience, we return the category_id of the feature.
    // (It might be worth spending some time genericizing this routine.)

    // Check to see if it exists in the database.
    if (!$is_cached()) {
      $sql = 'SELECT id, name
        FROM grammatical_feature_categories
        WHERE name = :category';
      $results = $this->query($sql, $params);
      foreach ($results as $result) $cache_set($result['id']);
    }

    // Make it exist in the database if it doesn't.
    if (!$is_cached()) {
      $sql = 'INSERT INTO grammatical_feature_categories
        (created_at, name) VALUES (NOW(), :name)';
      $this->query($sql, $params);
      $cache_set($this->pdo->lastInsertId());
    }

    return $cache_get();
  }

  // Handle the case of a category/subcategory pair (vs. a single category).
  private function grammatical_feature_category_two($category, $subcategory) {
    $cache_key = null;
    list($cache_get, $cache_set, $is_cached) =
      $this->cache_functions(__FUNCTION__, $cache_key);

    // Build the array of parameters.
    $params = array(
      ':category' => $category,
      ':subcategory' => $subcategory
    );

    // Check to see if it exists in the database.
    if (!$is_cached($category) or !$is_cached($subcategory)) {
      $sql = 'SELECT id, name FROM grammatical_feature_categories
        WHERE name = :category OR name = :subcategory';
      $results = $this->query($sql, $params);
      foreach ($results as $result)
        $cache_set($result['id'], $result['name']);
    }

    // Make it exist in the database if it doesn't.
    $sql = 'INSERT INTO grammatical_feature_categories
      (created_at, name) VALUES (NOW(), :name)';
    foreach (array_values($params) as $name)
      if (!$is_cached($name)) {
        $this->query($sql, array(':name' => $name));
        $cache_set($this->pdo->lastInsertId(), $name);
      }

    return array($cache_get($category), $cache_get($subcategory));
  }

  // In case of a category/subcategory pair, encode the heirarchy.
  private function grammatical_feature_category_heirarchy($ids) {
    $cache_key = '(' . implode(', ', $ids) . ')';
    list($cache_get, $cache_set, $is_cached) =
      $this->cache_functions(__FUNCTION__, $cache_key);

    // Build the array of parameters.
    list($parent_id, $child_id) = $ids;
    $params = array_combine(array(':parent_id', ':child_id'), $ids);

    // Check to see if it exists in the database.
    if (!$is_cached()) {
      $sql = 'SELECT * FROM grammatical_feature_category_heirarchies
        WHERE parent_id = :parent_id AND child_id = :child_id';
      $results = $this->query($sql, $params);
      foreach ($results as $result) $cache_set($result['id']);
    }

    // Make it exist in the database if it doesn't.
    if (!$is_cached()) {
      $sql = 'INSERT INTO grammatical_feature_category_heirarchies
        (parent_id, child_id) VALUES (:parent_id, :child_id)';
      $this->query($sql, $params);
      $cache_set($this->pdo->lastInsertId());
    }

    return $child_id;
  }

  private function grammatical_feature($feature) {
    $cache_key = $name = $feature['grammaticalfeature'];
    list($cache_get, $cache_set, $is_cached) =
      $this->cache_functions(__FUNCTION__, $cache_key);

    // Build the array of parameters.
    $params = array(
      ':category_id' => $this->grammatical_feature_category($feature),
      ':name' => $name,
      ':notes' => $feature['GrammarFeatures::notes'],
    );

    // Check to see if it exists in the database.
    if (!$is_cached()) {
      $sql = 'SELECT id, name FROM grammatical_features WHERE name = :name';
      $results = $this->query($sql, array(':name' => $name));
      foreach ($results as $result) $cache_set($result['id']);
    }

    // Make it exist in the database if it doesn't.
    if (!$is_cached()) {
      $sql = 'INSERT INTO grammatical_features
        (created_at, category_id, name, note)
        VALUES (NOW(), :category_id, :name, :notes)';
      $this->query($sql, $params);
      $cache_set($this->pdo->lastInsertId());
    }

    return $cache_get();
  }

  private function language($feature) {
    $cache_key = $language = $feature['Language'];
    list($cache_get, $cache_set, $is_cached) =
      $this->cache_functions(__FUNCTION__, $cache_key);

    // Build the array of parameters.
    $params = array(':name' => $language);

    // Check to see if it exists in the database.
    if (!$is_cached()) {
      $sql = 'SELECT id, name FROM languages WHERE name = :name';
      $results = $this->query($sql, $params);
      foreach ($results as $result) $cache_set($result['id']);
    }

    // We can't make it exist in the database if it doesn't already because
    // there's not enough language data in the grammatical feature data files.
    // The solution is to import language data first.

    // Return null in that case.
    return $is_cached() ? $cache_get() : null;
  }

  public function grammatical_feature_to_language($feature) {
    $feature_id = $this->grammatical_feature($feature);
    $language_id = $this->language($feature);
    $cache_key = '(' . implode(', ', array($feature_id, $language_id)) . ')';
    list($cache_get, $cache_set, $is_cached) =
      $this->cache_functions(__FUNCTION__, $cache_key);

    // Abort if we don't have any language data.
    if (null === $language_id) return;

    // Build the array of parameters.
    $params = array(
      ':feature_id' => $feature_id,
      ':language_id' => $language_id,
      ':answer' => $feature['Answer'],
      ':source' => $feature['source'],
    );

    // Check to see if it exists in the database.
    if (!$is_cached()) {
      $sql = 'SELECT id, feature_id, language_id
        FROM grammatical_features_to_languages
        WHERE feature_id = :feature_id AND language_id = :language_id';
      $results = $this->query($sql, array_slice($params, 0, 2));
      foreach ($results as $result) $cache_set($result['id']);
    }

    // Make it exist in the database if it doesn't.
    if (!$is_cached()) {
      $sql = 'INSERT INTO grammatical_features_to_languages
        (feature_id, language_id, answer, source)
        VALUES (:feature_id, :language_id, :answer, :source)';
      $this->query($sql, $params);
      $cache_set($this->pdo->lastInsertId());
    }

    return $cache_get();
  }
}

