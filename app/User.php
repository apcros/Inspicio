<?php

namespace App;

use App\Classes\GitProviderFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

	public function __construct($user_id, $auto_load = false) {
		$this->user_id = $user_id;

		if ($auto_load) {
			$user        = $this->load();
			$this->name  = $user->name;
			$this->email = $user->email;
		}

	}

	/*
		        This is a method in case we need to add additional checks later
	*/
	public function getPoints() {
		$user = $this->load();

		return $user->points;
	}

	public function addPoints($count) {
		return DB::table('users')->where('id', $this->user_id)->increment('points', $count);
	}

	public function removePoint() {
		return DB::table('users')->where('id', $this->user_id)->decrement('points');
	}

	public function getGitAccount($account_id) {
		$user_id = $this->user_id;
		$account = DB::table('accounts')->where([
			['user_id', '=', $user_id],
			['id', '=', $account_id]])->first();

		if ($account->refresh_token) {
			Log::info("[USER $user_id] Account " . $account->id . ' expire at ' . $account->expire_epoch);

			if ($account->expire_epoch <= time()) {

				$client = $this->getAccountClient($account, false);
				$tokens = $client->refreshToken($account->refresh_token);

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

	public function getAccountClient($account, $set_token = true) {
		$factory = new GitProviderFactory($account->provider);
		$client  = $factory->getProviderEngine();

		if ($set_token) {
			$client->setToken($account->token);
		}

		return $client;
	}

	public function getAvailableAccounts() {
		return DB::table('accounts')->where('user_id', $this->user_id)->get();
	}

	public function load() {
		return DB::table('users')
			->where('id', $this->user_id)
			->first();
	}

	public function routeNotificationForMail() {
		return $this->email;
	}

	public function getKey() {
		return $this->email;
	}

	public function getId() {
		return $this->user_id;
	}

}
