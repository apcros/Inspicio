<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ReviewTest extends TestCase {
	use DatabaseMigrations;

	public function testViewReview() {
		$this->seed('DatabaseSeederForTests');

		$response = $this->get('/reviews/e4dc3896-4a40-49b8-b3f2-0dc45916437a/view');

		$response->assertStatus(200);

		$content = $response->getContent();
		$this->assertRegExp('/Amazing code review request/', $content, 'Code Review request is loaded correctly');
	}
}
