<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class IncrementalPermissions extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('accounts', function (Blueprint $table) {
			/*
				                Setting the default to maximum even though new accounts will be created
				                with minimum permissions.
				                This is to be in sync with the current data in the DB (26/09/2017)
				                As everyone currently is using maximum permissions
			*/
			$table->string('permission_level')->default('maximum');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('accounts', function (Blueprint $table) {
			$table->dropColumn('permission_level');
		});
	}
}
