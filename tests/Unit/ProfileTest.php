<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ProfileTest extends TestCase {
	use DatabaseMigrations;
	private $user_data = ['user_nickname' => 'testuser', 'user_id' => '7636b30e-6db2-41b6-91b3-33560b9638c2', 'user_email' => 'testuser@thisisatest.co.uk'];

	public function testLoadProfile() {
		$this->seed('DatabaseSeederForTests');

		$response = $this->get('/account');
		$response->assertStatus(302);

		$response = $this->withSession($this->user_data)->get('/account');
		$response->assertStatus(200);

		$content = $response->getContent();

		$this->assertRegExp('/John Doe/', $content, 'User account is displaying as expected');
	}
}