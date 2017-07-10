<?php

namespace App\Http\Controllers;

use App\Classes\GitProviderFactory;
use App\Http\Controllers\Controller;
use App\Notifications\ActionOnYourReview;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use \Mews\Purifier\Facades\Purifier;
use \Ramsey\Uuid\Uuid;

class ReviewRequest extends Controller {

	private function cleanDescription($description) {
		return Purifier::clean($description, ['HTML.Allowed' => 'b,strong,i,em,u,a[href|title],ul,ol,li,p,br,pre,h2,h3,h4']);
	}

	public function create(Request $request) {
		$title              = $request->input('title');
		$repository_account = $request->input('repository');
		$language           = $request->input('language');
		$pull_request_url   = $request->input('pull_request');
		$description        = $request->input('description');

		$html_description = $this->cleanDescription($description);

		if ($this->getPoints() == 0) {
			Log::warning('[ USER ' . session('user_id') . '] Attempted to create a review with no points');

			return view('home', ['error_message' => "You don't have any points left. Please review someone else code to get points"]);
		}

		list($owner_repo, $account_id) = explode(',', $repository_account);

		$account = $this->getAccount($account_id, session('user_id'));

		if (!$account) {
			Log::error('[ USER ' . session('user_id') . '] No account available');

			return view('home', ['error_message' => 'Unexpected error']);
		}

		if (!$pull_request_url) {

			$head_branch = $request->input('head_branch');
			$base_branch = $request->input('base_branch');

			$client = $this->getClient($account->provider);
			$client->setToken($account->token);

			list($owner, $repo) = explode('/', $owner_repo);

			$pr_result = $client->createPullRequest($owner, $repo, $head_branch, $base_branch, $title, $html_description);

			if ($pr_result['success'] == 0 || !isset($pr_result['url'])) {
				Log::error('[USER ' . session('user_id') . '] Failed to create PR on ' . $account->provider);

				return view('home', ['error_message' => 'Error while creating your code review request : ' . $pr_result['error']]);
			}

			Log::info('[USER ' . session('user_id') . '] Created PR ' . $pr_result['url'] . ' on ' . $account->provider);
			$pull_request_url = $pr_result['url'];
		}

		$review_request_id = Uuid::uuid4()->toString();

		try {
			DB::table('requests')->insert([
				'id'          => $review_request_id,
				'name'        => $title,
				'description' => $html_description,
				'url'         => $pull_request_url,
				'status'      => 'open',
				'skill_id'    => $language,
				'author_id'   => session('user_id'),
				'repository'  => $owner_repo,
				'account_id'  => $account_id,
				'created_at'  => \Carbon\Carbon::now(),
				'updated_at'  => \Carbon\Carbon::now(),
			]);

			DB::table('users')->where('id', session('user_id'))->decrement('points');

		} catch (\Illuminate\Database\QueryException $e) {
			Log::error('[USER ' . session('user_id') . '] SQL Error caught while adding Pull request : ' . $e->getMessage());

			return view('home', ['error_message' => 'An error ocurred while trying to add your review request']);
		}

		Log::info('[USER ' . session('user_id') . '] Review request created');

		return redirect('/reviews/' . $review_request_id . '/view');
	}

	public function bulkImportForm() {
		$user_id = session('user_id');
		$user    = DB::table('users')->where('id', $user_id)->first();

		if ($user->points == 0) {
			return view('home', ['error_message' => "You have 0 points left, You can't import any pull requests. Please review someone else code to gain points"]);
		}

		return view('bulk-import', ['user' => $user]);
	}

	public function bulkImport() {

	}

	public function edit(Request $request, $id) {
		$title       = $request->input('title');
		$language    = $request->input('language');
		$description = $request->input('description');
		$update_git  = $request->input('update_on_git');

		$review = DB::table('requests')->where('id', $id)->first();

		$user_id = session('user_id');

		if ($user_id != $review->author_id) {
			return view('home', ['error_message' => "You can't edit someone else code review request"]);
		}

		if ($review->status != 'open') {
			return view('home', ['error_message' => "You can't edit a closed code review request"]);
		}

		$html_description = $this->cleanDescription($description);

		try {
			DB::table('requests')->where('id', $id)->update([
				'updated_at'  => \Carbon\Carbon::now(),
				'name'        => $title,
				'description' => $html_description,
				'skill_id'    => $language,
			]);
			Log::info("[USER $user_id] Updated their code review $id request on Inspicio");
		} catch (\Illuminate\Database\QueryException $e) {
			Log::error("[USER $user_id] SQL Error caught while editing Pull request : " . $e->getMessage());

			return view('home', ['error_message' => 'An internal error ocurred while trying to edit your review request']);
		}

		$account        = DB::table('accounts')->where('id', $review->account_id)->first();
		$provider_clean = ucfirst($account->provider);

		$git_message = "Not updated on $provider_clean";

		if ($update_git) {

			$client = $this->getClient($account->provider);
			$client->setToken($account->token);

			list($owner, $repository) = explode('/', $review->repository);
			list($status, $error)     = $client->updatePullRequest($owner, $repository, $review->url, $title, $html_description);

			if ($status) {
				$git_message = "Updated on $provider_clean";
				Log::info("[USER $user_id] Updated their code review $id request on $provider_clean");
			} else {
				$git_message = "Not updated on $provider_clean for the following reason : $error";
				Log::warning("[USER $user_id] Failed to update their code review $id request on $provider_clean : $error");
			}

		}

		return view('home', [
			'info_message' => "Updated code review request on Inspicio, $git_message",
			'info_html'    => "<p><a href='/reviews/$id/view' class='btn btn-primary'>View on Inspicio</a></p>",
		]);

	}

	public function editForm($id) {
		$languages = DB::table('skills')->get();
		$review    = DB::table('requests')->where('id', $id)->first();
		$account   = DB::table('accounts')->where('id', $review->account_id)->first();
		$user_id   = session('user_id');

		if ($user_id != $review->author_id) {
			return view('home', ['error_message' => "You can't edit someone else code review request"]);
		}

		return view('editreview', [
			'review'    => $review,
			'provider'  => ucfirst($account->provider),
			'languages' => $languages,
		]);
	}

	public function createForm() {

		$accounts        = $this->availableAccounts();
		$reposPerAccount = array();
		$points          = $this->getPoints();

		if ($points == 0) {
			return view('home', ['error_message' => "You don't have any points left. Please review someone else code to get points"]);
		}

		foreach ($accounts as $account) {

			$account_checked = $this->getAccount($account->id, $account->user_id);

//To force refresh where needed

			//Provider and id are not going to changen, but token might just have been changed by the above statement
			$client = $this->getClient($account_checked->provider);
			$client->setToken($account_checked->token);

			$reposPerAccount[] = array(
				'account_id' => $account_checked->id,
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
			return view('home', ['error_message' => 'Review Request not found !']);
		}

		$user_id = session('user_id');

		$tracked = DB::table('request_tracking')->where([
			['request_id', '=', $review->id],
			['user_id', '=', $user_id]])->first();

		$followers = DB::table('request_tracking')->where([
			['request_id', '=', $review->id],
			['is_active', '=', true],
		])->count();

		return view('view-review-public', [
			'review'    => $review,
			'tracked'   => $tracked,
			'followers' => $followers,
		]);
	}

	public function getOpenedPullRequestForRepo($owner, $repo, $account_id) {

		$account = $this->getAccount($account_id, session('user_id'));

		$client = $this->getClient($account->provider);
		$client->setToken($account->token);

		$pull_request_array = $client->listPullRequestsForRepo($owner, $repo);

		return json_encode($pull_request_array);
	}

	public function getBranches($owner, $repo, $account_id) {
		$account = $this->getAccount($account_id, session('user_id'));

		$client = $this->getClient($account->provider);
		$client->setToken($account->token);

		$raw_response = $client->listBranchesForRepo($owner, $repo);

		return json_encode($raw_response);
	}

	public function viewAllMine() {
		$user_id = session('user_id');
		$reviews = DB::table('requests')
			->where('author_id', $user_id)
			->join('skills', 'requests.skill_id', '=', 'skills.id')
			->select('requests.*', 'skills.name as language')
			->orderBy('status', 'desc')
			->orderBy('updated_at', 'desc')
			->get();

		$followers_per_review = array();

		foreach ($reviews as $review) {
			$followers = DB::table('request_tracking')
				->join('users', 'request_tracking.user_id', '=', 'users.id')
				->select('request_tracking.is_active', 'request_tracking.is_approved', 'users.nickname', 'users.id')
				->where([
					['request_id', '=', $review->id],
					['is_active', '=', true],
				])
				->get();

			if ($followers) {
				$followers_per_review[$review->id] = $followers;
			}

		}

		return view('my-reviews', ['reviews' => $reviews, 'followers' => $followers_per_review]);
	}

	public function viewAllTracked() {
		$user_id = session('user_id');

		$unapproved = $this->getTrackingsFor($user_id, false);
		$approved   = $this->getTrackingsFor($user_id, true);

		return view('my-tracked-reviews', ['reviews_unapproved' => $unapproved, 'reviews_approved' => $approved]);
	}

	private function getTrackingsFor($user_id, $approved) {
		return DB::table('request_tracking')
			->join('requests', 'request_tracking.request_id', '=', 'requests.id')
			->join('skills', 'requests.skill_id', '=', 'skills.id')
			->select('requests.id', 'requests.name', 'requests.updated_at', 'skills.name as language')
			->orderBy('requests.updated_at', 'desc')
			->where([
				['request_tracking.user_id', '=', $user_id],
				['request_tracking.is_approved', '=', $approved],
				['requests.status', '=', 'open'],
			])
			->get();
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

	private function getClient($provider) {
		$factory = new GitProviderFactory($provider);

		return $factory->getProviderEngine();
	}

	private function getReview($reviewid) {
		//TODO validate uuid to avoid ignoring sql errors
		try {
			return DB::table('requests')
				->join('users', 'requests.author_id', '=', 'users.id')
				->join('skills', 'requests.skill_id', '=', 'skills.id')
				->select('requests.*', 'users.nickname', 'skills.name as language')
				->orderBy('requests.updated_at', 'desc')
				->where('requests.id', $reviewid)
				->first();
		} catch (\Illuminate\Database\QueryException $e) {
			//Only debug and not error as it's likely to be due to invalid uuid representation
			Log::debug("Exception when getting $reviewid : " . $e->getMessage());

			return false;
		}

	}

	private function getAccount($account_id, $user_id) {
		$account = DB::table('accounts')->where([
			['user_id', '=', $user_id],
			['id', '=', $account_id]])->first();

		if ($account->refresh_token) {
			Log::info("[USER $user_id] Account " . $account->id . ' expire at ' . $account->expire_epoch);

			if ($account->expire_epoch <= time()) {
				$client = $this->getClient($account->provider);
				$tokens = $client->refreshToken($account->refresh_token);

				Log::info("[USER $user_id] Token expired, refreshing for $user_id (Account $account_id)");

				DB::table('accounts')->where('id', $account_id)->update([
					'token'        => $tokens->token,
					'expire_epoch' => $tokens->expire_epoch,
					'updated_at'   => \Carbon\Carbon::now(),
				]);

				$account = DB::table('accounts')->where([
					['user_id', '=', $user_id],
					['id', '=', $account_id]])->first();
			}

		}

		return $account;
	}

	private function notifyUserEmail($userid, $reviewid, $action) {
		$review = $this->getReview($reviewid);
		$owner  = DB::table('users')->where('id', $review->author_id)->first();
		$user   = DB::table('users')->where('id', $userid)->first();

		//TODO make eloquent models instead of re-using the default one in a horrible way
		$user_model        = new User();
		$user_model->email = $owner->email;

		$user_model->notify(new ActionOnYourReview($user, $review, $action));
	}

}
