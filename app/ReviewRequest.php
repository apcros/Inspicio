<?php

namespace App;

use App\Facades\UuidUtils;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use \Mews\Purifier\Facades\Purifier;

class ReviewRequest {

	private $user;
	private $log_prefix;
	public function __construct($user_id = false) {
		$this->log_prefix = "[ReviewRequest]";

		if ($user_id) {
			$this->user       = new User($user_id);
			$this->log_prefix = "[USER $user_id - ReviewRequest]";
		}

	}

	public function load($id) {

		if (!UuidUtils::is_valid($id)) {
			return [false, 'Invalid UUID'];
		}

		try {
			$review = DB::table('requests')
				->join('users', 'requests.author_id', '=', 'users.id')
				->join('skills', 'requests.skill_id', '=', 'skills.id')
				->select('requests.*', 'users.nickname', 'skills.name as language')
				->orderBy('requests.updated_at', 'desc')
				->where('requests.id', $id)
				->first();

			return [true, $review];
		} catch (\Illuminate\Database\QueryException $e) {
			Log::error("Exception when getting $id : " . $e->getMessage());

			return [false, 'Database Error'];
		}

	}

	public function edit(array $args) {
		$id      = $args['id'];
		$review  = DB::table('requests')->where('id', $id)->first();
		$user_id = $this->user->getId();

		if (!$review) {
			return [false, 'Code review request not found'];
		}

		if ($user_id != $review->author_id) {
			return [false, "You can't edit someone else code review request"];
		}

		if ($review->status != 'open') {
			return [false, "You can't edit a closed code review request"];
		}

		$html_description = $this->cleanDescription($args['description']);

		try {
			DB::table('requests')->where('id', $id)->update([
				'updated_at'  => \Carbon\Carbon::now(),
				'name'        => $args['title'],
				'description' => $html_description,
				'skill_id'    => $args['language'],
			]);
			Log::info($this->log_prefix . " Updated their code review $id request on Inspicio");
		} catch (\Illuminate\Database\QueryException $e) {
			Log::error($this->log_prefix . " SQL Error caught while editing Pull request : " . $e->getMessage());

			return [false, 'An internal error ocurred while trying to edit your review request'];
		}

		$message = "Updated code review request on Inspicio";

		$overall_success = true;

		if (isset($args['update_on_git'])) {
			$account                              = $this->user->getGitAccount($review->account_id);
			list($update_success, $update_result) = $this->updateOnGit($review->repository, $review->url, $args['title'], $html_description, $account);
			$overall_success                      = $update_success;
			$message .= ', ' . $update_result;
		}

		return [$overall_success, $message];

	}

	public function create(array $args) {

		if ($this->user->getPoints() <= 0) {
			Log::warning($this->log_prefix . " Attempted to create a review with no points");

			return [false, "You don't have any points left. Please review someone else code to get points"];
		}

		$account = $this->user->getGitAccount($args['account_id']);

		if (!$account) {
			Log::error($this->log_prefix . ' No account available');

			return [false, 'Error with your git account'];
		}

		$cleaned_description = $this->cleanDescription($args['description']);

		if (!isset($args['pull_request_url'])) {
			list($creation_status, $creation_data) = $this->createOnGit($args['head'], $args['base'], $args['repo_owner'], $args['title'], $cleaned_description, $account);

			if (!$creation_status) {
				Log::error($this->log_prefix . ' Error while adding PR on ' . $account->provider . ' : ' . $creation_data);

				return [$creation_status, $creation_data];
			}

			$args['pull_request_url'] = $creation_data;
		}

		if (!isset($args['language'])) {

			if(!isset($args['language_search_term'])) {
				return [false, 'No language selected'];
			}

			$args['language'] = $this->guessLanguageId($args['language_search_term']);
		}

		$review_request_id = UuidUtils::generate();
		try {
			DB::table('requests')->insert([
				'id'          => $review_request_id,
				'name'        => $args['title'],
				'description' => $cleaned_description,
				'url'         => $args['pull_request_url'],
				'status'      => 'open',
				'skill_id'    => $args['language'],
				'author_id'   => $this->user->getId(),
				'repository'  => $args['repo_owner'],
				'account_id'  => $args['account_id'],
				'created_at'  => \Carbon\Carbon::now(),
				'updated_at'  => \Carbon\Carbon::now(),
			]);
			$this->user->removePoint();

		} catch (\Illuminate\Database\QueryException $e) {
			Log::error($this->log_prefix . ' SQL Error caught while adding Pull request : ' . $e->getMessage());

			return [false, 'An error ocurred while trying to add your review request'];
		}

		Log::info($this->log_prefix . ' Review request created');

		return [true, $review_request_id];
	}

	private function createOnGit($head, $base, $repo_owner, $title, $description, $account) {
		list($owner, $repo) = explode('/', $repo_owner);
		$client             = $this->user->getAccountClient($account);

		$result = $client->createPullRequest($owner, $repo, $head, $base, $title, $description);

		if ($result['success'] == 0 || !isset($result['url'])) {
			return [false, 'Error while creating your code review request : ' . $result['error']];
		}

		return [true, $result['url']];
	}

	private function updateOnGit($repo_owner, $url, $title, $description, $account) {
		list($owner, $repository) = explode('/', $repo_owner);
		$client                   = $this->user->getAccountClient($account);

		list($status, $error) = $client->updatePullRequest($owner, $repository, $url, $title, $description);
		$provider_clean       = ucfirst($account->provider);

		if ($status) {
			Log::info($this->log_prefix . "Updated their code review $title request on $provider_clean");

			return [true, "Updated on $provider_clean"];
		}

		Log::warning($this->log_prefix . "Failed to update their code review $title request on $provider_clean : $error");

		return [false, "Not updated on $provider_clean for the following reason : $error"];

	}

	private function cleanDescription($html_description) {
		return Purifier::clean($html_description, ['HTML.Allowed' => 'b,strong,i,em,u,a[href|title],ul,ol,li,p,br,pre,h2,h3,h4']);
	}

	private function guessLanguageId($language_search_term) {
		/* It's not really guessing anything for now
			        But because Inspicio's language list come from GitHub that
			        Should be good enough for now.
		*/

		$language = DB::table('skills')
			->where('name', 'like', strtoupper($language_search_term))
			->first();

		if ($language) {
			return $language->id;
		}

		//Should probably have a better default than the first one in the list. (TODO)

		return 1;
	}

}
