<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Classes\Github;
use App\Classes\UserAgent;

class GithubTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testAuthorizeUrl()
    {
    	$client = new Github('d&é"&éummy- test', 'dum"&é"&²émy test');
    	$authorize_url = $client->getAuthorizeUrl('dummy','dummy');

        $this->assertEquals('https://github.com/login/oauth/authorize?client_id=d%26%C3%A9%22%26%C3%A9ummy-+test&state=dummy&redirect_uri=dummy&scope=user',$authorize_url);
    }

    public function testFetchAccessToken()
    {
    	$mocked_ua = $this->createMock(UserAgent::class);
    	$mocked_ua->method('post')->willReturn('{"access_token": "thisisafaketoken"}');
    	$client = new Github("testest", "testest",$mocked_ua);

    	$this->assertEquals($client->fetchAccessToken('dummy'),'thisisafaketoken');
    }

}
