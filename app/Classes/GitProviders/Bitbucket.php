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

		Log::debug($raw_response);

	}

	public function getPullRequest($owner, $repository, $pr_id) {

	}

	public function getUserInfo() {

	}

	public function listPullRequestsForRepo($owner, $repository) {

	}

	public function createPullRequest($owner, $repository, $head, $base, $title, $description) {

	}

	public function listBranchesForRepo($owner, $repository) {

	}

	public function listRepositories() {

	}

	public function setToken($token) {
		$this->ua->addHeader('Authorization: token ' . $token);
		$this->token = $token;
	}

}
