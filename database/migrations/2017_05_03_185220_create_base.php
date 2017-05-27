<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateBase extends Migration {
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {

		Schema::dropIfExists('request_tracking');
		Schema::dropIfExists('requests');
		Schema::dropIfExists('user_skills');
		Schema::dropIfExists('accounts');
		Schema::dropIfExists('users');
		Schema::dropIfExists('skills');
	}

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {

		Schema::create('skills', function (Blueprint $table) {
			$table->increments('id');
			$table->string('name');
		});

		DB::table('skills')->insert($this->getLanguages());

		Schema::create('users', function (Blueprint $table) {
			$table->uuid('id');
			$table->string('name');
			$table->string('email')->unique();
			$table->string('nickname');
			$table->integer('rank');
			$table->integer('points');
			$table->timestamps();
			$table->primary('id');
		});

		Schema::create('accounts', function (Blueprint $table) {
			$table->uuid('id');
			$table->string('login');
			$table->uuid('user_id');
			$table->string('token'); //TODO : Add refresh token and expiration date, Github doesn't need it, bitbucket does.
			$table->string('provider');
			$table->boolean('is_main');
			$table->unique(array('login', 'provider'));
			$table->timestamps();

			$table->foreign('user_id')->references('id')->on('users');
			$table->primary('id');
		});

		Schema::create('user_skills', function (Blueprint $table) {
			$table->increments('id');
			$table->uuid('user_id');
			$table->boolean('is_verified');
			$table->integer('level');
			$table->unsignedInteger('skill_id');
			$table->timestamps();

			$table->foreign('user_id')->references('id')->on('users');
			$table->foreign('skill_id')->references('id')->on('skills');
		});
		Schema::create('requests', function (Blueprint $table) {
			$table->uuid('id');
			$table->string('name');
			$table->text('description');
			$table->string('url');
			$table->string('status');
			$table->unsignedInteger('skill_id');
			$table->string('repository');
			$table->uuid('account_id');
			$table->uuid('author_id');
			$table->timestamps();

			$table->foreign('author_id')->references('id')->on('users');
			$table->foreign('account_id')->references('id')->on('accounts');
            $table->foreign('skill_id')->references('id')->on('skills');
			$table->primary('id');
		});

		Schema::create('request_tracking', function (Blueprint $table) {
			$table->uuid('user_id');
			$table->uuid('request_id');
			$table->string('status');
			$table->timestamps();

			$table->foreign('user_id')->references('id')->on('users');
			$table->foreign('request_id')->references('id')->on('requests');
			$table->primary(['user_id', 'request_id']);
		});
	}

	private function getLanguages() {
		$json_file = file_get_contents(database_path() . '/migrations/fixtures/languages.json');
		$languages = json_decode($json_file);

		$resultSet;

		foreach ($languages as $key => $language) {
			$resultSet[] = array('name' => $language);
		}

		return $resultSet;
	}

}
