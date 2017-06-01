<?php
namespace App\Classes\Models\Git;

class Tokens {

	public $token;

	public $refresh_token;

	public $expire_epoch;

	public function __construct($args) {

		foreach ($args as $attribute => $value) {
			$this->$attribute = $value;
		}

	}

}
