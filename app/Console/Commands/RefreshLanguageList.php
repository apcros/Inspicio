<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RefreshLanguageList extends Command {
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'refreshlanguagelist';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'This will refresh the language list';

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
		$this->info("Starting the language fetch..");
		$endpoint = "https://en.wikipedia.org/w/api.php?action=query&titles=List_of_programming_languages&prop=links&format=json&continue=||";

		$languages        = array();
		$availableResults = true;
		$continue         = '';

		while ($availableResults) {
			$this->info('Fetching.');
			$raw_page     = file_get_contents($endpoint . $continue);
			$current_page = json_decode($raw_page);

			foreach ($current_page->query->pages->{'144146'}
				->links as $key => $link) {
                
				$languages[] = str_replace('(programming language)', '', $link->title);
			}

			if (isset($current_page->continue)) {
				$continue = "&plcontinue=" . $current_page->continue->plcontinue;
				$this->info("Current continue value : $continue");
			} else {
				$availableResults = false;
			}

		}

		$file_path = database_path() . '/migrations/fixtures/languages.json';

//This is really horrible, but it's late I'm pissed at array_slice

//And it's just a artisan command.

//Dear Futur me : I'm sorry

//(Also need theses element to be removed as it's garbage links from the Wikipedia api)
		for ($i = 0; $i < 4; $i++) {
			array_pop($languages);
		}

		file_put_contents($file_path, json_encode($languages));
		$this->info('File saved to ' . $file_path);

	}

}
