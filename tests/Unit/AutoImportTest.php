<?php

namespace Tests\Unit;

use App\Classes\AutoImport;
use App\Classes\Models\Git\PullRequest;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AutoImportTest extends TestCase {
	use DatabaseMigrations;

	public function testAutoImport() {
		$this->seed('DatabaseSeederForTests');

		$stub = $this->getMockBuilder(App\Classes\GitProviderFactory::class)
			->setMethods(['listPullRequestsForRepo', 'getPullRequestData'])
			->getMock();

		$stub->method('listPullRequestsForRepo')
			->willReturn([
				new PullRequest([
					'name' => 'blah blah PR1',
					'url'  => 'https://github/thisisaurl1',
				]),
				new PullRequest([
					'name' => 'blah blah PR2',
					'url'  => 'https://github/thisisaurl2',
				]),
				new PullRequest([
					'name' => 'blah blah PR2',
					'url'  => 'https://github/thisisaurl2',
				]),
			]);

		$stub->method('getPullRequestData')
			->willReturn([true, new PullRequest([
				'name'        => 'blah blah',
				'url'         => 'https://github/thisisaurl2',
				'description' => 'Amazing description',
				'repository'  => 'blah/blah',
				'language'    => 'PHP',
			])]);

		$auto_import = $this->getMockBuilder(\App\AutoImport::class)->setMethods(['getGitClient'])->getMock();

		$auto_import->method('getGitClient')
			->willReturn($stub);

		$auto_import->run();

		$success = DB::table('auto_imports_result')->where('is_success', true)->count();

		$this->assertEquals(4, $success);

	}

}