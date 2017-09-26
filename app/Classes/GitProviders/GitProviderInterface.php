<?php
namespace App\Classes\GitProviders;

interface GitProviderInterface {
	public function fetchAccessToken($code);

	public function getAuthorizeUrl($csrf_token, $redirect_uri, $level);

	public function getUserInfo();

	public function listPullRequestsForRepo($owner, $repository);

	public function updatePullRequest($owner, $repository, $pr_id, $title, $description);

	public function createPullRequest($owner, $repository, $head, $base, $title, $description);

	public function getPullRequestData($url);

	public function listBranchesForRepo($owner, $repository);

	public function listRepositories();

	public function setToken($token);

	public function refreshToken($refresh_token);

	public function getAvailablePermissionLevels();

	public function getCurrentPermissionLevel();

}
