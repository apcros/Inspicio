<?php
namespace App\Classes\GitProviders;

use App\Classes\UserAgent;
use Illuminate\Support\Facades\Log;
/**
*  A simple API client for Github,  handle OAuth login
*/
class Github implements GitProviderInterface
{
	private $client_id;
	private $app_secret;
	private $token = '';

	private $github = 'https://github.com';
	private $api = 'https://api.github.com';

	private $ua;

	function __construct($client_id, $app_secret, $ua = null)
	{
		$this->client_id = $client_id;
		$this->app_secret = $app_secret;

		//Makes mocking way easier
		if($ua != null) {
			$this->ua = $ua;
		} else {
			$this->ua = new UserAgent();
			$this->ua->setHeaders(
				array(
			    'Content-type: application/json',
			    'Accept: application/json',
			    'User-Agent: Inspicio'
			));
		}

	}
	/*
		get_authorize_url will simply return a string where the user should be redirected to start the process of 
		oauth auth.
	*/
	public function getAuthorizeUrl($csrf_token, $redirect_uri) {
		return $this->github.'/login/oauth/authorize?client_id='
		.urlencode($this->client_id).'&state='
		.urlencode($csrf_token).'&redirect_uri='
		.urlencode($redirect_uri).'&scope=user';
	}

	public function setToken($token) {
		$this->ua->addHeader('Authorization: token '.$token);
		$this->token = $token;
	}

	/*
		Gets the GiHub temporary "code" and turns it into an access_token
	*/
	public function fetchAccessToken($code) {

		$raw_response = $this->ua->post($this->github.'/login/oauth/access_token',json_encode(array(
			'client_id' => $this->client_id,
			'client_secret' => $this->app_secret,
			'code' => $code
		)));

		$json = json_decode($raw_response);

		if (isset($json->access_token))
			$this->setToken($json->access_token);


		return $this->token;
	}

	/*
		Simply returns the user, useful for auth purposes on the website
	*/
	public function getUserInfo() {
		$raw_response = $this->ua->get($this->api.'/user');
		Log::debug('User info : '.$raw_response);
		return json_decode($raw_response);
	}

	public function listRepositories() {
		$raw_response = $this->ua->get($this->api.'/user/repos');
		Log::debug($raw_response);
		$repos = json_decode($raw_response);

		//We need to standarize the format
		$std_repos = array();
		foreach ($repos as $key => $repo) {
			$std_repos[] = array(
				'name' 	=> $repo->full_name,
				'id'	=> $repo->id,
				'url'	=> $repo->url, 
			);
		}
		return $std_repos;
	}

	public function listPullRequestsForRepo($repository) {

	}

}

?>