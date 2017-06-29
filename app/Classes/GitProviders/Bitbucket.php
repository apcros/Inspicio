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
 *  A simple API client for Bitbucket,  handle OAuth login
 */
class Bitbucket implements GitProviderInterface {
	private $api = 'https://api.bitbucket.org';

	private $app_secret;

	private $client_id;

	private $bitbucket = 'https://bitbucket.org';

	private $token = '';

	private $ua;

	//Public attribute, I'll go burn in hell
	public $csrf_enabled = false;

	function __construct($client_id, $app_secret, $ua = null) {
		$this->client_id  = $client_id;
		$this->app_secret = $app_secret;

//Makes mocking way easier
		if ($ua != null) {
			$this->ua = $ua;
		} else {
			$this->ua = new UserAgent();
		}

	}

	public function getAuthorizeUrl($csrf_token, $redirect_uri) {

		return $this->bitbucket . '/site/oauth2/authorize?client_id='
		. urlencode($this->client_id) . '&response_type=code';
	}

	public function fetchAccessToken($code) {
		$this->ua->addHeader('Authorization: Basic ' . base64_encode($this->client_id . ':' . $this->app_secret));
		$raw_response = $this->ua->post($this->bitbucket . '/site/oauth2/access_token',
			['grant_type' => "authorization_code", 'code' => $code]
		);

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
		$raw_response = $this->ua->get($this->api . '/2.0/user');

		Log::debug('[getUserInfo] - ' . $raw_response);
		$json = json_decode($raw_response);

		return new UserInfo(['login' => $json->username]);
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
		$url_parts = array_reverse(explode('/', $url));
		$pr_id     = $url_parts[0];

		if (!$pr_id) {
			return [false, 'Pull request url invalid'];
		}

		$this->ua->addHeader('Content-Type: application/json');
		$api_url      = $this->api . "/2.0/repositories/$owner/$repository/pullrequests/$pr_id";
		$raw_response = $this->ua->put($api_url, json_encode([
			'title'       => $title,
			'description' => strip_tags($description),
		]));

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
		$this->ua->addHeader('Content-Type: application/json');
		$request_data = json_encode([
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
		]);

		$raw_response = $this->ua->post($this->api . "/2.0/repositories/$owner/$repository/pullrequests", $request_data);
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
		$raw_response = $this->ua->get($this->api . '/1.0/user/repositories');
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
		$this->ua->setHeaders(['Authorization: Bearer ' . $token]);
		$this->token = $token;
	}

	public function refreshToken($refresh_token) {
		$this->ua->addHeader('Authorization: Basic ' . base64_encode($this->client_id . ':' . $this->app_secret));
		$raw_response = $this->ua->post($this->bitbucket . '/site/oauth2/access_token',
			['grant_type' => 'refresh_token', 'refresh_token' => $refresh_token]
		);

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
			$raw_response = $this->ua->get($this->api . '/' . $endpoint . "?pagelen=$pagelen&page=$page&$params");
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
