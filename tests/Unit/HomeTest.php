<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class HomeTest extends TestCase {
	use DatabaseMigrations;

	public function testDiscoverLogged() {
		$response = $this->withSession(['user_nickname' => 'testuser', 'user_id' => '7636b30e-6db2-41b6-91b3-33560b9638c2', 'user_email' => 'testuser@thisisatest.co.uk'])
			->get('/');

		$response->assertStatus(200);

		$content = $response->getContent();
		$this->assertRegExp('/testuser\@thisisatest\.co\.uk/', $content, 'The user can access his account');
	}

	public function testDiscoverNotLogged() {
		$response = $this->get('/');

		$response->assertStatus(200);

		$content = $response->getContent();
		$this->assertRegExp('/Login/', $content, 'There is a login button on the page');
	}

	public function testRegister() {

		$user_data = [
			'email'         => 'amazingtest@testest.co.uk',
			'name'          => 'bob',
			'auth_token'    => 'dummy',
			'auth_provider' => 'github',
		];

		$response = $this->post('/register', $user_data);

		$content = $response->getContent();
		$this->assertRegExp('/try again/', $content, 'No session cause a failure');

		$response = $this->withSession(['user_nickname' => 'bob_git_nickname'])->post('/register', $user_data);
		$content  = $response->getContent();
		$this->assertRegExp('/Redirecting to/', $content, 'Registered finished withotu errors');

		$this->assertDatabaseHas('users', [
			'email' => 'amazingtest@testest.co.uk',
		]);
		$this->assertDatabaseHas('accounts', [
			'login' => 'bob_git_nickname',
		]);
	}
}
