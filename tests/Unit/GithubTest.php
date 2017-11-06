<?php

namespace Tests\Unit;

use App\Classes\GitProviders\Github;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Response;
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
		$handler = $this->mockHttpClientHandler([
			[
				'name' => 'oauth-one',
			],
		]);

		$client = new Github("testest", "testest", $handler);

		$this->assertEquals($client->fetchAccessToken('dummy'), new \App\Classes\Models\Git\Tokens([
			'token'         => 'thisisatoken',
			'refresh_token' => null,
			'expire_epoch'  => null,
		]));
	}

	public function testGetUserInfo() {

		$handler = $this->mockHttpClientHandler([
			[
				'name' => 'get_user_info',
			],
		]);

		$client = new Github("testest", "testest", $handler);

		$user = $client->getUserInfo();

		$this->assertInstanceOf(\App\Classes\Models\Git\UserInfo::class, $user);
		$this->assertEquals('This_is_the_nickname', $user->login);
	}

	private function mockHttpClientHandler($methods) {
		$responses = [];

		foreach ($methods as $method) {
			$body_content = file_get_contents(resource_path('test_fixtures/github/' . $method['name'] . '.json'));
			unset($method['name']);
			$headers     = $method;
			$body        = Psr7\stream_for($body_content);
			$responses[] = new Response(200, $headers, $body);
		}

		$mock = new MockHandler($responses);

		return HandlerStack::create($mock);
	}

}
