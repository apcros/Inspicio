<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User {
	use Notifiable;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name', 'email',
	];

	private $user_id;

	public function __construct($user_id) {
		$this->user_id = $user_id;
	}

	/*
		        This is a method in case we need to add additional checks later
	*/
	public function getPoints() {
		$user = $this->load();

		return $user->points;
	}

	public function getGitAccount($account_id) {
		$user_id = $this->user_id;
		$account = DB::table('accounts')->where([
			['user_id', '=', $user_id],
			['id', '=', $account_id]])->first();

		if ($account->refresh_token) {
			Log::info("[USER $user_id] Account " . $account->id . ' expire at ' . $account->expire_epoch);

			if ($account->expire_epoch <= time()) {

				$factory = new GitProviderFactory($account->provider);
				$client  = $factory->getProviderEngine();
				$tokens  = $client->refreshToken($account->refresh_token);

				Log::info("[USER $user_id] Token expired, refreshing for $user_id (Account $account_id)");

				DB::table('accounts')->where('id', $account_id)->update([
					'token'        => $tokens->token,
					'expire_epoch' => $tokens->expire_epoch,
					'updated_at'   => \Carbon\Carbon::now(),
				]);

				$account = DB::table('accounts')->where([
					['user_id', '=', $user_id],
					['id', '=', $account_id]])->first();
			}

		}

		return $account;
	}

	public function load() {
		return DB::table('users')
			->select('points')
			->where('id', $this->user_id)
			->first();
	}

	public function routeNotificationForMail() {
		return $this->email;
	}

	public function getKey() {
		return $this->email;
	}

}
