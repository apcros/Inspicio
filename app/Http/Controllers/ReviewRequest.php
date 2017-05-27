<?php

namespace App\Http\Controllers;

use App\Classes\GitProviderFactory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use \Ramsey\Uuid\Uuid;

//TODO : Remvoe code duplication caused by the :

// - Check if exist or return

// - Run query or return
// - Return val
class ReviewRequest extends Controller {

	public function approve(Request $request, $reviewid) {
		//TODO : Maybe move getReview to a middleware ?
		$review = $this->getReview($reviewid);

		if (!$review) {
			return response()->json([
				'success' => 0,
				'message' => 'Review Request not found !',
			]);
		}

		$user_id = session('user_id');

		if ($review->author_id == $user_id) {
			return response()->json([
				'success' => 0,
				'message' => 'You can\'t approve your own review requests',
			]);
		}

		try {
			DB::table('request_tracking')->where([
				['user_id', '=', $user_id],
				['request_id', '=', $reviewid],
			])->update(['status' => 'approved']);
			$this->addPoint();

		} catch (\Illuminate\Database\QueryException $e) {
			Log::error('Error when USER ' . session('user_id') . ' attempted to approve code review ' . $reviewid . ' : ' . $e->getMessage());

			return response()->json([
				'success' => 0,
				'message' => 'Error while trying to approve the review request',
			]);
		}

		return response()->json([
			'success' => 1,
			'message' => 'Successfully approved (+1 point)',
		]);
	}

	public function create(Request $request) {
		$title              = $request->input('title');
		$repository_account = $request->input('repository');
		$language           = $request->input('language');
		$pull_request_url   = $request->input('pull_request');
		$description        = $request->input('description');

		if ($this->getPoints() == 0) {
			return view('home', ['error_message' => "You don't have any points left. Please review someone else code to get points"]);
		}

		list($owner_repo, $account_id) = explode(',', $repository_account);

		$account = DB::table('accounts')->where([
			['user_id', '=', session('user_id')],
			['id', '=', $account_id]])->first();

		if (!$account) {
			return view('home', ['error_message' => 'Unexpected error']);
		}

		if (!$pull_request_url) {

			$head_branch = $request->input('head_branch');
			$base_branch = $request->input('base_branch');

			$client = $this->getClient($account->provider);
			$client->setToken($account->token);

			list($owner, $repo) = explode('/', $owner_repo);

			$pr_result = $client->createPullRequest($owner, $repo, $head_branch, $base_branch, $title, $description);

			if ($pr_result['success'] == 0 || !isset($pr_result['url'])) {
				return view('home', ['error_message' => 'Error while creating your code review request : ' . $pr_result['error']]);
			}

			$pull_request_url = $pr_result['url'];
		}

		$review_request_id = Uuid::uuid4()->toString();

		try {
			DB::table('requests')->insert([
				'id'          => $review_request_id,
				'name'        => $title,
				'description' => $description,
				'url'         => $pull_request_url,
				'status'      => 'open',
				'skill_id'    => $language,
				'author_id'   => session('user_id'),
				'repository'  => $owner_repo,
				'account_id'  => $account_id,
				'created_at'  => \Carbon\Carbon::now(),
				'updated_at'  => \Carbon\Carbon::now(),
			]);
			$this->removePoint();
		} catch (\Illuminate\Database\QueryException $e) {
			Log::error("Error caught while adding Pull request : " . $e->getMessage());

			return view('home', ['error_message' => 'An error ocurred while trying to add your review request']);
		}

		return redirect('/reviews/' . $review_request_id . '/view');
	}

	public function createForm(Request $request) {

		$accounts        = $this->availableAccounts();
		$reposPerAccount = array();
		$points          = $this->getPoints();

		if ($points == 0) {
			return view('home', ['error_message' => "You don't have any points left. Please review someone else code to get points"]);
		}

		foreach ($accounts as $key => $account) {
			$client = $this->getClient($account->provider);
			$client->setToken($account->token);

			$reposPerAccount[] = array(
				'account_id' => $account->id,
				'repos'      => $client->listRepositories(),
			);
		}

		return view('newreview', [
			'reposPerAccount' => $reposPerAccount,
			'points'          => $points,
			'languages'       => DB::table('skills')->get()]);
	}

	public function displayReview($reviewid) {
		$review = $this->getReview($reviewid);

		if (!$review) {
			return view('view-review-public', ['error_message' => 'Review Request not found !']);
		}

		$user_id = session('user_id');

		$tracked = DB::table('request_tracking')->where([
			['request_id', '=', $review->id],
			['user_id', '=', $user_id]])->first();

		$followers = DB::table('request_tracking')->where('request_id', $review->id)->count();

		return view('view-review-public', [
			'review'    => $review,
			'tracked'   => $tracked,
			'followers' => $followers,
		]);
	}

	public function getOpenedPullRequestForRepo($owner, $repo, $account_id) {

		$account = DB::table('accounts')->where([
			['user_id', '=', session('user_id')],
			['id', '=', $account_id]])->first();

		$client = $this->getClient($account->provider);
		$client->setToken($account->token);

		$raw_response = $client->listPullRequestsForRepo($owner, $repo);

		return json_encode($raw_response);
	}

	public function getBranches($owner, $repo, $account_id) {
		$account = DB::table('accounts')->where([
			['user_id', '=', session('user_id')],
			['id', '=', $account_id]])->first();

		$client = $this->getClient($account->provider);
		$client->setToken($account->token);

		$raw_response = $client->listBranchesForRepo($owner, $repo);

		return json_encode($raw_response);
	}

	public function track(Request $request, $reviewid) {

		$review = $this->getReview($reviewid);

		if (!$review) {
			return response()->json([
				'success' => 0,
				'message' => 'Review Request not found !',
			]);
		}

//There's already a front-end check, but never trust client
		if ($review->author_id == session('user_id')) {
			return response()->json([
				'success' => 0,
				'message' => 'You can\'t follow your own review requests',
			]);

		}

		try {
			DB::table('request_tracking')->insert([
				'user_id'    => session('user_id'),
				'request_id' => $reviewid,
				'status'     => 'unapproved',
			]);
		} catch (\Illuminate\Database\QueryException $e) {
			Log::error('Error when USER ' . session('user_id') . ' attempted to track code review ' . $reviewid . ' : ' . $e->getMessage());

			return response()->json([
				'success' => 0,
				'message' => 'An error ocurred !',
			]);

		}

		return response()->json([
			'success' => 1,
			'message' => 'You are now following this review request',
		]);
	}

	public function close(Request $request, $reviewid) {
		$review = $this->getReview($reviewid);

		if (!$review) {
			return response()->json([
				'success' => 0,
				'message' => 'Review Request not found !',
			]);
		}

		if ($review->author_id != session('user_id')) {
			return response()->json([
				'success' => 0,
				'message' => 'You can only close your own review requests',
			]);
		}

		try {
			DB::table('requests')->where('id', $review->id)->update(['status' => 'closed', 'updated_at' => \Carbon\Carbon::now()]);
		} catch (\Illuminate\Database\QueryException $e) {
			Log::error('Error when USER ' . session('user_id') . ' attempted to close code review ' . $reviewid . ' : ' . $e->getMessage());

			return response()->json([
				'success' => 0,
				'message' => 'An error ocurred !',
			]);

		}

		return response()->json([
			'success' => 1,
			'message' => 'Code review closed',
		]);

	}

	public function viewAllMine(Request $request) {
		$user_id = session('user_id');
		$reviews = DB::table('requests')
			->where('author_id', $user_id)
			->join('skills', 'requests.skill_id', '=', 'skills.id')
			->select('requests.*', 'skills.name as language')
			->orderBy('status', 'desc')
			->orderBy('updated_at', 'desc')
			->get();

		$followers_per_review = array();
		foreach ($reviews as $key => $review) {
			$followers = DB::table('request_tracking')
				->join('users', 'request_tracking.user_id', '=', 'users.id')
				->select('request_tracking.status', 'users.nickname', 'users.id')
				->where('request_id', $review->id)
				->get();

			if ($followers) {
				$followers_per_review[$review->id] = $followers;
			}

		}

		return view('my-reviews', ['reviews' => $reviews, 'followers' => $followers_per_review]);
	}

	public function viewAllTracked(Request $request) {
		$user_id = session('user_id');

		//TODO get rid of this awful code duplication, Single query ?
		$unapproved = DB::table('request_tracking')
			->join('requests', 'request_tracking.request_id', '=', 'requests.id')
			->join('skills', 'requests.skill_id', '=', 'skills.id')
			->select('requests.id', 'requests.name', 'requests.updated_at', 'skills.name as language')
			->orderBy('requests.updated_at', 'desc')
			->where([
				['request_tracking.user_id', '=', $user_id],
				['request_tracking.status', '=', 'unapproved'],
			])
			->get();

		$approved = DB::table('request_tracking')
			->join('requests', 'request_tracking.request_id', '=', 'requests.id')
			->join('skills', 'requests.skill_id', '=', 'skills.id')
			->select('requests.id', 'requests.name', 'requests.updated_at', 'skills.name as language')
			->orderBy('requests.updated_at', 'desc')
			->where([
				['request_tracking.user_id', '=', $user_id],
				['request_tracking.status', '=', 'approved'],
			])
			->get();

		return view('my-tracked-reviews', ['reviews_unapproved' => $unapproved, 'reviews_approved' => $approved]);
	}

	private function availableAccounts() {
		return DB::table('accounts')->where('user_id', session('user_id'))->get();
	}

	private function getPoints() {
		$user = DB::table('users')
			->select('points')
			->where('id', session('user_id'))
			->first();

		return $user->points;
	}

	/*
		        add/removePoint are function to allow
	*/
	private function addPoint() {
		DB::table('users')->increment('points');
	}

	private function removePoint() {
		DB::table('users')->decrement('points');
	}

	private function getClient($provider) {
		$factory = new GitProviderFactory($provider);

		return $factory->getProviderEngine();
	}

	private function getReview($reviewid) {
		return DB::table('requests')
			->join('users', 'requests.author_id', '=', 'users.id')
			->join('skills', 'requests.skill_id', '=', 'skills.id')
			->select('requests.*', 'users.nickname', 'skills.name as language')
			->orderBy('requests.updated_at', 'desc')
			->where('requests.id', $reviewid)
			->first();
	}

}
