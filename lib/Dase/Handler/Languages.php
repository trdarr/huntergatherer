<?php

class Dase_Handler_Languages extends Dase_Handler {
  public $resource_map = array(
    '/' => 'index',
    'edit' => 'language_edit',
    '{id}' => 'language_by_id',
  );

  // Retrieve list of languages as HTML.
  public function getIndex($r) {
    $errors = array();

    $dbo = new Dase_DBO_Languages($this->db);
    list($success, $response) = $dbo->languages();
    if ($success) {
      $r->assign('languages', $response);
      $r->assign('languages_count', array_reduce($response,
        function ($sum, $e) { return $sum + count($e); }, 0));
    } else $errors[] = $response;

    if (!empty($errors))
      $r->renderError(500, print_r($errors, true));
    $r->renderTemplate('languages_index.tpl');
  }

  // Retrieve language data and features as HTML.
  public function getLanguageById($r) {
    $errors = array();
    $data = $this->languageById($r->get('id'));

    // Grab the language data, which isn't editable right now.
    // Render a 404 if there's no language with this id.
    if (empty($data['language'][1])) $r->renderError(404);
    else {
      list($success, $response) = $data['language'];
      if ($success) $r->assign('language', $response);
      else $errors[] = $response;
    }

    // If the user is allowed to edit the data...
    if (true) {
      $types = array('grammatical', /* 'vocabulary', 'syntactical' */);
      foreach ($types as $type) {
        $type = "{$type}_features";
        list($success, $response) = $data[$type];
        if (!$success) $errors[] = $response;
        else if (!empty($response)) {
          // Extract the metadata from the keys of the first feature.
          // Values whose keys begin with 'feature-to-language' are editable.
          // There should be a separate interface for editing actual features.
          $metadata = array();
          $sample = reset($response);
          foreach (array_keys($sample[0]) as $key) {
            if ($key === 'id') continue;
            $e = substr($key, 0, 4) === 'ftol';
            $metadata[] = array(
              'datatype' => 'string',
              'editable' => $e,
              'label' => ucfirst($e ? substr($key, 5) : $key),
              'name' => $key,
            );
          }

          // All of the metadata for a feature type will be the same.
          // Loop over the categories and massage the features of each.
          // NB: This should probably happen even without edit privileges.
          foreach ($response as $category => $features) {
            $feature_data = array();
            foreach ($features as $f)
              $feature_data[] = array('id' => $f['id'], 'values' => $f);

            // Combine the metadata with the data and stash it somewhere.
            $responses[$category] = array(
              'metadata' => $metadata,
              'data' => $feature_data,
            );
          }

          // Send, e.g., {'Segmental': [], 'Alignment': []} to the template.
          $r->assign($type, $responses);
        }
      }
    }

    if (!empty($errors)) $r->assign('errors', $errors);
    $r->renderTemplate('languages_view.tpl');
  }

  // Retrieve language data and features as JSON.
  public function getLanguageByIdJson($r) {
    $data = $this->languageById($r->get('id'));
    $r->renderResponse(json_encode($data));
  }

  // Retrieve language data and features as a PHP array.
  // Used by the other getLanguageById methods.
  private function languageById($language_id) {
    $dbo = new Dase_DBO_Languages($this->db);

    foreach (array(
      'language' => 'language',
      'grammatical_features' => 'grammar',
      'vocabulary_features' => 'vocabulary',
    ) as $property => $method)
      $data[$property] = $dbo->$method($language_id);

    return $data;
  }

  // Handle AJAXy input from the EditableGrid.
  public function putEditLanguage($r) {
    $r->renderOk();
  }
}

