<?php
namespace App\Classes\GitProviders;

use App\Classes\Models\Git\Branch;
use App\Classes\Models\Git\PullRequest;
use App\Classes\Models\Git\Repository;
use App\Classes\Models\Git\Tokens;
use App\Classes\Models\Git\UserInfo;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

/**
 *  A simple API client for Github,  handle OAuth login
 */
class Github implements GitProviderInterface {

	private $api    = 'https://api.github.com/';
	private $github = 'https://github.com';
	private $token  = '';
	private $default_headers;
	//Public attribute, I'll go burn in hell
	public $csrf_enabled = true;

	private $http_client;

	private $app_secret;
	private $client_id;

	function __construct($client_id, $app_secret, $custom_handler = null) {
		$this->client_id  = $client_id;
		$this->app_secret = $app_secret;

		$guzzle_args = ['base_uri' => $this->api, 'timeout' => 5.0];

//Makes mocking way easier
		if ($custom_handler != null) {
			$guzzle_args['handler'] = $custom_handler;
		}

		$this->default_headers = [
			'Content-type' => 'application/json',
			'Accept'       => 'application/json',
			'User-Agent'   => 'Inspicio',
		];

		$this->http_client = new Client($guzzle_args);
	}

	/*
		Gets the GiHub temporary "code" and turns it into an access_token
	*/
	public function fetchAccessToken($code) {
		$response = $this->http_client->request('POST', $this->github . '/login/oauth/access_token', [
			'json'    => [
				'client_id'     => $this->client_id,
				'client_secret' => $this->app_secret,
				'code'          => $code,
			],
			'headers' => $this->default_headers,
		]);
		$response_body = $response->getBody();
		Log::debug('[fetchAccessToken] - ' . $response_body);

		$json = json_decode($response_body);

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
	public function getAuthorizeUrl($csrf_token, $redirect_uri, $level = 'minimum') {

		$levels = $this->getAvailablePermissionLevels();
		$scope  = '';

		if (isset($levels[$level])) {
			$scope = $levels[$level]['scope'];
		}

		return $this->github . '/login/oauth/authorize?client_id='
		. urlencode($this->client_id) . '&state='
		. urlencode($csrf_token) . '&redirect_uri='
		. urlencode($redirect_uri) . '&scope=' . $scope;
	}

	/*
		Simply returns the user, useful for auth purposes on the website
	*/
	public function getUserInfo() {
		$response      = $this->http_client->request('GET', '/user', ['headers' => $this->default_headers]);
		$response_body = $response->getBody();
		Log::debug('[getUserInfo] - ' . $response_body);

		$json = json_decode($response_body);

		if (isset($json->login)) {
			return new UserInfo(['login' => $json->login]);
		}

		return false;
	}

	public function getAvailablePermissionLevels() {
		return [
			'maximum'             => [
				'scope'         => 'repo',
				'description'   => 'Public & Private Repos (Read,Write)',
				'can_create_pr' => true,
			],
			'maximum_public_only' => [
				'scope'         => 'public_repo',
				'description'   => 'Public repos (Read,Write)',
				'can_create_pr' => true,
			],
			'minimum'             => [
				'scope'         => '',
				'description'   => 'Public info only (Read)',
				'can_create_pr' => false,
			],
		];
	}

	public function getCurrentPermissionLevel() {
		$response      = $this->http_client->request('GET', '/user', ['headers' => $this->default_headers]);
		$current_scope = $response->getHeader('X-OAuth-Scopes')[0];

		Log::debug('Current scope is : ' . $current_scope);

		foreach ($this->getAvailablePermissionLevels() as $permission_name => $permission) {

			if ($permission['scope'] == $current_scope) {
				return $permission_name;
			}

		}

		Log::warning("The current scope : " . $current_scope . " returned by the API does not exist, defaulting to Minimum");

		return 'minimum';
	}

	public function listPullRequestsForRepo($owner, $repository) {
		$prs = $this->paginate(
			'/repos/' . $owner . '/' . $repository . '/pulls',
			"[listPullRequestsForRepo][$owner/$repository]",
			50
		);
		$std_prs = array();

		foreach ($prs as $pr) {
			$std_prs[] = new PullRequest([
				'name' => $pr['title'],
				'url'  => $pr['html_url'],
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

		foreach ($branches as $branch) {
			$std_branches[] = new Branch([
				'name' => $branch['name'],
			]);
		}

		return $std_branches;
	}

	public function getPullRequestData($url) {

		$pr_metadata = $this->splitPrUrl($url);

		if (!$pr_metadata) {
			return [false, 'Pull request url invalid'];
		}

		$api_url  = 'repos/' . $pr_metadata['owner'] . '/' . $pr_metadata['repository'] . '/pulls/' . $pr_metadata['id'];
		$response = $this->http_client->request('GET', $api_url, [
			'headers' => $this->default_headers,
		]);
		$response_body = $response->getBody();

		Log::debug("[getPullRequestData][$url] $response_body");
		$json = json_decode($response_body);

		if (!isset($json->html_url)) {
			return [false, 'Failed to fetch pull request information'];
		}

		if (isset($json->head->repo->full_name)) {
			$repository_name = $json->head->repo->full_name;
		} else {
			$repository_name = $json->base->repo->full_name;
		}

		return [true, new PullRequest([
			'name'        => $json->title,
			'url'         => $json->html_url,
			'description' => $json->body,
			'repository'  => $repository_name,
			'language'    => $json->base->repo->language,
		])];

	}

	private function splitPrUrl($url) {
		$url_parts = array_reverse(explode('/', $url));

//https://github.com/owner/repository/pull/id

		if (count($url_parts) < 5) {
			return false;
		}

		return [
			'id'         => htmlspecialchars($url_parts[0]),
			'owner'      => htmlspecialchars($url_parts[3]),
			'repository' => htmlspecialchars($url_parts[2]),
		];

	}

	public function updatePullRequest($owner, $repository, $url, $title, $description) {
		$pr_metadata = $this->splitPrUrl($url);
		$pr_id       = $pr_metadata['id'];

		if (!$pr_metadata) {
			return [false, 'Pull request url invalid'];
		}

		$api_url  = 'repos/' . $owner . '/' . $repository . '/pulls/' . $pr_id;
		$response = $this->http_client->request('PATCH', $api_url, [
			'json'    => [
				'title' => $title,
				'body'  => $description,
			],
			'headers' => $this->default_headers,
		]);
		$response_body = $response->getBody();

		Log::debug("[updatePullRequest][$owner/$repository] $response_body");

		$json = json_decode($response_body);

		if (isset($json->html_url)) {
			return [true, null];
		}

		if (isset($json->message)) {
			return [false, $json->message];
		}

		return [false, 'API Error'];
	}

	public function createPullRequest($owner, $repository, $head, $base, $title, $description) {
		$api_url = 'repos/' . $owner . '/' . $repository . '/pulls';

		$response = $this->http_client->request('POST', $api_url, [
			'json'    => [
				'title' => $title,
				'body'  => $description,
				'head'  => $head,
				'base'  => $base,
			],
			'headers' => $this->default_headers,
		]);
		$response_body = $response->getBody();
		Log::debug("[createPullRequest][$owner/$repository] $response_body");
		$pull_request = json_decode($response_body);

		$error_message = 'Failed to create pull request';

		if (isset($pull_request->url)) {
			return array(
				'success' => 1,
				'url'     => $pull_request->html_url,
			);
		}

		if (isset($pull_request->errors)) {
			$error_message = 'Error(s) from GitHub : ';

			foreach ($pull_request->errors as $error) {
				$error_message .= '[' . $error->message . ']';
			}

		}

		return array(
			'success' => 0,
			'error'   => $error_message,
		);

	}

	private function getRepoInfo($name) {
		$api_url = 'repos/' . $name;

		$response = $this->http_client->request('GET', $api_url, [
			'headers' => $this->default_headers,
		]);
		$response_body = $response->getBody();

		Log::debug("[getRepoInfo][$name] $response_body");

		return json_decode($response_body);
	}

	public function listRepositories() {
		$repos = $this->paginate('/user/repos', 'listRepositories', 100);
		//We need to standarize the format
		$std_repos = array();

		foreach ($repos as $repo) {

//If the repo is forked, we also want to fetch the original one

//In case the user have a PR between his forked version and the original one
			if ($repo['fork']) {

				$repo_info = $this->getRepoInfo($repo['full_name']);

				if (isset($repo_info->parent)) {

					$org_repo    = $repo_info->parent;
					$std_repos[] = new Repository([
						'name'     => $org_repo->full_name,
						'id'       => $org_repo->id,
						'url'      => $org_repo->url,
						'language' => $org_repo->language,
					]);
				}

			}

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
		$this->default_headers['Authorization'] = 'token ' . $token;
		$this->token                            = $token;
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
			$response = null;
			try {
				$response = $this->http_client->request('GET', $endpoint . "?page=$page" . $per_page_arg, [
					'headers' => $this->default_headers,
				]);
			} catch (\Exception $e) {
				Log::warning("Exception caught when fetching $endpoint : " . $e->getMessage());
				return $data;
			}

			$response_body = $response->getBody();

			Log::debug("[$method_name] - Page $page \n ===== \n" . $response_body . "\n ===== \n");
			$current_data = json_decode($response_body, true);

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