<?php

require_once 'Dase/DBO.php';

/*
 * DO NOT EDIT THIS FILE
 * it is auto-generated by the
 * script 'bin/class_gen.php
 * 
 */

class Dase_DBO_Autogen_VocabularyFeatures extends Dase_DBO 
{
	public function __construct($db,$assoc = false) 
	{
		parent::__construct($db,'vocabulary_features', array('created_at','updated_at','created_by','updated_by','english','field_id','pos_id'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->fields[$key] = $value;
			}
		}
	}
    public function getCreated_at() { return $this->fields["created_at"]; }
    public function getUpdated_at() { return $this->fields["updated_at"]; }
    public function getCreated_by() { return $this->fields["created_by"]; }
    public function getUpdated_by() { return $this->fields["updated_by"]; }
    public function getEnglish() { return $this->fields["english"]; }
    public function getField_id() { return $this->fields["field_id"]; }
    public function getPos_id() { return $this->fields["pos_id"]; }
}