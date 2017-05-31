<?php
namespace App\Classes\GitProviders;

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

		Log::debug('[fetchAccessToken] - '.$raw_response);

		$json = json_decode($raw_response);

		if (isset($json->access_token)) {
			$this->setToken($json->access_token);
		}

		$epoch = time();

		return [
			'token'         => $this->token,
			'refresh_token' => $json->refresh_token,
			'expire_epoch'  => $epoch + ($json->expires_in - 10), //10 seconds buffer, just in case
		];

	}

	public function getUserInfo() {
		$raw_response = $this->ua->get($this->api . '/2.0/user');

		Log::debug('[getUserInfo] - '.$raw_response);
		$json = json_decode($raw_response);

		return (object) ['login' => ucfirst(strtolower($json->username))];
	}

	public function listPullRequestsForRepo($owner, $repository) {
		$raw_response = $this->ua->get($this->api . "/2.0/repositories/$owner/$repository/pullrequests?pagelen=50&state=OPEN");
		Log::debug("[listPullRequestsForRepo][$owner/$repository] - ".$raw_response);

		$json    = json_decode($raw_response);
		$std_prs = array();

		foreach ($json->values as $key => $pr) {
			$std_prs[] = array(
				'name' => $pr->title,
				'url'  => $pr->links->html->href,
			);
		}

		return $std_prs;
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
			'description' => $description,
		]);

		$raw_response = $this->ua->post($this->api . "/2.0/repositories/$owner/$repository/pullrequests", $request_data);
		Log::debug("[createPullRequest][$owner/$repository] - ".$raw_response);

		$json          = json_decode($raw_response);
		$error_message = 'Failed to create pull request';

		if (isset($json->links->html->href)) {
			return [
				'success' => 1,
				'url'     => $json->links->html->href,
			];
		}

		if (isset($json->error->message)) {
			$error_message = 'Error from Bitbucket : '.$json->error->message;
		}

		return [
			'success' => 0,
			'error'   => $error_message,
		];

	}

	public function listBranchesForRepo($owner, $repository) {
		$raw_response = $this->ua->get($this->api . "/2.0/repositories/$owner/$repository/refs/branches?pagelen=100");
		Log::debug("[listBranchesForRepo][$owner/$repository] - ".$raw_response);

		$json = json_decode($raw_response);

		$std_branches = array();

		foreach ($json->values as $key => $branch) {
			$std_branches[] = array(
				'name' => $branch->name,
			);
		}

		return $std_branches;
	}

	public function listRepositories() {
		$raw_response = $this->ua->get($this->api . '/1.0/user/repositories?pagelen=100');
		Log::debug('[listRepositories] - '.$raw_response);

		$repos     = json_decode($raw_response);
		$std_repos = array();

		foreach ($repos as $key => $repo) {
			$std_repos[] = array(
				'name'     => $repo->owner . '/' . $repo->slug,
				'id'       => $repo->slug,
				'url'      => $this->bitbucket . '/' . $repo->owner . '/' . $repo->slug,
				'language' => $repo->language,
			);
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

		return [
			'token'         => $this->token,
			'refresh_token' => $json->refresh_token,
			'expire_epoch'  => $epoch + ($json->expires_in - 10), //10 seconds buffer, just in case
		];
	}

}
