<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
	protected $description = 'This will refresh the language list in db';

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

        $languages = $this->loadLatestLanguageList();

        $skills_count = DB::table('skills')->count();

        if($skills_count == count($languages)) {
            $this->info("Same number of skills/languages in DB and in the fixtures. Ignoring refresh");
            return 1;
        }

        foreach ($languages as $language) {
            $is_already_present = DB::table('skills')->where('name',$language)->first();

            if(!$is_already_present) {
                $this->info("New language detected : inserting $language in DB");
                $id_inserted = DB::table('skills')->insertGetId(['name' => $language]);
                $this->info("Inserted with success. ID is $id_inserted");
            }
        }

		$this->info("Finished language refresh");


	}

    private function loadLatestLanguageList() {
        $json_file = file_get_contents(database_path() . '/migrations/fixtures/languages.json');
        $languages = json_decode($json_file);
        return $languages;
    }

}
