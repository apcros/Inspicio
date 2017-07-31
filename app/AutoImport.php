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

		if (!$repos) {
			Log::debug('Nothing to auto import');
		}

		foreach ($auto_imports as $auto_import) {
			$import_results = $this->importFromRepository($auto_import);
		}

	}

	private function importFromRepository($auto_import) {
		$user    = new User($auto_import->user_id);
		$account = $user->getGitAccount($auto_import->account_id);
		$client  = $user->getAccountClient($account);

		list($owner, $repo) = $auto_import->repository;
		$pull_requests      = $client->listPullRequestsForRepo($owner, $repo);

		if (!$pull_requests) {
			return false;
		}

		$results = array();

		foreach ($pull_requests as $pull_request) {
			$duplicated_pr = DB::table('requests')->where('url', $pull_request->url)->first();

			if ($duplicated_pr) {
				Log::info($pull_request->url . "(" . $pull_request->name . ") Is already on Inspicio");
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
				'account_id'           => $account->id,
				'title'                => $fetch_data->name,
				'description'          => $fetch_data->description,
				'repo_owner'           => $fetch_data->repository,
				'pull_request_url'     => $fetch_data->url,
				'language_search_term' => $fetch_data->language,
			]);
			Log::info("Imported " . $fetch_data->url . " Into Inspicio : $create_data");
			$result[] = $this->insertImportResult($auto_import->id, $success_fetch, $create_data);

		}

	}

	private function insertImportResult($import_id, $success, $data) {
		$result = [
			'auto_import_id' => $auto_import->id,
			'created_at'     => \Carbon\Carbon::now(),
			'updated_at'     => \Carbon\Carbon::now(),
		];

		if (!$success) {
			$result['success'] = 0;
			$result['error']   = $data;

			return DB::table('auto_imports_result')->insertGetId($result);
		}

		$result['success']    = 1;
		$result['request_id'] = $data;

		return DB::table('auto_imports_result')->insertGetId($result);
	}

}
