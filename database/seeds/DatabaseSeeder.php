<?php

use Illuminate\Database\Seeder;
use \Ramsey\Uuid\Uuid;

class DatabaseSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		$this->command->ask('!!! You are about to run a database seeding script, Proceed ? !!!');
		$this->addMainAccount();
		$counts = array(
			'addRandomUser' => 30,
			'addRandomRequest' => 200,
			'addRandomTracking' => 30,
		);
		foreach ($counts as $method => $count) {
			$choosen_count = $this->command->ask("How many times do you want to run $method ? ", $count);
			$this->command->getOutput()->progressStart($choosen_count);
			for ($i = 1; $i <= $choosen_count; $i++) {
				$this->$method();
				$this->command->getOutput()->progressAdvance();
			}
			$this->command->getOutput()->progressFinish();
		}
	}

	private function addMainAccount() {
		$login = $this->command->ask('What is the nickname of the account you will use to login ?');
		$provider = $this->command->ask('What is the git provider of that account ? ', 'github');

		$user_id = Uuid::uuid4()->toString();
		$account_id = Uuid::uuid4()->toString();

		DB::table('users')->insert([
			'id' => $user_id,
			'name' => 'John Doe',
			'email' => 'fake@fake.com',
			'nickname' => $login,
			'rank' => 1,
			'points' => 5,
			'created_at' => \Carbon\Carbon::now(),
			'updated_at' => \Carbon\Carbon::now(),
		]);
		DB::table('accounts')->insert([
			'id' => $account_id,
			'login' => $login,
			'token' => 'will be populated upon login',
			'provider' => $provider,
			'is_main' => 1,
			'user_id' => $user_id,
			'created_at' => \Carbon\Carbon::now(),
			'updated_at' => \Carbon\Carbon::now(),
		]);
	}

	/*
		    	TODO : Random timestamps
	*/
	private function addRandomUser() {
		$user_id = Uuid::uuid4()->toString();
		$account_id = Uuid::uuid4()->toString();
		$nickname = str_random(15);

		DB::table('users')->insert([
			'id' => $user_id,
			'name' => ucfirst(str_random(5)) . ' ' . ucfirst(str_random(4)),
			'email' => str_random(10) . '@' . str_random(5) . '.' . str_random(3),
			'nickname' => $nickname,
			'rank' => 1,
			'points' => 5,
			'created_at' => \Carbon\Carbon::now(),
			'updated_at' => \Carbon\Carbon::now(),
		]);

		DB::table('accounts')->insert([
			'id' => $account_id,
			'login' => $nickname,
			'token' => 'fakefakefakefake',
			'provider' => 'github',
			'is_main' => 1,
			'user_id' => $user_id,
			'created_at' => \Carbon\Carbon::now(),
			'updated_at' => \Carbon\Carbon::now(),
		]);

	}

	private function addRandomRequest() {
		$id = Uuid::uuid4()->toString();
		$user = DB::table('users')->inRandomOrder()->first();
		$account = DB::table('accounts')->where('user_id', $user->id)->first();
		DB::table('requests')->insert([
			'id' => $id,
			'name' => str_random(25),
			'description' => str_random(500),
			'url' => 'http://' . str_random(20) . '.com',
			'status' => 'open',
			'author_id' => $user->id,
			'account_id' => $account->id,
			'language' => str_random(3), //Maybe having a language list somewhere ?
			'repository' => str_random(5) . '/' . str_random('5'),
			'created_at' => \Carbon\Carbon::now(),
			'updated_at' => \Carbon\Carbon::now(),
		]);
	}

	private function addRandomTracking() {
		$user = DB::table('users')->inRandomOrder()->first();
		$request = DB::table('requests')->whereNotIn('author_id', [$user->id])->inRandomOrder()->first();
		$status = array_rand(['unapproved', 'approved']);

		DB::table('request_tracking')->insert([
			'user_id' => $user->id,
			'request_id' => $request->id,
			'status' => $status,
			'created_at' => \Carbon\Carbon::now(),
			'updated_at' => \Carbon\Carbon::now(),
		]);
	}

}
