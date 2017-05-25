<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Profile extends Controller {
	public function summary(Request $request) {

		$user_id  = session('user_id');
		$user     = DB::table('users')->where('id', $user_id)->first();
		$accounts = DB::table('accounts')->where('user_id', $user_id)->get();

		$skills = $this->getAllSkills();

		return view('my-account', [
			'user'     => $user,
			'accounts' => $accounts,
			'skills'   => $skills,
		]);
	}

	public function displayPublicProfile($userid) {

		$user = DB::table('users')->where('id', $userid)->first();

		if (!$user) {
			return view('home', ['error_message' => 'User not found']);
		}

		$skills  = $this->getAllSkills();
		$reviews = DB::table('requests')->where([
			['author_id', '=', $userid],
			['status', '=', 'open'],
		])->get();

		return view('profile', [
			'user'    => $user,
			'skills'  => $skills,
			'reviews' => $reviews]);
	}

	private function getAllSkills() {
		return DB::table('user_skills')
			->join('skills', 'user_skills.skill_id', '=', 'skills.id')
			->select('user_skills.*', 'skills.name')
			->where('user_skills.user_id', session('user_id'))->get();
	}

}
