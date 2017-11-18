<?php

namespace App\Http\Controllers;

use App\AutoImport;
use App\Http\Controllers\Controller;
use App\Notifications\ActionOnYourReview;
use App\ReviewRequest;
use App\User;
use App\UserSettingsManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReviewRequestApi extends Controller {

	private $MINIMUM_TIME_BEFORE_APPROVAL = 120;

	/*
		This will approve the ReviewRequest passed as parameters.
		This will act as the user set in the session
		It will check the time at which the review request has been followed
		and refuse to approve it if it's less than the above
		MINIMUM_TIME_BEFORE_APPROVAL (In seconds)

		$id : The GUID of the Review Request to approve
	*/
	public function approve($id) {
		$user_id                = session('user_id');
		$review_request         = new \App\ReviewRequest($user_id);
		list($success, $review) = $review_request->load($id);

		if (!$success) {
			return $this->apiResponse($review);
		}

		if ($review->author_id == $user_id) {
			Log::warning("[USER $user_id] Attempted to approve his own review ($id)");

			return $this->apiResponse("You can't approve your own review requests");
		}

		try {
			$tracking = DB::table('request_tracking')->where([
				['user_id', '=', $user_id],
				['request_id', '=', $id],
				['is_active', '=', true],
			])->first();

			if (!$tracking) {
				return $this->apiResponse("You can't approve a review request you don't follow");
			}

			if ($tracking->is_approved) {
				return $this->apiResponse("You already approved this review request");
			}

			$time_since_creation = time() - strtotime($tracking->created_at);

			if ($time_since_creation < $this->MINIMUM_TIME_BEFORE_APPROVAL) {
				return $this->apiResponse("You can't approve a review request you followed less than 2 minutes ago");
			}

			DB::table('request_tracking')->where([
				['user_id', '=', $user_id],
				['request_id', '=', $id],
			])->update(['is_approved' => true]);

			DB::table('users')->where('id', session('user_id'))
				->increment('points');

			$this->notifyUserEmail($user_id, $id, 'approved');

		} catch (\Illuminate\Database\QueryException $e) {
			Log::error('[USER ' . session('user_id') . '] SQL error for review ' . $id . ' : ' . $e->getMessage());

			return $this->apiResponse("Error while trying to approve the review request");
		}

		Log::info("[USER $user_id] Review $id approved");

		return $this->apiResponse("Successfully approved (+1 point)", 1);
	}

	public function getpermissions($id) {
		$user_id = session('user_id');
		$review_request         = new \App\ReviewRequest($user_id);
		list($success, $review) = $review_request->load($id);

		if (!$success) {
			return $this->apiResponse($review);
		}

		$permissions = [
			'review_id'   => $id,
			'review_url'  => $review->url,
			'is_owner' 	  => false,
			'is_open' 	  => false,
			'is_followed' => false,
			'is_logged_in'   => false,
			'is_approved' => false
		];

		if(!$user_id) {
			return $this->apiResponse($permissions, 1);
		}
		$permissions['is_logged_in'] = true;
	
		if($review->status == 'open') {
			$permissions['is_open'] = true;
		}

		if($review->author_id == $user_id) {
			$permissions['is_owner'] = true;
			return $this->apiResponse($permissions,1);
		}


		$tracking = DB::table('request_tracking')->where([
				['user_id', '=', $user_id],
				['request_id', '=', $id],
			])->first();

		if(!$tracking){
			return $this->apiResponse($permissions, 1);
		}

		$permissions['is_followed'] = $tracking->is_active;
		$permissions['is_approved'] = $tracking->is_approved;

		return $this->apiResponse($permissions, 1);

	}

	/*
		Will enable or disable AutoImport entries for the current user
	*/
	public function updateAutoImport($id, Request $request) {
		$enabled = $request->input('enabled');
		$user_id = session('user_id');

		$auto_import             = new \App\AutoImport();
		list($is_success, $data) = $auto_import->update($id, $user_id, $enabled);

		if (!$is_success) {
			return $this->apiResponse($data);
		}

		return $this->apiResponse($data, 1);

	}

	/*
		Unfollow a ReviewRequest by disabling the request_tracking
		entry.
	*/
	public function untrack($id) {
		$user_id        = session('user_id');
		$review_request = new \App\ReviewRequest($user_id);

		list($success, $review) = $review_request->load($id);

		if (!$success) {
			return $this->apiResponse($review);
		}

		try {
			$tracking = DB::table('request_tracking')
				->where([
					['user_id', '=', $user_id],
					['request_id', '=', $id],
					['is_active', '=', true],
				])->first();

			if (!$tracking) {
				return $this->apiResponse('You were not following this review request');
			}

			DB::table('request_tracking')->where([
				['user_id', '=', $user_id],
				['request_id', '=', $id],
			])->update(['is_active' => false]);

		} catch (\Illuminate\Database\QueryException $e) {
			Log::error("[USER $user_id ] SQL Error caught when unfollowing  $id : " . $e->getMessage());

			return $this->apiResponse('An error ocurred !');
		}

		Log::info("[USER $user_id ] unfollowed $id");

		return $this->apiResponse('Review request unfollowed', 1);

	}

	/*
		On every available GIT accounts for the current user, this will
		list the repositories and for each repository it will list the pull requests
		(This is used mainly on the AutoImport setup, to get immediately a list of
		PR ready to be imported)
		PRs will be returned ordered per Repo
	*/
	public function listAllAvailablePrsForImport() {
		$user_id = session('user_id');

		$user     = new User($user_id);
		$accounts = $user->getAvailableAccounts();

		$repos_available = [];

		Log::info("[USER ID $user_id ] Fetching repo and PR for " . count($accounts) . " accounts");

		foreach ($accounts as $available_account) {

			$account = $user->getGitAccount($available_account->id);
			$client  = $user->getAccountClient($account);

			$repositories = $client->listRepositories();

			foreach ($repositories as $repository) {
				list($owner, $repository_name) = explode('/', $repository->name);
				Log::info("[USER ID $user_id] Fetching $owner/$repository_name prs..");
				$open_prs = $client->listPullRequestsForRepo($owner, $repository_name);

				if (count($open_prs) > 0) {
					$repos_available[] = [
						'object'        => $repository,
						'account_id'    => $account->id,
						'pull_requests' => $open_prs,
					];
				}

			}

		}

		$user = DB::table('users')->where('id', $user_id)->first();

		return $this->apiResponse([
			'repositories' => $repos_available,
			'points'       => $user->points,
		], 1);
	}

	/*
		Will re-enable existing tracking if active user
		unfollowed the Review request precendetly,
		otherwise it'll create a new entry and notify the
		PR owner.
	*/
	public function track($id) {
		$user_id                = session('user_id');
		$review_request         = new \App\ReviewRequest($user_id);
		list($success, $review) = $review_request->load($id);

		if (!$success) {
			return $this->apiResponse($review);
		}

		if ($review->author_id == $user_id) {
			Log::warning("[USER $user_id ] Attempted to follow his own review ($id)");

			return $this->apiResponse("You can't follow your own review requests");
		}

		try {
			$tracking = DB::table('request_tracking')->where([
				['user_id', '=', $user_id],
				['request_id', '=', $id],
			])->first();

			if ($tracking) {
				//$tracking will be set if the review was followed and then unfollowed
				DB::table('request_tracking')->where([
					['user_id', '=', $user_id],
					['request_id', '=', $id],
				])->update(['is_active' => true]);
			} else {

				DB::table('request_tracking')->insert([
					'user_id'     => $user_id,
					'request_id'  => $id,
					'is_active'   => true,
					'is_approved' => false,
					'created_at'  => \Carbon\Carbon::now(),
					'updated_at'  => \Carbon\Carbon::now(),
				]);
				$this->notifyUserEmail(session('user_id'), $id, 'followed');
			}

		} catch (\Illuminate\Database\QueryException $e) {
			Log::error("[USER $user_id ] SQL Error caught when following  $id : " . $e->getMessage());

			return $this->apiResponse('An error ocurred !');
		}

		Log::info("[USER $user_id ] followed $id");

		return $this->apiResponse('You are now following this review request', 1);

	}

	public function reopen($id) {
		return $this->changeReviewStatus($id, 'open');
	}

	public function close($id) {
		return $this->changeReviewStatus($id, 'closed');
	}

	/*
		Change the status of the ReviewRequest identified
		by $id to $status
		(Will act as the user set in the session)
	*/
	private function changeReviewStatus($id, $status) {
		$user_id                = session('user_id');
		$review_request         = new \App\ReviewRequest($user_id);
		list($success, $review) = $review_request->load($id);

		if (!$success) {
			return $this->apiResponse($review);
		}

		$user_id = session('user_id');

		if ($review->author_id != $user_id) {
			Log::warning("[USER " . session('user_id') . "] Attempted to change someone else review ($id) to $status");

			return $this->apiResponse('You can only update the status of your own review requests');
		}

		try {
			DB::table('requests')->where('id', $review->id)
				->update([
					'status'     => $status,
					'updated_at' => \Carbon\Carbon::now(),
				]);
		} catch (\Illuminate\Database\QueryException $e) {
			Log::error("[USER $user_id ] SQL Error when changing status for code review $id (new status : $status ) : " . $e->getMessage());

			return $this->apiResponse('An error ocurred !');
		}

		return $this->apiResponse("Code review status changed to $status", 1);

	}

	private function apiResponse($message, $success = 0) {
		return response()->json([
			'success' => $success,
			'message' => $message,
		]);
	}

	/*
		Notify owner of a review of action on his review
		(Follow or Approval) if he enabled the option
		on his account
	*/
	private function notifyUserEmail($userid, $reviewid, $action) {
		$user_id                = session('user_id');
		$review_request         = new \App\ReviewRequest($user_id);
		list($success, $review) = $review_request->load($reviewid);

		if (!$success) {
			Log::error("Failed to notify $userid about $reviewid ($action) Because : $review");

			return 0;
		}

		$owner = DB::table('users')->where('id', $review->author_id)->first();
		$user  = DB::table('users')->where('id', $userid)->first();

		$user_model        = new User($owner->id);
		$user_model->email = $owner->email;
		$settings_mngr     = new UserSettingsManager($owner->id);

		if (
			($settings_mngr->get('notify_follows') == 1 && $action == 'followed')
			||
			($settings_mngr->get('notify_approvals') == 1 && $action == 'approved')
		) {
			$user_model->notify(new ActionOnYourReview($user, $review, $action));
		}

	}

}
