<?php

namespace App\Http\Controllers;

//Todo : Factory for OAuth clients
use App\Classes\Github;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class OAuthLogin extends Controller
{

    public function stepOne()
    {
        $client = new Github(env('GITHUB_CLIENT_ID'), env('GITHUB_SECRET'));

        //TODO make use of the CSRF token
        $redirect_to = $client->get_authorize_url('DUMMY',env('APP_URL').'/oauth/callback/github');
        Log::info('Redirecting user to OAuth on Github..');
        return redirect($redirect_to);
    }

    public function stepTwo(Request $request)
    {
        $code = $request->input('code');
        $client = new Github(env('GITHUB_CLIENT_ID'), env('GITHUB_SECRET'));

        Log::debug('Exchanging GitHub temporary code ('.$code.') to access token');
        $access_token = $client->fetch_access_token($code);

        Log::debug('Access token fetched.');

        Log::debug('Fetching user data associated with token');
        $user_data = $client->get_user_info();

        if(!isset($user_data->login)) {
            return 'Login error';
        }

        Log::info('Achieved stepTwo OAuth, user is : '.$user_data->login);

        if($this->isRegistered($user_data->login)) {

            DB::table('users')->where('nickname',$user_data->login)->update(['auth_provider' => 'github','auth_token' => $access_token, 'updated_at' => \Carbon\Carbon::now()]);
            $user = DB::table('users')->where('nickname',$user_data->login)->first();
            session(['user_nickname' => $user->nickname, 'user_email' => $user->email, 'user_id' => $user->id]);

            Log::info($user->email.' Logged in !');
            return redirect('/');
        } else {
            session(['user_nickname' => $user_data->login]);
            return view('register', ['auth_token' => $access_token, 'auth_provider' => 'github']);
        }

    }

    private function isRegistered($nick) {

        $user = DB::table('users')->where('nickname',$nick)->first();
        return isset($user);
    }

    public function logout(Request $request) {
        $request->session()->flush();
        return view('home');
    }
}