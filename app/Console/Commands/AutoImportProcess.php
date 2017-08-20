<?php

namespace App\Console\Commands;

use App\AutoImport;
use Illuminate\Console\Command;

class AutoImportProcess extends Command {
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'autoimport:process';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'This will look through all the setup Auto Import and process them';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle() {
		$auto_import = new \App\AutoImport();
		$auto_import->run();
	}
}
