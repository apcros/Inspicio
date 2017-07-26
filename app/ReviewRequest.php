<?php

namespace App;

use App\User;
use Illuminate\Support\Facades\Log;

class ReviewRequest {

	private $user;
	private $log_prefix;
	public function __construct($user_id) {
		$this->user = App\User::new ($user_id);
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

		if (!$args['pull_request_url']) {
			list($creation_status, $creation_data) = $this->createOnGit($args['head'], $args['base'], $args['repo_owner'], $args['title'], $cleaned_description, $account);

			if (!$creation_status) {
				return [$creation_status, $creation_data];
			}

		}

	}

	private function createOnGit($head, $base, $repo_owner, $title, $description, $account) {

	}

	private function cleanDescription($html_description) {

	}

}
