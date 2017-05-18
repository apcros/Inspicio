<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBase extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
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
			$table->string('token');
			$table->string('provider');
			$table->boolean('is_main');
			$table->unique(array('login', 'provider'));
			$table->timestamps();

			$table->foreign('user_id')->references('id')->on('users');
			$table->primary('id');
		});

		Schema::create('skills', function (Blueprint $table) {
			$table->uuid('id');
			$table->string('name');
			$table->uuid('user_id');
			$table->boolean('is_verified');
			$table->integer('level');
			$table->timestamps();

			$table->foreign('user_id')->references('id')->on('users');
			$table->primary('id');
		});

		Schema::create('requests', function (Blueprint $table) {
			$table->uuid('id');
			$table->string('name');
			$table->text('description');
			$table->string('url');
			$table->string('status');
			$table->string('language');
			$table->string('repository');
			$table->uuid('account_id');
			$table->uuid('author_id');
			$table->timestamps();

			$table->foreign('author_id')->references('id')->on('users');
			$table->foreign('account_id')->references('id')->on('accounts');
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

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {

		Schema::dropIfExists('request_tracking');
		Schema::dropIfExists('requests');
		Schema::dropIfExists('skills');
		Schema::dropIfExists('accounts');
		Schema::dropIfExists('users');
	}
}
