<?php
namespace App\Classes\GitProviders;

use App\Classes\Models\Git\Branch;
use App\Classes\Models\Git\PullRequest;
use App\Classes\Models\Git\Repository;
use App\Classes\Models\Git\Tokens;
use App\Classes\Models\Git\UserInfo;
use App\Classes\UserAgent;
use Illuminate\Support\Facades\Log;

/**
 *  A simple API client for Github,  handle OAuth login
 */
class Github implements GitProviderInterface {
	private $api = 'https://api.github.com';

	private $app_secret;

	private $client_id;

	private $github = 'https://github.com';

	private $token = '';

	private $ua;

	//Public attribute, I'll go burn in hell
	public $csrf_enabled = true;

	function __construct($client_id, $app_secret, $ua = null) {
		$this->client_id  = $client_id;
		$this->app_secret = $app_secret;

//Makes mocking way easier
		if ($ua != null) {
			$this->ua = $ua;
		} else {
			$this->ua = new UserAgent();
			$this->ua->setHeaders(
				array(
					'Content-type: application/json',
					'Accept: application/json',
					'User-Agent: Inspicio',
				));
		}

	}

	/*
		Gets the GiHub temporary "code" and turns it into an access_token
	*/
	public function fetchAccessToken($code) {

		$raw_response = $this->ua->post($this->github . '/login/oauth/access_token', json_encode(array(
			'client_id'     => $this->client_id,
			'client_secret' => $this->app_secret,
			'code'          => $code,
		)));
		Log::debug('[fetchAccessToken] - ' . $raw_response);

		$json = json_decode($raw_response);

		if (isset($json->access_token)) {
			$this->setToken($json->access_token);
		}

		return new Tokens([
			'token'         => $this->token,
			'refresh_token' => null,
			'expire_epoch'  => null,
		]);
	}

	/*
		get_authorize_url will simply return a string where the user should be redirected to start the process of
		oauth auth.
	*/
	public function getAuthorizeUrl($csrf_token, $redirect_uri) {
		return $this->github . '/login/oauth/authorize?client_id='
		. urlencode($this->client_id) . '&state='
		. urlencode($csrf_token) . '&redirect_uri='
		. urlencode($redirect_uri) . '&scope=user,repo';
	}

	/*
		Simply returns the user, useful for auth purposes on the website
	*/
	public function getUserInfo() {
		$raw_response = $this->ua->get($this->api . '/user');
		Log::debug('[getUserInfo] - ' . $raw_response);

		$json = json_decode($raw_response);

		return new UserInfo(['login' => $json->login]);
	}

	public function listPullRequestsForRepo($owner, $repository) {
		$prs = $this->paginate(
			'/repos/' . $owner . '/' . $repository . '/pulls',
			"[listPullRequestsForRepo][$owner/$repository]",
			50
		);
		$std_prs = array();

		foreach ($prs as $key => $pr) {
			$std_prs[] = new PullRequest([
				'name' => $pr->title,
				'url'  => $pr->html_url,
			]);
		}

		return $std_prs;
	}

	public function listBranchesForRepo($owner, $repository) {

		$branches = $this->paginate(
			'/repos/' . $owner . '/' . $repository . '/branches',
			"[listBranchesForRepo][$owner/$repository]"
		);

		$std_branches = array();

		foreach ($branches as $key => $branch) {
			$std_branches[] = new Branch([
				'name' => $branch->name,
			]);
		}

		return $std_branches;
	}

	public function createPullRequest($owner, $repository, $head, $base, $title, $description) {
		$api_url = $this->api . '/repos/' . $owner . '/' . $repository . '/pulls';
		Log::debug("[createPullRequest][$owner/$repository]" . $raw_response);

		$raw_response = $this->ua->post($api_url, json_encode([
			'title' => $title,
			'body'  => $description,
			'head'  => $head,
			'base'  => $base,
		]));

		$pull_request = json_decode($raw_response);

		$error_message = 'Failed to create pull request';

		if (isset($pull_request->url)) {
			return array(
				'success' => 1,
				'url'     => $pull_request->html_url,
			);
		}

		if (isset($pull_request->errors)) {
			$error_message = 'Error(s) from GitHub : ';

			foreach ($pull_request->errors as $key => $error) {
				$error_message .= '[' . $error->message . ']';
			}

		}

		return array(
			'success' => 0,
			'error'   => $error_message,
		);

	}

	public function listRepositories() {
		$repos = $this->paginate('/user/repos', 'listRepositories', 100);
		//We need to standarize the format
		$std_repos = array();

		foreach ($repos as $key => $repo) {
			$std_repos[] = new Repository([
				'name'     => $repo['full_name'],
				'id'       => $repo['id'],
				'url'      => $repo['url'],
				'language' => $repo['language'],
			]);
		}

		return $std_repos;
	}

	public function setToken($token) {
		$this->ua->addHeader('Authorization: token ' . $token);
		$this->token = $token;
	}

	public function refreshToken($refresh_token) {
		Log::warning('RefreshToken called for Github, Tokens should not expire');
	}

	private function paginate($endpoint, $method_name, $per_page = null) {
		$page  = 1;
		$fetch = true;
		$data  = [];

		isset($per_page) ? ($per_page_arg = '') : ($per_page_arg = "&per_page=$per_page");

		while ($fetch) {

			$raw_response = $this->ua->get($this->api . $endpoint . "?page=$page" . $per_page_arg);
			Log::debug("[$method_name] - Page $page \n ===== \n" . $raw_response . "\n ===== \n");
			$current_data = json_decode($raw_response, true);

			if (isset($current_data[0])) {
				$page++;
				$data = array_merge($current_data, $data);
			} else {
				$fetch = false;
			}

		}

		return $data;

	}

}

?>