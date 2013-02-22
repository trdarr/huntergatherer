<?php

class Importer {
  // The number of database queries by type, for debugging.
  public static $queries = array();

  public $methods; // An array of the names of this object's methods.
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
    // Shortcuts to save some typing.
    $cache = &$this->cache[__FUNCTION__];
    $cache_it = function ($k, $v) use (&$cache) { return $cache[$k] = $v; };
    $is_cached = function ($k) use (&$cache) { return isset($cache[$k]); };

    // Build the array of parameters.
    $params = array(':category' => $category);

    // 1. If it's cached, don't query for it again. Otherwise, query for it.
    // 2. If the query returns something, cache it. If not, insert it first.
    // To avoid deep nesting, keep checking is_cached.
    // For convenience, we return the category_id of the feature.
    // (It might be worth spending some time genericizing this routine.)

    // Check to see if it exists in the database.
    if (!$is_cached($category)) {
      $sql = 'SELECT id, name
        FROM grammatical_feature_categories
        WHERE name = :category';
      $results = $this->query($sql, $params);
      if (count($results) < 2)
        $cache_it($category, $results[0]['id']);
      else throw new Exception("Too many of $category.");
    }

    // Make it exist in the database if it doesn't.
    if (!$is_cached($category)) {
      $sql = 'INSERT INTO grammatical_feature_categories
        (created_at, name) VALUES (NOW(), :name)';
      $this->query($sql, $params);
      $cache_it($category, $this->pdo->lastInsertId());
    }

    return $cache[$category];
  }

  // Handle the case of a category/subcategory pair (vs. a single category).
  private function grammatical_feature_category_two($category, $subcategory) {
    // Shortcuts to save some typing.
    $cache = &$this->cache[__FUNCTION__];
    $cache_it = function ($k, $v) use (&$cache) { return $cache[$k] = $v; };
    $is_cached = function ($k) use (&$cache) { return isset($cache[$k]); };

    // Build the array of parameters.
    $params = array(':category' => $category, ':subcategory' => $subcategory);

    // Check to see if it exists in the database.
    if (!$is_cached($category) or !$is_cached($subcategory)) {
      $sql = 'SELECT id, name
        FROM grammatical_feature_categories
        WHERE name = :category OR name = :subcategory';
      $results = $this->query($sql, $params);
      if (count($results) < 3)
        foreach ($results as $result)
          $cache_it($result['name'], $result['id']);
      else throw new Exception("Too many of $category or $subcategory.");
    }

    // Make it exist in the database if it doesn't.
    $sql = 'INSERT INTO grammatical_feature_categories
      (created_at, name) VALUES (NOW(), :name)';
    foreach (array_values($params) as $name)
      if (!$is_cached($name)) {
        $this->query($sql, array(':name' => $name));
        $cache_it($name, $this->pdo->lastInsertId());
      }

    return array($cache[$category], $cache[$subcategory]);
  }

  // In case of a category/subcategory pair, encode the heirarchy.
  private function grammatical_feature_category_heirarchy($ids) {
    // Shortcuts to save some typing.
    list($parent_id, $child_id) = $ids;
    $cache = &$this->cache[__FUNCTION__];
    $cache_it = function ($k, $v) use (&$cache) { return $cache[$k] = $v; };
    $is_cached = function ($k) use (&$cache) { return isset($cache[$k]); };
    $key = '(' . implode(', ', $ids) . ')';

    // Build the array of parameters.
    $params = array_combine(array(':parent_id', ':child_id'), $ids);

    // Check to see if it exists in the database.
    if (!$is_cached($key)) {
      $sql = 'SELECT * FROM grammatical_feature_category_heirarchies
        WHERE parent_id = :parent_id AND child_id = :child_id';
      $results = $this->query($sql, $params);
      if (count($results) < 2)
        foreach ($results as $result)
          $cache_it($key, $result['id']);
      else throw new Exception("Too many of $key.");
    }

    // Make it exist in the database if it doesn't.
    if (!$is_cached($key)) {
      $sql = 'INSERT INTO grammatical_feature_category_heirarchies
        (parent_id, child_id) VALUES (:parent_id, :child_id)';
      $this->query($sql, $params);
      $cache_it($key, $this->pdo->lastInsertId());
    }

    return $child_id;
  }

  public function grammatical_feature($feature) {
    // Shortcuts to save some typing.
    $cache = &$this->cache[__FUNCTION__];
    $cache_it = function ($k, $v) use (&$cache) { return $cache[$k] = $v; };
    $is_cached = function ($k) use (&$cache) { return isset($cache[$k]); };

    // Build the array of parameters.
    $name = $feature['grammaticalfeature'];
    $params = array(
      ':category_id' => $this->grammatical_feature_category($feature),
      ':name' => $name,
      ':notes' => $feature['GrammarFeatures::notes'],
    );

    // Check to see if it exists in the database.
    if (!$is_cached($name)) {
      $sql = 'SELECT id, name
        FROM grammatical_features
        WHERE name = :name';
      $results = $this->query($sql, array(':name' => $name));
      if (count($results) < 2)
        foreach ($results as $result)
          $cache_it($name, $result['id']);
      else throw new Exception("Too many of $name.");
    }

    // Make it exist in the database if it doesn't.
    if (!$is_cached($name)) {
      $sql = 'INSERT INTO grammatical_features
        (created_at, category_id, name, note)
        VALUES (NOW(), :category_id, :name, :notes)';
      $this->query($sql, $params);
      $cache_it($name, $this->pdo->lastInsertId());
    }

    return $cache[$name];
  }
}

