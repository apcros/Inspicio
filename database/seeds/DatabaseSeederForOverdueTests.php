<?php

use Illuminate\Database\Seeder;

class DatabaseSeederForOverdueTests extends Seeder {
	public function run() {
		DB::table('users')->insert([
			'id'            => '7636b30e-6db2-41b6-91b3-33560b9638c2',
			'name'          => 'John Doe',
			'email'         => 'testuser@thisisatest.co.uk',
			'nickname'      => 'testuser',
			'rank'          => 1,
			'is_confirmed'  => true,
			'confirm_token' => 'blah',
			'points'        => 5,
			'created_at'    => \Carbon\Carbon::now(),
			'updated_at'    => \Carbon\Carbon::now(),
		]);
		DB::table('users')->insert([
			'id'            => 'e6ca8f33-b196-4006-b7e6-3f2ffef3df92',
			'name'          => 'Jane Doe',
			'email'         => 'testuser2@thisisatest.co.uk',
			'nickname'      => 'testuser2',
			'is_confirmed'  => true,
			'confirm_token' => 'blah',
			'rank'          => 1,
			'points'        => 5,
			'created_at'    => \Carbon\Carbon::now(),
			'updated_at'    => \Carbon\Carbon::now(),
		]);
		DB::table('accounts')->insert([
			'id'         => '4cff704c-efe4-4024-9fa1-e5c0d7eaf2c5',
			'login'      => 'testuser',
			'token'      => 'dummy',
			'provider'   => 'github',
			'is_main'    => 1,
			'user_id'    => '7636b30e-6db2-41b6-91b3-33560b9638c2',
			'created_at' => \Carbon\Carbon::now(),
			'updated_at' => \Carbon\Carbon::now(),
		]);

		DB::table('accounts')->insert([
			'id'         => '1b157096-aa5b-4019-9839-c345b063447e',
			'login'      => 'testuser2',
			'token'      => 'dummy',
			'provider'   => 'github',
			'is_main'    => 1,
			'user_id'    => 'e6ca8f33-b196-4006-b7e6-3f2ffef3df92',
			'created_at' => \Carbon\Carbon::now(),
			'updated_at' => \Carbon\Carbon::now(),
		]);

		DB::table('requests')->insert([
			'id'          => 'e4dc3896-4a40-49b8-b3f2-0dc45916437a',
			'name'        => 'A long overdue PR',
			'description' => 'Wow this is a description',
			'url'         => 'http://dummyurl.com',
			'status'      => 'open',
			'author_id'   => '7636b30e-6db2-41b6-91b3-33560b9638c2',
			'account_id'  => '4cff704c-efe4-4024-9fa1-e5c0d7eaf2c5',
			'skill_id'    => 1,
			'repository'  => 'testuser/testrepo',
			'created_at'  => \Carbon\Carbon::now()->subYear(),
			'updated_at'  => \Carbon\Carbon::now()->subYear(),
		]);
		DB::table('requests')->insert([
			'id'          => 'e4dc3896-4a40-49b8-b3f2-0dc45916437b',
			'name'        => 'An old PR that was updated recently',
			'description' => 'Wow this is a description',
			'url'         => 'http://dummyurl.com/2',
			'status'      => 'open',
			'author_id'   => '7636b30e-6db2-41b6-91b3-33560b9638c2',
			'account_id'  => '4cff704c-efe4-4024-9fa1-e5c0d7eaf2c5',
			'skill_id'    => 1,
			'repository'  => 'testuser/testrepo',
			'created_at'  => \Carbon\Carbon::now()->subYear(),
			'updated_at'  => \Carbon\Carbon::now()->subDay(),
		]);

		DB::table('requests')->insert([
			'id'          => 'e4dc3896-4a40-49b8-b3f2-0dc45916437c',
			'name'        => 'A long overdue PR but with no approvals',
			'description' => 'Wow this is a description',
			'url'         => 'http://dummyurl.com',
			'status'      => 'open',
			'author_id'   => '7636b30e-6db2-41b6-91b3-33560b9638c2',
			'account_id'  => '4cff704c-efe4-4024-9fa1-e5c0d7eaf2c5',
			'skill_id'    => 1,
			'repository'  => 'testuser/testrepo',
			'created_at'  => \Carbon\Carbon::now()->subYear(),
			'updated_at'  => \Carbon\Carbon::now()->subYear(),
		]);

		DB::table('request_tracking')->insert([
			'user_id'     => 'e6ca8f33-b196-4006-b7e6-3f2ffef3df92',
			'request_id'  => 'e4dc3896-4a40-49b8-b3f2-0dc45916437a',
			'is_approved' => true,
			'is_active'   => true,
			'created_at'  => \Carbon\Carbon::now(),
			'updated_at'  => \Carbon\Carbon::now(),
		]);

		DB::table('request_tracking')->insert([
			'user_id'     => 'e6ca8f33-b196-4006-b7e6-3f2ffef3df92',
			'request_id'  => 'e4dc3896-4a40-49b8-b3f2-0dc45916437c',
			'is_approved' => false,
			'is_active'   => true,
			'created_at'  => \Carbon\Carbon::now(),
			'updated_at'  => \Carbon\Carbon::now(),
		]);

		DB::table('request_tracking')->insert([
			'user_id'     => 'e6ca8f33-b196-4006-b7e6-3f2ffef3df92',
			'request_id'  => 'e4dc3896-4a40-49b8-b3f2-0dc45916437b',
			'is_approved' => false,
			'is_active'   => true,
			'created_at'  => \Carbon\Carbon::now()->subYear(),
			'updated_at'  => \Carbon\Carbon::now()->subYear(),
		]);
	}
}
