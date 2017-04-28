<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Classes\Github;

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
    	$authorize_url = $client->get_authorize_url('dummy','dummy');

        $this->assertEquals('https://github.com/login/oauth/authorize?=client_id=d%26%C3%A9%22%26%C3%A9ummy-+test&state=dummy&redirect_uri=dummy',$authorize_url);
    }
}
