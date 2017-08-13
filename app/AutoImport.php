<?php

namespace App;

use App\ReviewRequest;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoImport {

	private function listAllWatchedRepositories() {
		return DB::table('auto_imports')->where('is_active', true)->get();
	}

	public function run() {
		$auto_imports = $this->listAllWatchedRepositories();

		if (!$auto_imports) {
			Log::debug('Nothing to auto import');
		}

		foreach ($auto_imports as $auto_import) {
			$import_results = $this->importFromRepository($auto_import);
		}

	}

	private function importFromRepository($auto_import) {

		$client             = $this->getGitClient($auto_import->user_id, $auto_import->account_id);
		list($owner, $repo) = explode('/', $auto_import->repository);
		Log::info("Found $owner / $repo in the setup auto-imports");
		$pull_requests = $client->listPullRequestsForRepo($owner, $repo);

		if (!$pull_requests) {
			return false;
		}

		$results = array();
		$user    = new User($auto_import->user_id);

		foreach ($pull_requests as $pull_request) {
			$duplicated_pr = DB::table('requests')->where([
				['url', '=', $pull_request->url],
				['author_id', '=', $auto_import->user_id],
			])->first();

			if ($duplicated_pr) {
				Log::info($pull_request->url . "(" . $pull_request->name . ") Is already on Inspicio");
				continue;
			}

			if ($user->getPoints() < 1) {
				Log::warning("No points left, Ignoring import for " . $auto_import->user_id);
				continue;
			}

			list($success_fetch, $fetch_data) = $client->getPullRequestData($pull_request->url);

			if (!$success_fetch) {
				Log::error("Error while trying to retrieve pr data from " . $pull_request->url . " " . $fetch_data);
				$result[] = $this->insertImportResult($auto_import->id, false, $fetch_data);
				continue;
			}

			$review_request = new \App\ReviewRequest($auto_import->user_id);

			list($success_create, $create_data) = $review_request->create([
				'account_id'           => $auto_import->account_id,
				'title'                => $fetch_data->name,
				'description'          => $fetch_data->description,
				'repo_owner'           => $fetch_data->repository,
				'pull_request_url'     => $fetch_data->url,
				'language_search_term' => $fetch_data->language,
			]);

			$result[] = $this->insertImportResult($auto_import->id, $success_create, $create_data);

		}

	}

//This really should be private, but phpunit doesn't let me mock private methods :'(
	public function getGitClient($user_id, $account_id) {
		$user    = new User($user_id);
		$account = $user->getGitAccount($account_id);
		$client  = $user->getAccountClient($account);

		return $client;
	}

	private function insertImportResult($import_id, $success, $data) {
		$result = [
			'auto_import_id' => $import_id,
			'created_at'     => \Carbon\Carbon::now(),
			'updated_at'     => \Carbon\Carbon::now(),
			'is_success'     => $success,
		];

		if (!$success) {
			$result['error'] = $data;
			Log::error("Failed to import import_id = $import_id into Inspicio : $data");

			return DB::table('auto_imports_result')->insertGetId($result);
		}

		$result['request_id'] = $data;
		Log::info("Imported $import_id Into Inspicio : $data");

		return DB::table('auto_imports_result')->insertGetId($result);
	}

	public function update($import_id, $user_id, $is_active) {
		$auto_import = DB::table('auto_imports')->where('id', $import_id)->get();

		if (!$auto_import) {
			return [false, "Auto Import entry not found"];
		}

		if ($auto_import->user_id != $user_id) {
			return [false, "You can't update someone else Auto Import Entry"];
		}

		DB::table('auto_imports')->update([
			'is_active' => $is_active,
		])->where('id', $import_id);

		return [true, 'Updated with success'];
	}

}
