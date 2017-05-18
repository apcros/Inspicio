<?php

namespace Tests\Unit;

use App\Classes\GitProviderFactory;
use Tests\TestCase;

class GitProviderFactoryTest extends TestCase {

	public function testHappyPath() {
		$factory = new GitProviderFactory('github');

		$this->assertInstanceOf(\App\Classes\GitProviders\Github::class, $factory->getProviderEngine());
	}

	public function testUnknownFactory() {
		$factory = new GitProviderFactory('idontexist');

		$this->expectException(\Error::class);
		$factory->getProviderEngine();
	}

}
