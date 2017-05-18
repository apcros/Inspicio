<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Profile extends Controller {
	public function summary(Request $request) {

		$user_id = session('user_id');
		$user = DB::table('users')->where('id', $user_id)->first();
		$accounts = DB::table('accounts')->where('user_id', $user_id)->get();
		$skills = DB::table('skills')->where('user_id', $user_id)->get();

		return view('my-account', [
			'user'     => $user,
			'accounts' => $accounts,
			'skills'   => $skills,
		]);
	}
}