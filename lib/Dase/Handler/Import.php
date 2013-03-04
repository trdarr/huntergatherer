<?php

// Who needs autoloading when you have manual imports?
require_once BASE_PATH . '/GrammarImporter.php';
require_once BASE_PATH . '/LanguagesImporter.php';

class Dase_Handler_Import extends Dase_Handler {
  protected $resource_map = array(
    'grammar/{family}' => 'grammar_by_family',
    'languages/{region}' => 'languages_by_region',
  );

  // Reads a CSV file and imports each record into the database.
  // The import is handled by the methods of the Importer class.
  private function importFile($importer, $file) {
    $time = microtime(true);
    $fh = fopen($file, 'r');

    $method_calls = array();
    $header = fgetcsv($fh);
    while (false !== ($feature = fgetcsv($fh))) {
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
    printf("Database queries: %d. %s\n\n",
      array_sum(Importer::$queries), print_r(Importer::$queries, true));
  }

  protected function getGrammarByFamily($r) {
    $file = sprintf('%s/files/uploads/%s.csv',
      BASE_PATH, basename($r->get('family')));

    if (is_readable($file)) {
      $importer = new GrammarImporter($this->db->getDbh());
      $this->importFile($importer, $file);
    } else $r->renderError(404);

    $r->renderOk();
  }

  protected function getLanguagesByRegion($r) {
    $file = sprintf('%s/files/uploads/%s.csv',
      BASE_PATH, basename($r->get('region')));

    if (is_readable($file)) {
      $importer = new LanguagesImporter($this->db->getDbh());
      $this->importFile($importer, $file);
    } else $r->renderError(404);

    $r->renderOk();
  }
}

