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

		Log::debug($raw_response);

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

	public function getPullRequest($owner, $repository, $pr_id) {

	}

	public function getUserInfo() {
		$raw_response = $this->ua->get($this->api . '/2.0/user');

		Log::debug($raw_response);
		$json = json_decode($raw_response);

		return (object) ['login' => $json->username];
	}

	public function listPullRequestsForRepo($owner, $repository) {
		return [];
	}

	public function createPullRequest($owner, $repository, $head, $base, $title, $description) {

	}

	public function listBranchesForRepo($owner, $repository) {
		return [];
	}

	public function listRepositories() {
		return [];
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

		Log::debug('Refresh token : ' . $refresh_token);

		$json = json_decode($raw_response);

		if (isset($json->access_token)) {
			$this->setToken($json->access_token);
		}

		return [
			'token'         => $this->token,
			'refresh_token' => $json->refresh_token,
			'expire_epoch'  => $epoch + ($json->expires_in - 10), //10 seconds buffer, just in case
		];
	}

}
