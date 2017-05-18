<?php
namespace App\Classes\GitProviders;

interface GitProviderInterface {
	public function fetchAccessToken($code);

	public function getAuthorizeUrl($csrf_token, $redirect_uri);

	public function getPullRequest($owner, $repository, $pr_id);

	public function getUserInfo();

	public function listPullRequestsForRepo($owner, $repository);

	public function listRepositories();

	public function setToken($token);
}
