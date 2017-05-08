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

    public function step_one()
    {
        $client = new Github(env('GITHUB_CLIENT_ID'), env('GITHUB_SECRET'));
        //TODO use env for redirect_uri
        //TODO make use of the CSRF token
        $redirect_to = $client->get_authorize_url('DUMMY','http://127.0.0.1/inspicio/public/oauth/callback/github');
        Log::info('Redirecting user to OAuth on Github..');
        return redirect($redirect_to);
    }

    public function step_two(Request $request)
    {
        $code = $request->input('code');
        $client = new Github(env('GITHUB_CLIENT_ID'), env('GITHUB_SECRET'));

        Log::info('Exchanging GitHub temporary code ('.$code.') to access token');
        $access_token = $client->fetch_access_token($code);

        Log::info('Access token fetched.');

        Log::info('Fetching user data associated with token');
        $user_data = $client->get_user_info();

        if(!isset($user_data->login)) {
            return 'Login error';
        }

        Log::info('Data fetched, user is : '.$user_data->login);

        if($this->is_registered($user_data->login)) {
            DB::table('users')->where('nickname',$user_data->login)->update(['auth_provider' => 'github','auth_token' => $access_token]);
            $user = DB::table('users')->where('nickname',$user_data->login)->first();
            session(['user_nickname' => $user->nickname, 'user_email' => $user->email, 'user_id' => $user->id]);
            Log::info($user->email.' Logged in !');
            return 'Logged in !';
        } else {
            // TODO - We need to ask the user to register himself. (Especially for email)
        }

        return 'OK';
    }

    private function is_registered($nick) {

        $user = DB::table('users')->where('nickname',$nick)->first();
        return isset($user);
    }

}