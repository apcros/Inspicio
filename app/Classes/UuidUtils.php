<?php
namespace App\Classes;
use \Ramsey\Uuid\Uuid;

class UuidUtils {

	public function is_valid($uuid) {
		$valid_uuid = preg_match("/^(\{)?[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}(?(1)\})$/i", $uuid);

		if (!$valid_uuid) {
			return false;
		}

		return true;
	}

	public function generate() {
		return Uuid::uuid4()->toString();
	}

}
