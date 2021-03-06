<?php

require_once 'Dase/DBO.php';

/*
 * DO NOT EDIT THIS FILE
 * it is auto-generated by the
 * script 'bin/class_gen.php
 * 
 */

class Dase_DBO_Autogen_VocabularyFeaturesToLanguages extends Dase_DBO 
{
	public function __construct($db,$assoc = false) 
	{
		parent::__construct($db,'vocabulary_features_to_languages', array('feature_id','language_id','original_form','ipa_form'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->fields[$key] = $value;
			}
		}
	}
    public function getFeature_id() { return $this->fields["feature_id"]; }
    public function getLanguage_id() { return $this->fields["language_id"]; }
    public function getOriginal_form() { return $this->fields["original_form"]; }
    public function getIpa_form() { return $this->fields["ipa_form"]; }
}