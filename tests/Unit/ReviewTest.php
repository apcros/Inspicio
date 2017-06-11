<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ReviewTest extends TestCase {
	use DatabaseMigrations;

	private $user_data       = ['user_nickname' => 'testuser', 'user_id' => '7636b30e-6db2-41b6-91b3-33560b9638c2', 'user_email' => 'testuser@thisisatest.co.uk'];
	private $user_data_bis   = ['user_nickname' => 'testuser2', 'user_id' => 'e6ca8f33-b196-4006-b7e6-3f2ffef3df92', 'user_email' => 'testuser2@thisisatest.co.uk'];
	private $user_review_id  = 'e4dc3896-4a40-49b8-b3f2-0dc45916437a';
	private $user_account_id = '4cff704c-efe4-4024-9fa1-e5c0d7eaf2c5';

	public function testViewReview() {
		$this->seed('DatabaseSeederForTests');

		$response = $this->withSession($this->user_data_bis)->get('/reviews/' . $this->user_review_id . '/view');

		$response->assertStatus(200);

		$content = $response->getContent();
		$this->assertRegExp('/Amazing code review request/', $content, 'Code Review request is loaded correctly');
		$this->assertRegExp('/Follow/', $content, 'There\'s the button to follow the code review');

		$response = $this->withSession($this->user_data)->get('/reviews/' . $this->user_review_id . '/view');
		$response->assertStatus(200);

		$content = $response->getContent();
		$this->assertNotRegExp('/Follow/', $content, 'No follow button on your own code review request');
	}

	public function testListMyReviews() {
		$this->seed('DatabaseSeederForTests');

		$response = $this->withSession($this->user_data)->get('/reviews/mine');
		$response->assertStatus(200);

		$content = $response->getContent();
		$this->assertRegExp('/' . $this->user_review_id . '/', $content, 'Reviews Requests listed correctly');
	}
	public function testCreateReview() {
		$this->seed('DatabaseSeederForTests');

		$response = $this->withSession($this->user_data)->post('/reviews/create', [
			'title'        => 'Another greate code review',
			'repository'   => 'supertest/amazing,' . $this->user_account_id,
			'pull_request' => 'http://dummyurl.com',
			'description'  => 'Wow great',
			'language'     => 1,
		]);

		$response->assertStatus(302);
		$content = $response->getContent();
		$this->assertRegExp('/Redirecting to/', $content, 'Creating review finished without errors');

		$this->assertDatabaseHas('requests', [
			'name'        => 'Another greate code review',
			'repository'  => 'supertest/amazing',
			'description' => 'Wow great',
			'skill_id'    => 1,
			'author_id'   => $this->user_data['user_id'],
			'account_id'  => $this->user_account_id,
		]);

		$this->assertDatabaseHas('users', [
			'id'     => $this->user_data['user_id'],
			'points' => 4,
		]);
	}

	public function testTrackAndApproval() {
		$this->seed('DatabaseSeederForTests');
		Notification::fake();

		$response = $this->withSession($this->user_data_bis)
			->json('POST', '/ajax/reviews/' . $this->user_review_id . '/track')
			->assertJson([
				'success' => 1,
				'message' => "You are now following this review request",
			]);

		$this->assertDatabaseHas('request_tracking', [
			'user_id'    => $this->user_data_bis['user_id'],
			'request_id' => $this->user_review_id,
			'status'     => 'unapproved',
		]);

		$response = $this->withSession($this->user_data_bis)
			->json('POST', '/ajax/reviews/' . $this->user_review_id . '/approve')
			->assertJson([
				'success' => 1,
				'message' => "Successfully approved (+1 point)",
			]);

		$this->assertDatabaseHas('request_tracking', [
			'user_id'    => $this->user_data_bis['user_id'],
			'request_id' => $this->user_review_id,
			'status'     => 'approved',
		]);

		$this->assertDatabaseHas('users', [
			'id'     => $this->user_data_bis['user_id'],
			'points' => 6,
		]);

		$response = $this->withSession($this->user_data_bis)->get('/reviews/tracked');
		$response->assertStatus(200);
		$content = $response->getContent();
		$this->assertRegExp('/' . $this->user_review_id . '/', $content, 'Review request is displayed correctly on the tracked page');
	}

	public function testTrackAndApprovalFail() {
		$this->seed('DatabaseSeederForTests');
		$response = $this->withSession($this->user_data)
			->json('POST', '/ajax/reviews/' . $this->user_review_id . '/track')
			->assertJson([
				'success' => 0,
				'message' => "You can't follow your own review requests",
			]);

		$response = $this->withSession($this->user_data)
			->json('POST', '/ajax/reviews/' . $this->user_review_id . '/approve')
			->assertJson([
				'success' => 0,
				'message' => "You can't approve your own review requests",
			]);
	}

	public function testClose() {
		$this->seed('DatabaseSeederForTests');
		$this->withSession($this->user_data_bis)
			->json('POST', '/ajax/reviews/' . $this->user_review_id . '/close')
			->assertJson([
				'success' => 0,
				'message' => 'You can only close your own review requests',
			]);
		$this->withSession($this->user_data)
			->json('POST', '/ajax/reviews/' . $this->user_review_id . '/close')
			->assertJson([
				'success' => 1,
				'message' => 'Code review closed',
			]);
	}
}