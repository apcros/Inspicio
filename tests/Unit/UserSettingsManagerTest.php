<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class UserSettingsManagerTest extends TestCase {
	use DatabaseMigrations;

	public function testAutoImport() {
		$this->seed('DatabaseSeederForTests');

		$manager = new \App\UserSettingsManager('7636b30e-6db2-41b6-91b3-33560b9638c2');

		$approval_overdue = $manager->get('notify_approval_overdue');

		//Coming from default
		$this->assertEquals(1, $approval_overdue);

		//Create a new row
		$manager->set('notify_approval_overdue', 'top banana');
		$approval_overdue = $manager->get('notify_approval_overdue');
		// It's a type boolean so the value is casted
		$this->assertEquals(true, $approval_overdue);

		//Updating existing row
		$manager->set('notify_approval_overdue', '0');
		$approval_overdue = $manager->get('notify_approval_overdue');
		$this->assertEquals(false, $approval_overdue);

	}

}