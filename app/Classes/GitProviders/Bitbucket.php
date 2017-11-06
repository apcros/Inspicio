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
 *  A simple API client for Bitbucket,  handle OAuth login
 */
class Bitbucket implements GitProviderInterface {
	private $api = 'https://api.bitbucket.org';

	private $app_secret;

	private $client_id;

	private $bitbucket = 'https://bitbucket.org';

	private $token = '';

	private $http_client;

	//Public attribute, I'll go burn in hell
	public $csrf_enabled = false;

	function __construct($client_id, $app_secret, $custom_handler = null) {
		$this->client_id  = $client_id;
		$this->app_secret = $app_secret;

		$guzzle_args = ['base_uri' => $this->api, 'timeout' => 15.0];

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

	public function getAuthorizeUrl($csrf_token, $redirect_uri, $level = null) {

		return $this->bitbucket . '/site/oauth2/authorize?client_id='
		. urlencode($this->client_id) . '&response_type=code';
	}

	public function fetchAccessToken($code) {
		$headers                  = $this->default_headers;
		$headers['Authorization'] = 'Basic ' . base64_encode($this->client_id . ':' . $this->app_secret);
		unset($headers['Content-type']);
		$response = $this->http_client->request('POST', $this->bitbucket . '/site/oauth2/access_token',
			['form_params' => ['grant_type' => "authorization_code", 'code' => $code], 'headers' => $headers]
		);

		$raw_response = $response->getBody();
		Log::debug('[fetchAccessToken] - ' . $raw_response);

		$json = json_decode($raw_response);

		if (isset($json->access_token)) {
			$this->setToken($json->access_token);
		}

		$epoch = time();

		return new Tokens([
			'token'         => $this->token,
			'refresh_token' => $json->refresh_token,
			'expire_epoch'  => $epoch + ($json->expires_in - 10), //10 seconds buffer, just in case
		]);

	}

	public function getUserInfo() {
		$response = $this->http_client->request('GET', $this->api . '/2.0/user', ['headers' => $this->default_headers]);

		$raw_response = $response->getBody();

		Log::debug('[getUserInfo] - ' . $raw_response);
		$json = json_decode($raw_response);

		return new UserInfo(['login' => $json->username]);
	}

	public function getPullRequestData($url) {
		$pr_metadata = $this->splitPrUrl($url);

		if (!$pr_metadata) {
			return [false, 'Pull request url invalid'];
		}

		$api_url      = $this->api . '/2.0/repositories/' . $pr_metadata['owner'] . '/' . $pr_metadata['repository'] . '/pullrequests/' . $pr_metadata['id'];
		$response     = $this->http_client->request('GET', $api_url, ['headers' => $this->default_headers]);
		$raw_response = $response->getBody();

		Log::debug("[getPullRequestData][$url] $raw_response");
		$json = json_decode($raw_response);

		if (isset($json->links)) {

			return [true, new PullRequest([
				'name'        => $json->title,
				'url'         => $json->links->html->href,
				'description' => $json->description,
				'repository'  => $pr_metadata['owner'] . '/' . $pr_metadata['repository'],
				'language'    => '',
			])];
		}

		if (isset($json->error)) {

			if (isset($json->error->message)) {
				return [false, $json->error->message];
			}

		}

	}

	public function getAvailablePermissionLevels() {
		return [
			'maximum' => [
				'scope'         => 'repo',
				'description'   => 'Public Repos (Read,Write)',
				'can_create_pr' => true,
			],
		];
	}

	public function getCurrentPermissionLevel() {
		return 'maximum';
	}

	private function splitPrUrl($url) {
		$url_parts = array_reverse(explode('/', $url));

//https://bitbucket.org/owner/repository/pull-requests/id

		if (count($url_parts) < 5) {
			return false;
		}

		return [
			'id'         => htmlspecialchars($url_parts[0]),
			'owner'      => htmlspecialchars($url_parts[3]),
			'repository' => htmlspecialchars($url_parts[2]),
		];

	}

	public function listPullRequestsForRepo($owner, $repository) {

		$prs = $this->paginate(
			"/2.0/repositories/$owner/$repository/pullrequests",
			"[listPullRequestsForRepo][$owner/$repository]",
			'state=OPEN');

		$std_prs = array();

		foreach ($prs as $pr) {
			$std_prs[] = new PullRequest([
				'name' => $pr['title'],
				'url'  => $pr['links']['html']['href'],
			]);
		}

		return $std_prs;
	}

	public function updatePullRequest($owner, $repository, $url, $title, $description) {
		$pr_metadata = $this->splitPrUrl($url);
		$pr_id       = $pr_metadata['id'];

		if (!$pr_id) {
			return [false, 'Pull request url invalid'];
		}

		$api_url  = $this->api . "/2.0/repositories/$owner/$repository/pullrequests/$pr_id";
		$response = $this->http_client->request('PUT', $api_url,
			[
				'json'    => [
					'title'       => $title,
					'description' => strip_tags($description),
				],
				'headers' => $this->default_headers,
			]
		);
		$raw_response = $response->getBody();

		Log::debug("[updatePullRequest][$owner/$repository] $raw_response");
		$json = json_decode($raw_response);

		if (isset($json->links)) {
			return [true, null];
		}

		if (isset($json->error)) {

			if (isset($json->error->message)) {
				return [false, $json->error->message];
			}

		}

		return [false, 'API Error'];

	}

	public function createPullRequest($owner, $repository, $head, $base, $title, $description) {

		$request_data = [
			'source'      => [
				'branch' => [
					'name' => $head,
				],
			],
			'title'       => $title,
			'destination' => [
				'branch' => [
					'name' => $base,
				],
			],
			'description' => strip_tags($description),
		];

		$response = $this->http_client->request('POST', $this->api . "/2.0/repositories/$owner/$repository/pullrequests",
			['json'   => $request_data,
				'headers' => $this->default_headers,
			]
		);
		$raw_response = $response->getBody();
		Log::debug("[createPullRequest][$owner/$repository] - " . $raw_response);

		$json          = json_decode($raw_response);
		$error_message = 'Failed to create pull request';

		if (isset($json->links->html->href)) {
			return [
				'success' => 1,
				'url'     => $json->links->html->href,
			];
		}

		if (isset($json->error->message)) {
			$error_message = 'Error from Bitbucket : ' . $json->error->message;
		}

		return [
			'success' => 0,
			'error'   => $error_message,
		];

	}

	public function listBranchesForRepo($owner, $repository) {

		$branches = $this->paginate(
			"/2.0/repositories/$owner/$repository/refs/branches",
			"[listBranchesForRepo][$owner/$repository]");

		$std_branches = array();

		foreach ($branches as $branch) {
			$std_branches[] = new Branch([
				'name' => $branch['name'],
			]);
		}

		return $std_branches;
	}

	public function listRepositories() {
		//TODO Fetch the username and use the 2.0 api to be able to use pagination

		$response = $this->http_client->request('GET',
			$this->api . '/1.0/user/repositories',
			['headers' => $this->default_headers]
		);
		$raw_response = $response->getBody();
		Log::debug('[listRepositories] - ' . $raw_response);

		$repos     = json_decode($raw_response);
		$std_repos = array();

		foreach ($repos as $repo) {

			if ($repo->fork_of) {
				$org_repo    = $repo->fork_of;
				$std_repos[] = new Repository([
					'name'     => $org_repo->owner . '/' . $org_repo->slug,
					'id'       => $org_repo->slug,
					'url'      => $this->bitbucket . '/' . $org_repo->owner . '/' . $org_repo->slug,
					'language' => $org_repo->language,
				]);
			}

			$std_repos[] = new Repository([
				'name'     => $repo->owner . '/' . $repo->slug,
				'id'       => $repo->slug,
				'url'      => $this->bitbucket . '/' . $repo->owner . '/' . $repo->slug,
				'language' => $repo->language,
			]);
		}

		return $std_repos;
	}

	public function setToken($token) {
		$this->default_headers['Authorization'] = 'Bearer ' . $token;
		$this->token                            = $token;
	}

	public function refreshToken($refresh_token) {
		$headers                  = $this->default_headers;
		$headers['Authorization'] = 'Basic ' . base64_encode($this->client_id . ':' . $this->app_secret);
		unset($headers['Content-type']);
		$response = $this->http_client->request('POST',
			$this->bitbucket . '/site/oauth2/access_token',
			[
				'form_params' =>
				[
					'grant_type'    => 'refresh_token',
					'refresh_token' => $refresh_token,
				],
				'headers'     => $headers,
			]
		);
		$raw_response = $response->getBody();
		Log::debug('[refreshToken] - ' . $refresh_token);

		$json = json_decode($raw_response);

		if (isset($json->access_token)) {
			$this->setToken($json->access_token);
		}

		$epoch = time();

		return new Tokens([
			'token'         => $this->token,
			'refresh_token' => $json->refresh_token,
			'expire_epoch'  => $epoch + ($json->expires_in - 10), //10 seconds buffer, just in case
		]);
	}

	private function paginate($endpoint, $method_name, $params = '', $pagelen = 50) {
		$page  = 1;
		$fetch = true;
		$data  = [];

		while ($fetch) {
			$response = $this->http_client->request('GET',
				$this->api . '/' . $endpoint . "?pagelen=$pagelen&page=$page&$params",
				['headers' => $this->default_headers]
			);
			$raw_response = $response->getBody();
			Log::debug("[$method_name] Page $page - \n ==== \n " . $raw_response . "\n ==== \n");
			$current_data = json_decode($raw_response, true);

			if (isset($current_data['page'])) {

				if (count($current_data['values']) > 0) {
					$page++;
					$data = array_merge($current_data['values'], $data);
				} else {
					$fetch = false;
				}

			} else {
				Log::error("Error from bitbucket api during pagination for $method_name");
				$fetch = false;
			}

		}

		return $data;

	}

}
