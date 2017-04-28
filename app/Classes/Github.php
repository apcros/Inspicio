<?php
namespace App\Classes;
/**
*  A simple API client for Github,  handle OAuth login
*/
class Github 
{
	private $client_id;
	private $app_secret;
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
		return $this->endpoint.'/login/oauth/authorize?=client_id='
		.urlencode($this->client_id).'&state='
		.urlencode($csrf_token).'&redirect_uri='
		.urlencode($redirect_uri);
	}
}

?>