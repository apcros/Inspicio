<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Notifications\ActionOnYourReview;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReviewRequestApi extends Controller {

	private $MINIMUM_TIME_BEFORE_APPROVAL = 120;

	public function approve($id) {
		$user_id              = session('user_id');
		list($review, $error) = $this->fetchReview($id);

		if (!$review) {
			return $this->apiResponse($error);
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

	private function fetchReview($id) {
		$valid_uuid = preg_match("/^(\{)?[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}(?(1)\})$/i", $id);

		if (!$valid_uuid) {
			return array(false, 'Invalid UUID');
		}

		try {
			$review = DB::table('requests')
				->join('users', 'requests.author_id', '=', 'users.id')
				->join('skills', 'requests.skill_id', '=', 'skills.id')
				->select('requests.*', 'users.nickname', 'skills.name as language')
				->orderBy('requests.updated_at', 'desc')
				->where('requests.id', $id)
				->first();

			return array($review, null);
		} catch (\Illuminate\Database\QueryException $e) {
			Log::error("[REVIEW $id] SQL error : " . $e->getMessage());

			return array(false, 'Database Error');
		}

	}

	private function apiResponse($message, $success = 0) {
		return response()->json([
			'success' => $success,
			'message' => $message,
		]);
	}

	private function notifyUserEmail($userid, $reviewid, $action) {
		list($review, $error) = $this->fetchReview($reviewid);

		if (!$review) {
			Log::error("Failed to notify $userid about $reviewid ($action) Because : $error");

			return 0;
		}

		$owner = DB::table('users')->where('id', $review->author_id)->first();
		$user  = DB::table('users')->where('id', $userid)->first();

		//TODO make eloquent models instead of re-using the default one in a horrible way
		$user_model        = new User();
		$user_model->email = $owner->email;

		$user_model->notify(new ActionOnYourReview($user, $review, $action));
	}

}
