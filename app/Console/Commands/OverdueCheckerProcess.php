<?php

namespace App\Console\Commands;

use App\OverdueChecker;
use Illuminate\Console\Command;

class OverdueCheckerProcess extends Command {
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'overduechecker:process';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Will check all overdue actions and send email accordingly.';

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
		$checker = new \App\OverdueChecker();
		$checker->run();
	}
}
