<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserSettingsManager {

	private $user_id;

	public function __construct($id) {
		$this->user_id = $id;
	}

	public function get($key) {
		$setting      = DB::table('settings')->where('key', $key)->first();
		$user_setting = $this->fetch_user_setting_row($key);

		if ($user_setting) {
			return $this->cast_type($setting->type, $user_setting->value);
		}

		return $this->cast_type($setting->type, $setting->value);
	}

	private function cast_type($type, $value) {

		if ($type == 'boolean') {

			if ($value == 'false') {
				return false;
			}

			return (bool) $value;
		}

		return $value;
	}

	public function set($key, $value) {

		$user_setting = $this->fetch_user_setting_row($key);

		if ($user_setting) {
			Log::debug("Updating $key with $value for " . $this->user_id);
			DB::table('user_settings')->where([
				['setting_key', '=', $key],
				['user_id', '=', $this->user_id],
			])->update(['value' => $value]);

			return;
		}

		DB::table('user_settings')->insert([
			'value'       => $value,
			'user_id'     => $this->user_id,
			'setting_key' => $key,
		]);

	}

	/*
		Merge the default and the user customized settings to get a full list
		TODO : Check to do that with a single query ?
	*/
	public function get_all() {
		$default_settings = DB::table('settings')->get();
		$merged_settings  = [];

		foreach ($default_settings as $setting) {
			$user_setting = DB::table('user_settings')->where([
				['setting_key', '=', $setting->key],
				['user_id', '=', $this->user_id],
			])->first();

			$merged_settings[$setting->key] = $setting;

			if ($user_setting) {
				$merged_settings[$setting->key]->value = $user_setting->value;
			}

			$merged_settings[$setting->key]->value = $this->cast_type($setting->type, $merged_settings[$setting->key]->value);

		}

		return $merged_settings;
	}

	private function fetch_user_setting_row($key) {
		return DB::table('user_settings')->where([
			['setting_key', '=', $key],
			['user_id', '=', $this->user_id],
		])->first();
	}

}
