<?php

namespace Tests\Unit;

use App\Classes\GitProviders\Github;
use App\Classes\UserAgent;
use Tests\TestCase;

class GithubTest extends TestCase {
	/**
	 * A basic test example.
	 *
	 * @return void
	 */
	public function testAuthorizeUrl() {
		$client        = new Github('d&é"&éummy- test', 'dum"&é"&²émy test');
		$authorize_url = $client->getAuthorizeUrl('dummy', 'dummy');

		$this->assertEquals('https://github.com/login/oauth/authorize?client_id=d%26%C3%A9%22%26%C3%A9ummy-+test&state=dummy&redirect_uri=dummy&scope=', $authorize_url);

		$authorize_url = $client->getAuthorizeUrl('dummy', 'dummy', 'maximum');
		$this->assertEquals('https://github.com/login/oauth/authorize?client_id=d%26%C3%A9%22%26%C3%A9ummy-+test&state=dummy&redirect_uri=dummy&scope=repo', $authorize_url);
	}

	public function testFetchAccessToken() {

		$mocked_ua = $this->createMock(UserAgent::class);
		$this->mockGithubMethod('oauth-one', 'post', $mocked_ua);

		$client = new Github("testest", "testest", $mocked_ua);

		$this->assertEquals($client->fetchAccessToken('dummy'), new \App\Classes\Models\Git\Tokens([
			'token'         => 'thisisatoken',
			'refresh_token' => null,
			'expire_epoch'  => null,
		]));
	}

	public function testGetUserInfo() {
		$mocked_ua = $this->createMock(UserAgent::class);
		$this->mockGithubMethod('get_user_info', 'get', $mocked_ua);

		$client = new Github("test", "test", $mocked_ua);

		$user = $client->getUserInfo();

		$this->assertInstanceOf(\App\Classes\Models\Git\UserInfo::class, $user);
		$this->assertEquals('This_is_the_nickname', $user->login);
	}

	private function mockGithubMethod($method, $type, $ua) {
		$ua->method($type)->willReturn(file_get_contents(resource_path('test_fixtures/github/' . $method . '.json')));
	}
}
