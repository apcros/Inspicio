<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncSettings extends Command {
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'syncSettings';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'This will sync the settings in DB using the flat file';

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

		$settings = $this->loadSettingsFromJson();

		foreach ($settings as $setting) {
			$key = $setting['key'];

			$current_setting = DB::table('settings')->where('key', $key)->first();

			if ($current_setting) {

				if (
					$current_setting->value != $setting['value'] ||
					$current_setting->name != $setting['name'] ||
					$current_setting->type != $setting['type'] ||
					$current_setting->category != $setting['category']
				) {
					$this->info("$key Changed new value :" . var_export($setting, true));
					DB::table('settings')
						->where('key', $key)
						->update($setting);
				}

			}

			if (!$current_setting) {
				$this->info("New setting detected : inserting $key in DB : " . var_export($setting, true));
				DB::table('settings')->insert(
					$setting
				);
				$this->info("Inserted $key with success");
			}

		}

		$this->info("Finished settings refresh");

	}

	private function loadSettingsFromJson() {
		$json_file = file_get_contents(database_path() . '/migrations/fixtures/settings.json');

		return json_decode($json_file, true);
	}

}
