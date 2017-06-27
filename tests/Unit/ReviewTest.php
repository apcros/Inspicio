<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
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

		$response = $this->withSession($this->user_data_bis)->get('/reviews/blah/view');
		$content  = $response->getContent();
		$this->assertNotRegExp('/Not Found/', $content, 'PR not found');
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
			'description'  => '<p>Hello I am a review</p><h2>And I am a title</h2><script>alert("And I am a nasty script that should be ditched"</script><div>My div will be ditched</div>',
			'language'     => 1,
		]);

		$response->assertStatus(302);
		$content = $response->getContent();
		$this->assertRegExp('/Redirecting to/', $content, 'Creating review finished without errors');

		$this->assertDatabaseHas('requests', [
			'name'        => 'Another greate code review',
			'repository'  => 'supertest/amazing',
			'description' => '<p>Hello I am a review</p><h2>And I am a title</h2>My div will be ditched',
			'skill_id'    => 1,
			'author_id'   => $this->user_data['user_id'],
			'account_id'  => $this->user_account_id,
		]);

		$this->assertDatabaseHas('users', [
			'id'     => $this->user_data['user_id'],
			'points' => 4,
		]);
	}

	public function testEditReview() {
		$this->seed('DatabaseSeederForTests');

		$response = $this->withSession($this->user_data_bis)->get('/reviews/' . $this->user_review_id . '/edit');
		$content  = $response->getContent();
		$this->assertRegExp("/You can&#039;t edit someone else code review request/", $content, "Can't edit someone else PR");

		$response = $this->withSession($this->user_data_bis)->post('/reviews/' . $this->user_review_id . '/edit', [
			'title'       => 'blah',
			'description' => 'blah',
			'language'    => 2,
		]);
		$content = $response->getContent();
		$this->assertRegExp("/You can&#039;t edit someone else code review request/", $content, "Can't edit someone else PR");

		$response = $this->withSession($this->user_data)->get('/reviews/' . $this->user_review_id . '/edit');
		$content  = $response->getContent();
		$this->assertRegExp("/Wow this is a description/", $content, "Description is populated");
		$this->assertRegExp("/Amazing code review request/", $content, "Title is populated");

		$response = $this->withSession($this->user_data)->post('/reviews/' . $this->user_review_id . '/edit', [
			'title'       => 'Amazing a new title',
			'description' => '<p><b>Loook at meeeeee</b></p><script>alert("And I am a nasty script that should be ditched"</script>',
			'language'    => 2,
		]);
		$content = $response->getContent();
		$this->assertRegExp("/Updated code review request on Inspicio, Not updated on Github/", $content, "PR updated and message is correct");
		$this->assertDatabaseHas('requests', [
			'name'        => 'Amazing a new title',
			'description' => '<p><b>Loook at meeeeee</b></p>',
			'skill_id'    => 2,
			'id'          => $this->user_review_id,
		]);

		$response = $this->withSession($this->user_data)->post('/reviews/' . $this->user_review_id . '/edit', [
			'title'       => 'Amazing a new title',
			'description' => '<p><b>Loook at meeeeee</b></p><script>alert("And I am a nasty script that should be ditched"</script>',
			'language'    => 2,
			'update_on_git'  => 'on',
		]);
		$content = $response->getContent();
		$this->assertRegExp("/Pull request url invalid/", $content, "PR updated and message is correct");

	}

}