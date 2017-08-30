<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSettings extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('settings', function (Blueprint $table) {
			$table->string('key');
			$table->string('default');
			$table->primary('key');
		});
		DB::table('settings')->insert($this->getSettings());
		Schema::create('user_settings', function (Blueprint $table) {
			$table->increments('id');
			$table->string('value');
			$table->uuid('user_id');
			$table->string('setting_key');

			$table->foreign('user_id')->references('id')->on('users');
			$table->foreign('setting_key')->references('key')->on('settings');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('user_settings');
		Schema::dropIfExists('settings');
	}

	private function getSettings() {
		$json_file = file_get_contents(database_path() . '/migrations/fixtures/settings.json');

		return json_decode($json_file, true);
	}
}
