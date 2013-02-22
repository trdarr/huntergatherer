<?php

class Importer {
  // A map of CSV header strings to names of this object's methods.
  // Methods that need multiple keys: separated by the | character.
  public static $functions = array(
    'GrammarFeatures::category' => 'grammatical_feature_category',
    'GrammarFeatures::category|grammaticalfeature|GrammarFeatures::notes' => 'grammatical_feature',
    // XXX: Abort. Each level requires all of the data, basically. :(
  );

  private $cache;  // Cache the results of SQL queries.
  private $pdo;    // The PDO object, required by the constructor.

  public function __construct($pdo) {
    $this->cache = array_fill_keys(array_values(self::$functions), array());
    $this->pdo = $pdo;
  }

  // TODO: Handle category heirarchy instead of ignoring it.
  public function grammatical_feature_category($value) {
    // Ensure the value has two parts.
    if (false !== strpos($value, ' - ')) {
      list($category, $subcategory) = explode(' - ', $value);
      $params = array(':category' => $category,
        ':subcategory' => $subcategory);
    } else return;  // TODO: Handle single-level categories.

    // Shortcuts to save some typing.
    $cache = &$this->cache[__FUNCTION__];
    $pdo = $this->pdo;

    // If any of $category or $subcategory isn't cached, query for it.
    if (!isset($cache[$category]) or !isset($cache[$subcategory])) {
      $statement = $pdo->prepare('SELECT id, name
        FROM grammatical_feature_categories
        WHERE name = :category OR name = :subcategory');
      $statement->execute($params);
      $results = $statement->fetchAll(PDO::FETCH_ASSOC);

      // Cache the results of the query.
      foreach ($results as $result)
        $cache[$result['name']] = $result['id'];
    }

    // Insert any of $category or $subcategory if it's uncached.
    $c = isset($cache[$category]);
    $s = isset($cache[$subcategory]);
    if (!$c or !$s) {
      $values = array();
      if (!$c) $values[] = '(NOW(), :category)';
      else unset($params[':category']);
      if (!$s) $values[] = '(NOW(), :subcategory)';
      else unset($params[':subcategory']);
      $statement = $pdo->prepare('INSERT INTO grammatical_feature_categories
        (created_at, name) VALUES ' . implode(', ', $values));;
      $statement->execute($params);

      // Query for $category and $subcategory. Cache the results.
      $statement = $pdo->prepare('SELECT id, name
        FROM grammatical_feature_categories
        WHERE name = :category OR name = :subcategory');
      $statement->execute($params);
      $results = $statement->fetchAll(PDO::FETCH_ASSOC);
      foreach ($results as $result)
        $cache[$result['name']] = $result['id'];
    }

    // Avoid having to reparse the input to the function.
    return array($category, $subcategory);
  }

  public function grammatical_feature($category, $name, $notes) {
    // Shortcuts to save some typing.
    $cache = &$this->cache[__FUNCTION__];
    $pdo = $this->pdo;

    // If $name isn't cached, query for it.
    if (!isset($cache[$name])) {
      $statement = $pdo->prepare('SELECT id, name
        FROM grammatical_features WHERE name = :name');
      $statement->execute(array(':name' => $name));
      $results = $statement->fetchAll(PDO::FETCH_ASSOC);

      // Cache the results of the query.
      foreach ($results as $result)
        $cache[$result['name']] = $result['id'];
    }

    // If $name isn't cached, sort it.
    if (!isset($cache[$name])) {
      $function = 'grammatical_feature_category';
      list($category, $subcategory) = $this->$function($category);
      $category_id = isset($this->cache[$function][$subcategory])
        ? $this->cache[$function][$subcategory]
        : 0;  // TODO: Handle single-level categories.

      $statement = $pdo->prepare('INSERT INTO grammatical_features
        (created_at, category_id, name, note)
        VALUES (NOW(), :category_id, :name, :note)');
      $statement->execute(array(':name' => $name,
        ':category_id' => $category_id, ':note' => $notes));

      // Query for $feature. Cache the results.
      $statement = $pdo->prepare('SELECT id, name
        FROM grammatical_features WHERE name = :name');
      $statement->execute(array(':name' => $name));
      $results = $statement->fetchAll(PDO::FETCH_ASSOC);
      foreach ($results as $result)
        $cache[$result['name']] = $result['id'];
    }

    return array($name);
  }
}


class Dase_Handler_Import extends Dase_Handler {
  protected $resource_map = array(
    '/' => 'index',
  );

  // Return the tables of a (My-?) SQL database.
  private function getTables() {
    $pdo = $this->db->getDbh();
    return array_map(function($e) { return $e[0]; },
      $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_NUM));
  }

  // Return the columns of a (My-?) SQL table.
  private function getColumns($table) {
    $pdo = $this->db->getDbh();
    return array_map(function($e) { return $e[0]; },
      // This should work with bindParam. Why doesn't it?
      $pdo->query("DESCRIBE $table")->fetchAll(PDO::FETCH_NUM));
  }

  // Reads a CSV file and imports each record into the database.
  // The import is handled by the methods of the Importer class.
  private function importFile($importer, $file) {
    $fh = fopen($file, 'r');

    $header = fgetcsv($fh, 0, "\t");
    while (false !== ($feature = fgetcsv($fh, 0, "\t"))) {
      $feature = array_combine($header, $feature);
      foreach (Importer::$functions as $key => $function) {
        $keys = explode('|', $key);
        call_user_func_array(array($importer, $function), array_map(
          function ($e) use ($feature) { return $feature[$e]; }, $keys));
      }
    }

    fclose($fh);
  }

  protected function getIndex($r) {
    $file = '/srv/huntergatherer/files/uploads/arawak.tsv';
    $this->importFile(new Importer($this->db->getDbh()), $file);
    $r->renderOk();
  }
}

