<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

use \Ramsey\Uuid\Uuid;

class Home extends Controller
{

    public function displayMyTrackedReviews(Request $request) {

    }

    public function displayMyOpenReviews(Request $request) {

    }

    public function displayDiscover(Request $request) {
        return view('home');
    }

    public function displayNewReview(Request $request) {

    }

    public function createNewReview(Request $request) {

    }

    public function chooseAuthProvider() {
        return view('choose-auth-provider');
    }

    public function register(Request $request) {
        if(!$request->session()->has('user_nickname')) {
            return view('home',['error_message' => "Couldn't register your account, please try again"]);
        }
        $email = $request->input('email');
        $name = $request->input('name');
        $auth_token = $request->input('auth_token');
        $auth_provider = $request->input('auth_provider');
        $guid = Uuid::uuid4()->toString();

        try {
            Log::info("Creating a new user : $email / $name");
            DB::table('users')->insert(
                [
                'id'            => $guid,
                'email'         => $email,
                'name'          => $name,
                'auth_token'    => $auth_token,
                'auth_provider' => $auth_provider,
                'nickname'      => $request->session()->get('user_nickname'),
                'rank'          => 1,
                'points'        => 5,
                'created_at'    => \Carbon\Carbon::now(),
                'updated_at'    => \Carbon\Carbon::now(),
                ]
            );
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error("Error caught while adding user : ".$e->getMessage());
            //TODO catch duplicates and display a nice error message
            return view('home', ['error_message' => $e->getMessage()]);
        }

        Session(['user_email' => $email, 'user_id' => $guid]);
        return redirect('/');
    }

}