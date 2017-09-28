<?php

namespace App\Http\Controllers;

use App\Classes\GitProviderFactory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use \Ramsey\Uuid\Uuid;

class OAuthLogin extends Controller {
	public function logout(Request $request) {
		$request->session()->flush();

		return redirect('/');
	}

	public function stepOne(Request $request, $provider) {
		$permission_level = 'minimum';
		if($request->has('perm_level')){
			$permission_level = $request->input('perm_level');
		}

		$client = $this->getClient($provider);

		$token = csrf_token();
		session(['oauth_csrf' => $token]);
		$redirect_to = $client->getAuthorizeUrl($token, env('APP_URL') . '/oauth/callback/' . $provider, $permission_level);

		Log::info("[$provider] - OAuth Redirect (Step one)");
		Log::debug("Redirect url : $redirect_to");

		return redirect($redirect_to);
	}

	private function stepTwoNewAccount(Request $request, $provider) {
		$code   = $request->input('code');
		$state  = $request->input('state');
		$client = $this->getClient($provider);

		$tokens    = $client->fetchAccessToken($code);
		$user_data = $client->getUserInfo();
		$login     = $user_data->login;
		$user_id   = session('user_id');

		if (!isset($user_data->login)) {
			Log::warning("[USER $user_id] - No user data login");

			return view('home', ['error_message' => 'Failed to add your account']);
		}

		if ($client->csrf_enabled) {

			if ($state != session('oauth_csrf')) {
				Log::error("[USER $user_id] CSRF mismatch");

				return view('home', ['error_message' => 'CSRF Token mismatch']);
			}

		}

		$duplicate_account = DB::table('accounts')->where([
			['login', '=', $login],
			['provider', '=', $provider],
			['user_id', '!=', $user_id],
		])->first();

		if ($duplicate_account) {
			Log::warning("[USER $user_id] Duplicate account (id : " . $duplicate_account->id . ')');

			return view('home', ['error_message' => 'This account is already in use']);
		}

		$current_account = DB::table('accounts')->where([
			['login', '=', $login],
			['provider', '=', $provider],
			['user_id', '=', $user_id],
		])->first();
		$permission_level = $client->getCurrentPermissionLevel();

		if ($current_account) {

			DB::table('accounts')->where('id', $current_account->id)->update([
				'token'            => $tokens->token,
				'refresh_token'    => $tokens->refresh_token,
				'expire_epoch'     => $tokens->expire_epoch,
				'updated_at'       => \Carbon\Carbon::now(),
				'permission_level' => $permission_level,
			]);
			Log::info("[USER $user_id ] - Updated permission level to $permission_level on $login ($provider)");

			return redirect('/account');
		}

		$account_id = Uuid::uuid4()->toString();
		DB::table('accounts')->insert(
			[
				'id'               => $account_id,
				'provider'         => $provider,
				'login'            => $login,
				'token'            => $tokens->token,
				'refresh_token'    => $tokens->refresh_token,
				'expire_epoch'     => $tokens->expire_epoch,
				'user_id'          => $user_id,
				'permission_level' => $permission_level,
				'is_main'          => false,
				'created_at'       => \Carbon\Carbon::now(),
				'updated_at'       => \Carbon\Carbon::now(),
			]
		);
		Log::info("[USER $user_id] - Added $provider account $login");

		return redirect('/account');
	}

	public function stepTwo(Request $request, $provider) {
		$code  = $request->input('code');
		$state = $request->input('state');

		if (session('user_id')) {
			return $this->stepTwoNewAccount($request, $provider);
		}

		$client = $this->getClient($provider);

		Log::debug('Exchanging ' . $provider . ' temporary code (' . $code . ') to access token');
		$tokens = $client->fetchAccessToken($code);

		Log::debug('Fetching user data associated with token');
		$user_data = $client->getUserInfo();

		if (!$user_data) {
			Log::warning('No user data login');

			return view('choose-auth-provider', ['error_message' => "Failed to validate user information from $provider"]);

			return redirect('/');
		}

		if ($client->csrf_enabled) {

			if ($state != session('oauth_csrf')) {
				Log::error('CSRF mismatch');

				return view('choose-auth-provider', ['error_message' => 'CSRF Token mismatch']);

			}

		}

		Log::info('Achieved stepTwo OAuth, user is : ' . $user_data->login);

		$user               = $this->getUser($user_data->login, $provider);
		$current_permission = $client->getCurrentPermissionLevel();

		if ($user) {

			$account = DB::table('accounts')->where([
				['login', '=', $user_data->login],
				['user_id', '=', $user->id],
				['provider', '=', $provider],
			])->first();

			$account_update = [
				'provider'      => $provider,
				'refresh_token' => $tokens->refresh_token,
				'expire_epoch'  => $tokens->expire_epoch,
				'updated_at'    => \Carbon\Carbon::now(),
			];

/*
If the permission level on the saved account is different
that means the user raised the permission settings (Since
login use the minimum permissions level).
Because of that we don't replace the token to avoid lowering
the permission level.
 */
			if ($account->permission_level == $current_permission) {
				$account_update['token'] = $tokens->token;
			}

			DB::table('accounts')->where([
				['login', '=', $user_data->login],
				['user_id', '=', $user->id],
				['provider', '=', $provider],
			])->update($account_update);

			session(['user_nickname' => $user->nickname, 'user_email' => $user->email, 'user_id' => $user->id]);

			Log::info('[USER ' . $user->id . '] Logged in. Email : ' . $user->email);

			return redirect('/');
		} else {
			session(['user_nickname' => $user_data->login, 'user_permission_level' => $current_permission]);

			return view('register', [
				'auth_token'    => $tokens->token,
				'refresh_token' => $tokens->refresh_token,
				'expire_epoch'  => $tokens->expire_epoch,
				'auth_provider' => $provider,
			]);
		}

	}

	private function getClient($provider) {
		$factory = new GitProviderFactory($provider);

		return $factory->getProviderEngine();
	}

	private function getUser($login, $provider) {

		$account = DB::table('accounts')->where([
			['login', '=', $login],
			['provider', '=', $provider],
		])->first();

		if (isset($account)) {
			$user = DB::table('users')->where('id', $account->user_id)->first();

			return $user;
		}

		return false;
	}

}
