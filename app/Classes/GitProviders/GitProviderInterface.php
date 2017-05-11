<?php
namespace App\Classes\GitProviders;

interface GitProviderInterface {

	public function getAuthorizeUrl($csrf_token, $redirect_uri);
	public function fetchAccessToken($code);
	public function getUserInfo();
	public function setToken($token);

	public function listRepositories();
	public function listPullRequestsForRepo($repository);
}