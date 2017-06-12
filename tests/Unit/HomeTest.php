<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Notification;
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
		Notification::fake();
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
		$this->assertRegExp('/Redirecting to/', $content, 'Registered finished without errors');

		$this->assertDatabaseHas('users', [
			'email' => 'amazingtest@testest.co.uk',
		]);
		$this->assertDatabaseHas('accounts', [
			'login' => 'bob_git_nickname',
		]);
	}

	public function testSearch() {
		$this->seed('DatabaseSeederForTests');

		$review = [
			'id'         => 'e4dc3896-4a40-49b8-b3f2-0dc45916437a',
			'name'       => 'Amazing code review request',
			'repository' => 'testuser/testrepo',
			'author'     => 'testuser',
			'language'   => 'A++'];

		$this->json('POST', '/api/reviews/search',
			['filters' => [
				'query'     => 'NOT FOUNDS',
				'languages' => [],
			],
			])->assertJson(['success' => 1, 'reviews' => []]);

		$this->json('POST', '/api/reviews/search',
			['filters' => [
				'query'     => 'NOT FOUNDS',
				'languages' => [1, 2],
			],
			])->assertJson(['success' => 1, 'reviews' => []]);

		$this->json('POST', '/api/reviews/search',
			['filters' => [
				'query'     => '',
				'languages' => [1, 2],
			],
			])->assertJson(['success' => 1, 'reviews' => [$review]]);

		$this->json('POST', '/api/reviews/search',
			['filters' => [
				'query'     => 'Amazing',
				'languages' => [],
			],
			])->assertJson(['success' => 1, 'reviews' => [$review]]);
	}
}
