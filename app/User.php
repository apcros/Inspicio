<?php

namespace App;

use App\Classes\GitProviderFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class User {
	use Notifiable;

	private $user_id;
	public $data;

	public function __construct($user_id) {
		$this->user_id = $user_id;
		$this->load();
	}

	/*
		        This is a method in case we need to add additional checks later
	*/
	public function getPoints() {
		$this->load();

		return $this->data->points;
	}

	public function addPoints($count) {
		$result = DB::table('users')->where('id', $this->user_id)->increment('points', $count);
		$this->load();

		return $result;
	}

	public function removePoint() {
		$result = DB::table('users')->where('id', $this->user_id)->decrement('points');
		$this->load();

		return $result;
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
		$user = DB::table('users')
			->where('id', $this->user_id)
			->first();
		$this->data = $user;

		return $this->data;
	}

	public function routeNotificationForMail() {
		return $this->data->email;
	}

	public function getKey() {
		return $this->data->email;
	}

	public function getId() {
		return $this->user_id;
	}

}
