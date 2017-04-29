<?php
namespace App\Classes;
/**
*  A simple API client for Github,  handle OAuth login
*/
class Github 
{
	private $client_id;
	private $app_secret;
	private $token = '';
	private $endpoint = 'https://github.com';
	
	function __construct($client_id, $app_secret)
	{
		$this->client_id = $client_id;
		$this->app_secret = $app_secret;
	}
	/*
		get_authorize_url will simply return a string where the user should be redirected to start the process of 
		oauth auth.
	*/
	function get_authorize_url($csrf_token, $redirect_uri) {
		return $this->endpoint.'/login/oauth/authorize?client_id='
		.urlencode($this->client_id).'&state='
		.urlencode($csrf_token).'&redirect_uri='
		.urlencode($redirect_uri).'&scope=user';
	}

	function set_token($token) {
		$this->token = $token;
	}

	/*
		Gets the GiHub temporary "code" and turns it into an access_token
	*/
	function fetch_access_token($code) {

		$response = $this->query('login/oauth/access_token',CURLOPT_POST,array(
			'client_id' => $this->client_id,
			'client_secret' => $this->app_secret,
			'code' => $code
		));

		$this->token = $response['access_token'];
		return $this->token;
	}

	/*
		Simply returns the user, useful for auth purposes on the website
	*/
	function get_user_info() {
		return $this->query('/user', CURLOPT_HTTPGET);
	}

	/*
		Generic private method to abstract away the curl methods
	*/
	private function query($method, $curl_opt, $data = null) {
		$curl = curl_init();
		curl_setopt($curl, $curl_opt, 1);

        if ($data) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }

        $headers = array(
		    'Content-type: application/json',
		);

		if($this->token != '') {
			$headers[] = 'Authorization: token '.$this->token;
		}
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_URL, $this->endpoint.$method);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$raw_result = curl_exec($curl);

		return json_decode($raw_result);

	}
}

?>