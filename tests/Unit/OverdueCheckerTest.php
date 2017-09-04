<?php

namespace Tests\Unit;

use App\OverdueChecker;
use App\User;
use App\UserSettingsManager;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OverdueCheckerTest extends TestCase {
	use DatabaseMigrations;

	public function testOverdueCheck() {
		$this->seed('DatabaseSeederForOverdueTests');

		Notification::fake();

		$overdue_checker = new \App\OverdueChecker();
		$overdue_checker->run();

		$author                  = new User('7636b30e-6db2-41b6-91b3-33560b9638c2');
		$author->email           = 'testuser@thisisatest.co.uk';
		$author_settings_manager = new UserSettingsManager('7636b30e-6db2-41b6-91b3-33560b9638c2');

		$user                  = new User('e6ca8f33-b196-4006-b7e6-3f2ffef3df92');
		$user->email           = 'testuser2@thisisatest.co.uk';
		$user_settings_manager = new UserSettingsManager('e6ca8f33-b196-4006-b7e6-3f2ffef3df92');

		Notification::assertSentTo(
			$author,
			\App\Notifications\ReviewOpenedTooLong::class
		);

		Notification::assertNotSentTo(
			$author,
			\App\Notifications\ReviewFollowedForTooLong::class
		);

		Notification::assertNotSentTo(
			$user,
			\App\Notifications\ReviewOpenedTooLong::class
		);

		Notification::assertSentTo(
			$user,
			\App\Notifications\ReviewFollowedForTooLong::class
		);

		Notification::fake();

		$author_settings_manager->set('notify_closing_overdue', false);
		$overdue_checker->run();

		Notification::assertNotSentTo(
			$author,
			\App\Notifications\ReviewOpenedTooLong::class
		);

		Notification::assertNotSentTo(
			$author,
			\App\Notifications\ReviewFollowedForTooLong::class
		);

		Notification::assertNotSentTo(
			$user,
			\App\Notifications\ReviewOpenedTooLong::class
		);

		Notification::assertSentTo(
			$user,
			\App\Notifications\ReviewFollowedForTooLong::class
		);

		$author_settings_manager->set('notify_closing_overdue', true);
		$user_settings_manager->set('notify_approval_overdue', false);
		Notification::fake();
		$overdue_checker->run();

		Notification::assertSentTo(
			$author,
			\App\Notifications\ReviewOpenedTooLong::class
		);

		Notification::assertNotSentTo(
			$author,
			\App\Notifications\ReviewFollowedForTooLong::class
		);

		Notification::assertNotSentTo(
			$user,
			\App\Notifications\ReviewOpenedTooLong::class
		);

		Notification::assertNotSentTo(
			$user,
			\App\Notifications\ReviewFollowedForTooLong::class
		);

	}

}