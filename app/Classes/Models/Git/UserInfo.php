<?php
namespace App\Classes\Models\Git;

class UserInfo {

	public $login;

	public function __construct($args) {

		foreach ($args as $attribute => $value) {
			$setter = 'set' . ucfirst($attribute);

			if (method_exists($this, $setter)) {
				$this->$setter($value);
			} else {
				$this->$attribute = $value;
			}

		}

	}

	public function setLogin($value) {
		$this->login = ucfirst(strtolower($value));
	}

}
