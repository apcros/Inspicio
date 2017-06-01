<?php
namespace App\Classes\Models\Git;

class PullRequest {

	public $name;

	public $url;

	public function __construct($args) {

		foreach ($args as $attribute => $value) {
			$this->$attribute = $value;
		}

	}

}
