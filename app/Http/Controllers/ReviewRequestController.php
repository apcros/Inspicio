<?php

namespace App\Http\Controllers;

use App\Classes\GitProviderFactory;
use App\Http\Controllers\Controller;
use App\Notifications\ActionOnYourReview;
use App\ReviewRequest;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use \Mews\Purifier\Facades\Purifier;

class ReviewRequestController extends Controller {

	private function cleanDescription($description) {
		return Purifier::clean($description, ['HTML.Allowed' => 'b,strong,i,em,u,a[href|title],ul,ol,li,p,br,pre,h2,h3,h4']);
	}

	public function create(Request $request) {
		$title              = $request->input('title');
		$repository_account = $request->input('repository');
		$language           = $request->input('language');
		$pull_request_url   = $request->input('pull_request');
		$description        = $request->input('description');

		$head_branch = $request->input('head_branch');
		$base_branch = $request->input('base_branch');

		$user_id                       = session('user_id');
		list($repo_owner, $account_id) = explode(',', $repository_account);

		$review_request = new \App\ReviewRequest($user_id);

		list($success, $result) = $review_request->create([
			'account_id'       => $account_id,
			'title'            => $title,
			'description'      => $description,
			'repo_owner'       => $repo_owner,
			'base'             => $base_branch,
			'head'             => $head_branch,
			'pull_request_url' => $pull_request_url,
			'language'         => $language,
		]);

		if (!$success) {
			return view('home', ['error_message' => $result]);
		}

		return redirect('/reviews/' . $result . '/view');
	}

	public function bulkImportForm() {
		$user_id = session('user_id');
		$user    = DB::table('users')->where('id', $user_id)->first();

		if ($user->points == 0) {
			return view('home', ['error_message' => "You have 0 points left, You can't import any pull requests. Please review someone else code to gain points"]);
		}

		return view('bulk-import', ['user' => $user]);
	}

	public function bulkImport(Request $request) {
		//TODO : Implement duplicate detection
		$prs_to_import      = $request->input('prs_selected');
		$user_id            = session('user_id');
		$processing_results = [];

		if (!$prs_to_import) {
			return view('home', ['error_message' => "No pull requests selected !"]);
		}

		$user = new User($user_id);

		foreach ($prs_to_import as $data_to_import) {
			list($pr_url, $account_id) = explode(',', $data_to_import);

			$account = $user->getGitAccount($account_id);

			if (!$account) {
				Log::warning("[USER ID $user_id] Missing account $account_id for $pr_url");
				$processing_results[] = [
					'title'    => '',
					'success'  => 0,
					'url'      => $pr_url,
					'provider' => 'Git provider',
					'message'  => 'Account error',
				];
				continue;
			}

			$client                           = $user->getAccountClient($account);
			list($fetch_success, $data_fetch) = $client->getPullRequestData($pr_url);

			if (!$fetch_success) {
				$processing_results[] = [
					'title'    => '',
					'success'  => 0,
					'url'      => $pr_url,
					'provider' => ucfirst($account->provider),
					'message'  => $data_fetch,
				];
				continue;
			}

			$pr = $data_fetch;

			$review_request                     = new \App\ReviewRequest($user_id);
			list($import_success, $data_import) = $review_request->create([
				'account_id'           => $account_id,
				'title'                => $pr->name,
				'description'          => $pr->description,
				'repo_owner'           => $pr->repository,
				'pull_request_url'     => $pr->url,
				'language_search_term' => $pr->language,
			]);

			$current_result = [
				'title'    => $data_fetch->name,
				'url'      => $data_fetch->url,
				'provider' => ucfirst($account->provider),
				'success'  => 1,
			];

			if (!$import_success) {
				$current_result['success'] = 0;
				$current_result['message'] = $data_fetch;
			}

			$current_result['message'] = $data_import;
			$processing_results[]      = $current_result;

		}

		return view('bulk-import-results', ['results' => $processing_results]);

	}

	public function edit(Request $request, $id) {
		$title       = $request->input('title');
		$language    = $request->input('language');
		$description = $request->input('description');
		$update_git  = $request->input('update_on_git');
		$user_id     = session('user_id');

		$review_request = new \App\ReviewRequest($user_id);

		list($success, $message) = $review_request->edit([
			'id'            => $id,
			'description'   => $description,
			'title'         => $title,
			'language'      => $language,
			'update_on_git' => $update_git,
		]);

		if (!$success) {
			return view('home', ['error_message' => $message]);
		}

		return view('home', [
			'info_message' => $message,
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
		$user_id         = session('user_id');
		$reposPerAccount = array();
		$points          = $this->getPoints();

		if ($points == 0) {
			return view('home', ['error_message' => "You don't have any points left. Please review someone else code to get points"]);
		}

		$user = new User($user_id);

		foreach ($accounts as $account) {

			$account_checked = $user->getGitAccount($account->id);
			$client          = $user->getAccountClient($account_checked);

//To force refresh where needed
			//Provider and id are not going to changen, but token might just have been changed by the above statement

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
		$review_request = new \App\ReviewRequest();
		$review         = $review_request->load($reviewid);

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

		$user    = new User(session('user_id'));
		$account = $user->getGitAccount($account_id);
		$client  = $user->getAccountClient($account);

		$pull_request_array = $client->listPullRequestsForRepo($owner, $repo);

		return json_encode($pull_request_array);
	}

	public function getBranches($owner, $repo, $account_id) {
		$user    = new User(session('user_id'));
		$account = $user->getGitAccount($account_id);
		$client  = $user->getAccountClient($account);

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

	private function notifyUserEmail($userid, $reviewid, $action) {
		$review_request = new \App\ReviewRequest($user_id);
		$review         = $review_request->load($reviewid);
		$owner          = DB::table('users')->where('id', $review->author_id)->first();
		$user           = DB::table('users')->where('id', $userid)->first();

		//TODO make eloquent models instead of re-using the default one in a horrible way
		$user_model        = new User($owner->id);
		$user_model->email = $owner->email;

		$user_model->notify(new ActionOnYourReview($user, $review, $action));
	}

}
