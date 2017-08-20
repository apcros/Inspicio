<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AutoImportRepositories extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('auto_imports', function (Blueprint $table) {
			$table->increments('id');
			$table->string('repository');
			$table->uuid('account_id');
			$table->uuid('user_id');
			$table->boolean('is_active');
			$table->boolean('send_result');
			$table->timestamps();

			$table->foreign('account_id')->references('id')->on('accounts');
			$table->foreign('user_id')->references('id')->on('users');
		});

		Schema::create('auto_imports_result', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('auto_import_id');
			$table->timestamps();
			$table->boolean('is_success');

			//If there was an error, the request row certainly isn't created
			//Alernatively, if it's a success, there will be no error message
			$table->string('error')->nullable();
			$table->uuid('request_id')->nullable();

			$table->foreign('auto_import_id')->references('id')->on('auto_imports');
			$table->foreign('request_id')->references('id')->on('requests');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::drop('auto_imports_result');
		Schema::drop('auto_imports');
	}
}
