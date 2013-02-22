<?php

// Who need autoloading when you have manual imports?
require_once BASE_PATH . '/Importer.php';

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
    $time = microtime(true);
    $fh = fopen($file, 'r');

    $method_calls = array();
    $header = fgetcsv($fh, 0, "\t");
    while (false !== ($feature = fgetcsv($fh, 0, "\t"))) {
      $feature = array_combine($header, $feature);
      foreach ($importer->methods as $method) {
        if (isset($method_calls[$method]))
          $method_calls[$method] += 1;
        else $method_calls[$method] = 1;
        $importer->$method($feature);
      }
    }

    fclose($fh);

    // Output debugging information during development.
    printf("Execution time: %.2f s.\n\n", microtime(true) - $time);
    printf("Method calls: %d. %s\n\n",
      array_sum($method_calls), print_r($method_calls, true));
    printf("Database queries: %d. via %s\n\n",
      array_sum(Importer::$queries), print_r(Importer::$queries, true));
  }

  protected function getIndex($r) {
    $file = '/srv/huntergatherer/files/uploads/arawak.tsv';
    $this->importFile(new Importer($this->db->getDbh()), $file);
    $r->renderOk();
  }
}

