<?php

require_once 'Dase/DBO.php';

/*
 * DO NOT EDIT THIS FILE
 * it is auto-generated by the
 * script 'bin/class_gen.php
 * 
 */

class Dase_DBO_Autogen_LanguageFamilies extends Dase_DBO 
{
	public function __construct($db,$assoc = false) 
	{
		parent::__construct($db,'language_families', array('created_at','updated_at','name'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->fields[$key] = $value;
			}
		}
	}
    public function getCreated_at() { return $this->fields["created_at"]; }
    public function getUpdated_at() { return $this->fields["updated_at"]; }
    public function getName() { return $this->fields["name"]; }
}