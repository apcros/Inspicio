<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ReviewApiTest extends TestCase {
	use DatabaseMigrations;
	private $user_data      = ['user_nickname' => 'testuser', 'user_id' => '7636b30e-6db2-41b6-91b3-33560b9638c2', 'user_email' => 'testuser@thisisatest.co.uk'];
	private $user_data_bis  = ['user_nickname' => 'testuser2', 'user_id' => 'e6ca8f33-b196-4006-b7e6-3f2ffef3df92', 'user_email' => 'testuser2@thisisatest.co.uk'];
	private $user_review_id = 'e4dc3896-4a40-49b8-b3f2-0dc45916437a';

	public function testTrackAndApproval() {
		$this->seed('DatabaseSeederForTests');
		Notification::fake();

		$response = $this->withSession($this->user_data_bis)
			->json('POST', '/ajax/reviews/' . $this->user_review_id . '/approve')
			->assertJson([
				'success' => 0,
				'message' => "You can't approve a review request you don't follow",
			]);

		$response = $this->withSession($this->user_data_bis)
			->json('POST', '/ajax/reviews/' . $this->user_review_id . '/track')
			->assertJson([
				'success' => 1,
				'message' => "You are now following this review request",
			]);

		$this->assertDatabaseHas('request_tracking', [
			'user_id'     => $this->user_data_bis['user_id'],
			'request_id'  => $this->user_review_id,
			'is_approved' => false,
			'is_active'   => 1,
		]);

		$response = $this->withSession($this->user_data_bis)
			->json('POST', '/ajax/reviews/' . $this->user_review_id . '/approve')
			->assertJson([
				'success' => 0,
				'message' => "You can't approve a review request you followed less than 2 minutes ago",
			]);

		//Editing the DB directly is not great, but it works. More elegant solutions welcome
		DB::table('request_tracking')->where([
			['user_id', '=', $this->user_data_bis['user_id']],
			['request_id', '=', $this->user_review_id],
		])->update(['created_at' => \Carbon\Carbon::yesterday()]);

		$response = $this->withSession($this->user_data_bis)
			->json('POST', '/ajax/reviews/' . $this->user_review_id . '/approve')
			->assertJson([
				'success' => 1,
				'message' => "Successfully approved (+1 point)",
			]);

		$response = $this->withSession($this->user_data_bis)
			->json('POST', '/ajax/reviews/' . $this->user_review_id . '/approve')
			->assertJson([
				'success' => 0,
				'message' => "You already approved this review request",
			]);

		$this->assertDatabaseHas('request_tracking', [
			'user_id'     => $this->user_data_bis['user_id'],
			'request_id'  => $this->user_review_id,
			'is_approved' => true,
		]);

		$response = $this->withSession($this->user_data_bis)->get('/reviews/tracked');
		$response->assertStatus(200);
		$content = $response->getContent();
		$this->assertRegExp('/' . $this->user_review_id . '/', $content, 'Review request is displayed correctly on the tracked page');

		$response = $this->withSession($this->user_data_bis)
			->json('POST', '/ajax/reviews/' . $this->user_review_id . '/untrack')
			->assertJson([
				'success' => 1,
				'message' => "Review request unfollowed",
			]);

		$this->assertDatabaseHas('request_tracking', [
			'user_id'     => $this->user_data_bis['user_id'],
			'request_id'  => $this->user_review_id,
			'is_approved' => true,
			'is_active'   => false,
		]);

		$response = $this->withSession($this->user_data_bis)
			->json('POST', '/ajax/reviews/' . $this->user_review_id . '/untrack')
			->assertJson([
				'success' => 0,
				'message' => "You were not following this review request",
			]);
		$this->assertDatabaseHas('users', [
			'id'     => $this->user_data_bis['user_id'],
			'points' => 6,
		]);

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

	public function testChangeStatus() {
		$this->seed('DatabaseSeederForTests');
		$this->withSession($this->user_data_bis)
			->json('POST', '/ajax/reviews/' . $this->user_review_id . '/close')
			->assertJson([
				'success' => 0,
				'message' => 'You can only update the status of your own review requests',
			]);

		$this->withSession($this->user_data)
			->json('POST', '/ajax/reviews/' . $this->user_review_id . '/close')
			->assertJson([
				'success' => 1,
				'message' => 'Code review status changed to closed',
			]);

		$this->assertDatabaseHas('requests', [
			'status' => 'closed',
			'id'     => $this->user_review_id,
		]);

		$this->withSession($this->user_data)
			->json('POST', '/ajax/reviews/' . $this->user_review_id . '/reopen')
			->assertJson([
				'success' => 1,
				'message' => 'Code review status changed to open',
			]);

		$this->assertDatabaseHas('requests', [
			'status' => 'open',
			'id'     => $this->user_review_id,
		]);
	}
}