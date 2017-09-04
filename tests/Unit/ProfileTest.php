<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Notification;
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

	public function testLoadPublicProfile() {
		$this->seed('DatabaseSeederForTests');

		$response = $this->get('/members/7636b30e-6db2-41b6-91b3-33560b9638c2/profile');
		$response->assertStatus(200);

		$content = $response->getContent();
		$this->assertRegExp('/John Doe/', $content, 'User profile is displayed as expected');
		$this->assertRegExp('/e4dc3896-4a40-49b8-b3f2-0dc45916437a/', $content, 'There is a link to the user code review request');

		$response = $this->get('/members/00000000-0000-0000-0000-000000000000/profile');
		$content  = $response->getContent();
		$this->assertRegExp('/User not found/', $content, 'Error message returned sucessfully');

		$response = $this->get('/members/blah/profile');
		$content  = $response->getContent();
		$this->assertRegExp('/User not found/', $content, 'Error message returned sucessfully when uuid representation is invalid');
	}

	public function testUpdateProfile() {
		$this->seed('DatabaseSeederForTests');
		Notification::fake();

		$response = $this->withSession($this->user_data)->post('/account', [
			'email' => 'wowanewemail@fakefakefake.com',
			'name'  => 'MOUNDIIIIIR',
		]);
		$response->assertStatus(302);

		$this->assertDatabaseHas('users', [
			'name'         => 'MOUNDIIIIIR',
			'email'        => 'wowanewemail@fakefakefake.com',
			'id'           => $this->user_data['user_id'],
			'is_confirmed' => false,
		]);

	}
	public function testSkill() {
		$this->seed('DatabaseSeederForTests');

		$this->withSession($this->user_data)
			->json('POST', '/ajax/account/skills', ['skill' => 1, 'level' => 1])
			->assertJson([
				'success' => 1,
				'message' => 'Skill added with success',
			]);

		$this->assertDatabaseHas('user_skills', [
			'user_id'  => $this->user_data['user_id'],
			'skill_id' => 1,
		]);

		$this->withSession($this->user_data)
			->json('POST', '/ajax/account/skills', ['skill' => 1, 'level' => 1])
			->assertJson([
				'success' => 0,
				'message' => 'You already have that skill',
			]);

		$this->withSession($this->user_data)
			->json('POST', '/ajax/account/skills/1/delete')
			->assertJson([
				'success' => 1,
				'message' => 'Skill deleted',
			]);
	}

	public function testSettings() {
		$this->seed('DatabaseSeederForTests');

		$response = $this->withSession($this->user_data)->get('/account');
		$response->assertStatus(200);
		$content = $response->getContent();
		$this->assertRegExp('/name="setting_notify_approval_overdue" checked="checked">/', $content, 'User settings are correct');

		$this->withSession($this->user_data)
			->json('POST', '/ajax/settings', ['settings' => [
				[
					'key'   => 'notify_approval_overdue',
					'value' => 'false',
				],
			]])->assertJson([
			'success' => 1,
			'message' => 'Settings updated',
		]);

		$response = $this->withSession($this->user_data)->get('/account');
		$response->assertStatus(200);
		$content = $response->getContent();
		$this->assertRegExp('/name="setting_notify_approval_overdue">/', $content, 'User settings were updated');
	}
}