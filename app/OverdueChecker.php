<?php

namespace App;

use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OverdueChecker {
	private $to_alert;
	private $dry_run;

	public function __construct($dry_run = false) {
		$this->dry_run = $dry_run;
	}

	public function run() {

		$this->generateRecipientList();

		foreach ($this->to_alert as $notification) {
			$user              = $notification['user'];
			$notification_type = '\App\Notifications\\' . $notification['type'];

			$user_model        = new User($user->id);
			$user_model->email = $user->email;
			Log::info("Notifying " . $user->email . " (" . $user->id . ") with " . $notification_type . " impacting " . count($notification['reviews_impacted']) . " reviews");

//If it's a dry run, we don't want to notify the users
			if (!$this->dry_run) {
				$user_model->notify(new $notification_type($user, $notification['reviews_impacted']));
			}

		}

	}

	/*
					We group email if someone have several reviews impacted, because everyone hates
					email spam
				*/
	private function addToNotificationList($user, $type, $review) {

		if (!isset($this->to_alert[$user->id])) {

			$this->to_alert[$user->id] = [
				'user'             => $user,
				'reviews_impacted' => [$review],
				'type'             => $type,
			];
		} else {
			$to_alert[$user->id]['reviews_impacted'][] = $review;
		}

	}

	private function generateRecipientList() {
		$this->to_alert = [];
		$reviews        = DB::table('requests')->where('status', 'open')->get();

		foreach ($reviews as $review) {
			$pending_approvals = DB::table('request_tracking')->where([
				['is_active', '=', true],
				['is_approved', '=', false],
				['request_id', '=', $review->id],
			])->get();

/*
We can't blame the author for not closing his PR if he's still
waiting on approvals.
But we will remind theses users to approve
 */
			if ($pending_approvals) {
				foreach ($pending_approvals as $pending_approval) {
					if ($pending_approval->updated_at <= \Carbon\Carbon::now()->subWeeks(2)) {
						$user = DB::table('users')->where('id', $pending_approval->user_id)->first();
						$this->addToNotificationList($user, 'ReviewFollowedForTooLong', $review);
					}

				}

			}

			/* We don't want to blame the author if he doens't have any follower either.
			 (We might send him some tips later ?) */
			$followers = DB::table('request_tracking')->where('request_id', $review->id)->get();

			if ($followers && $review->updated_at <= \Carbon\Carbon::now()->subWeeks(2)) {
				$author = DB::table('users')->where('id', $review->author_id)->first();
				$this->addToNotificationList($author, 'ReviewOpenedTooLong', $review);
			}

		}

	}

}
