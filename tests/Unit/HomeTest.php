<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class HomeTest extends TestCase {
	use DatabaseMigrations;
	private $user_review_id = 'e4dc3896-4a40-49b8-b3f2-0dc45916437a';

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
		$this->assertRegExp('/You need to accept the terms and conditions/', $content, 'ToS not accepted');

		$user_data['accept_tos'] = 'on';

		$response = $this->withSession(['user_nickname' => 'bob_git_nickname'])->post('/register', $user_data);
		$content  = $response->getContent();
		$this->assertRegExp('/Account created with success. You need to confirm your email. Check your inbox/', $content, 'Registered finished without errors');

		$this->assertDatabaseHas('users', [
			'email'        => 'amazingtest@testest.co.uk',
			'is_confirmed' => false,
		]);

		$this->assertDatabaseHas('accounts', [
			'login' => 'bob_git_nickname',
		]);

		//Monkey patching the user so that we don't have to know what the notification was
		//And to attempt a follow before the account is approved
		DB::table('users')->where('email', 'amazingtest@testest.co.uk')->update(['confirm_token' => 'blah']);
		$user = DB::table('users')->where('email', 'amazingtest@testest.co.uk')->first();

		$response = $this->withSession(['user_nickname' => $user->nickname, 'user_id' => $user->id, 'user_email' => $user->email])
			->json('POST', '/ajax/reviews/' . $this->user_review_id . '/track')
			->assertJson([
				'success' => 0,
				'message' => "Your account needs to be confirmed to do this (Check your inbox !)",
			]);
		$response = $this->get('/confirm/' . $user->id . '/blah');

		$this->assertDatabaseHas('users', [
			'email'        => 'amazingtest@testest.co.uk',
			'is_confirmed' => true,
		]);

	}

	public function testSearch() {
		$this->seed('DatabaseSeederForTests');

		$review = [
			'id'         => 'e4dc3896-4a40-49b8-b3f2-0dc45916437a',
			'name'       => 'Amazing code review request',
			'repository' => 'testuser/testrepo',
			'author'     => 'testuser',
			'language'   => '1C Enterprise'];

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
