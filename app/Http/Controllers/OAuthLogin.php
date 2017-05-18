<?php

namespace App\Http\Controllers;

use App\Classes\GitProviderFactory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OAuthLogin extends Controller {
	public function logout(Request $request) {
		$request->session()->flush();

		return view('home');
	}

	public function stepOne($provider) {
		$client = $this->getClient($provider);
		//TODO make use of the CSRF token
		$redirect_to = $client->getAuthorizeUrl('DUMMY', env('APP_URL') . '/oauth/callback/' . $provider);
		Log::info('Redirecting user to OAuth on ' . $provider);

		return redirect($redirect_to);
	}

	public function stepTwo(Request $request, $provider) {
		$code = $request->input('code');

		$client = $this->getClient($provider);

		Log::debug('Exchanging ' . $provider . ' temporary code (' . $code . ') to access token');
		$access_token = $client->fetchAccessToken($code);

		Log::debug('Access token fetched.');

		Log::debug('Fetching user data associated with token');
		$user_data = $client->getUserInfo();

		if (!isset($user_data->login)) {
			return 'Login error';
		}

		Log::info('Achieved stepTwo OAuth, user is : ' . $user_data->login);

		$user = $this->getUser($user_data->login, $provider);

		if ($user) {

			DB::table('accounts')->where([
				['is_main', '=', true],
				['user_id', '=', $user->id],
			])->update(['provider' => $provider, 'token' => $access_token, 'updated_at' => \Carbon\Carbon::now()]);

			session(['user_nickname' => $user->nickname, 'user_email' => $user->email, 'user_id' => $user->id]);

			Log::info($user->email . ' Logged in !');

			return redirect('/');
		} else {
			session(['user_nickname' => $user_data->login]);

			return view('register', ['auth_token' => $access_token, 'auth_provider' => $provider]);
		}

	}

	private function getClient($provider) {
		$factory = new GitProviderFactory($provider);

		return $factory->getProviderEngine();
	}

	private function getUser($login, $provider) {

/*If there's an account with that login, provider and it's the main account (= account used to login)
Then we can fetch the associated user*/
		$account = DB::table('accounts')->where([
			['login', '=', $login],
			['is_main', '=', true],
			['provider', '=', $provider],
		])->first();

		if (isset($account)) {
			$user = DB::table('users')->where('id', $account->user_id)->first();

			return $user;
		}

		return false;
	}

}
