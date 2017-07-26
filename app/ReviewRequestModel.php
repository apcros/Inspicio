<?php

namespace App;

use App\Classes\GitProviderFactory;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use \Mews\Purifier\Facades\Purifier;
use \Ramsey\Uuid\Uuid;

class ReviewRequestModel {

	private $user;
	private $log_prefix;
	public function __construct($user_id) {
		$this->user = new User($user_id);
		$log_prefix = "[USER $user_id - ReviewRequest]";
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

		if (!$args['pull_request_url']) {
			list($creation_status, $creation_data) = $this->createOnGit($args['head'], $args['base'], $args['repo_owner'], $args['title'], $cleaned_description, $account);

			if (!$creation_status) {
				Log::error($this->log_prefix . ' Error while adding PR on ' . $account->provider . ' : ' . $creation_data);

				return [$creation_status, $creation_data];
			}

			$args['pull_request_url'] = $creation_data;
		}

		$review_request_id = Uuid::uuid4()->toString();
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
		$factory            = new GitProviderFactory($account->provider);
		$client             = $factory->getProviderEngine();
		$client->setToken($account->token);
		$result = $client->createPullRequest($owner, $repo, $head, $base, $title, $description);

		if ($result['success'] == 0 || !isset($result['url'])) {
			return [false, 'Error while creating your code review request : ' . $pr_result['error']];
		}

		return [true, $pr_result['url']];
	}

	private function cleanDescription($html_description) {
		return Purifier::clean($html_description, ['HTML.Allowed' => 'b,strong,i,em,u,a[href|title],ul,ol,li,p,br,pre,h2,h3,h4']);
	}

}
