<?php
namespace App\Classes\Models\Git;

class Repository {

	public $name;

	public $id;

	public $url;

	public $language;

	public function __construct($args) {

		foreach ($args as $attribute => $value) {
			$this->$attribute = $value;
		}

	}

}
