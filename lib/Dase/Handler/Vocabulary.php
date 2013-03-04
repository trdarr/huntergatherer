<?php

class Dase_Handler_Vocabulary extends Dase_Handler {
  public $resource_map = array(
    '/' => 'index',
    '{english}' => 'vocabulary_by_english',
  );

  public function getIndex($r) {
    $dbo = new Dase_DBO_VocabularyFeatures($this->db);
  }
}

