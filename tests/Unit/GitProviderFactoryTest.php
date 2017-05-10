<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Classes\GitProviderFactory;

class GitProviderFactoryTest extends TestCase
{

    public function testHappyPath()
    {
    	$factory = new GitProviderFactory('github');

        $this->assertInstanceOf(\App\Classes\Github::class,$factory->getProviderEngine());
    }

    public function testUnknownFactory()
    {
        $factory = new GitProviderFactory('idontexist');

        $this->expectException(\Error::class);
        $factory->getProviderEngine();
    }

}
