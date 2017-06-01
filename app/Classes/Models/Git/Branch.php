<?php
namespace App\Classes\Models\Git;

class Branch {

	public $name;

	public function __construct($args) {

		foreach ($args as $attribute => $value) {
			$this->$attribute = $value;
		}

	}

}
