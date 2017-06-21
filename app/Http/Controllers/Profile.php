<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Profile extends Controller {
	public function summary() {
		$user_id  = session('user_id');
		$user     = DB::table('users')->where('id', $user_id)->first();
		$accounts = DB::table('accounts')->where('user_id', $user_id)->get();

		$skills           = $this->getAllSkills($user_id);
		$available_skills = DB::table('skills')->get();

		return view('my-account', [
			'user'             => $user,
			'accounts'         => $accounts,
			'skills'           => $skills,
			'available_skills' => $available_skills,
		]);
	}

	public function updateProfile(Request $request) {
		$new_email = $request->input('email');
        //TODO email update needs to force a reconfirmation

		$new_name  = $request->input('name');
		$user_id   = session('user_id');

		//TODO not updating both if one is empty
		try {
			$user = DB::table('users')->where('id', $user_id)->update([
				'updated_at' => \Carbon\Carbon::now(),
				//'email'      => $new_email,
				'name'       => $new_name,
			]);
			Log::info("[USER $user_id ] Updated profile. $new_email ($new_name)");

		} catch (\Illuminate\Database\QueryException $e) {
			Log::error("[USER $user_id ] SQL Error while updating profile with $new_email ($new_name) : " . $e->getMessage());

			return view('home', ['error_message' => 'Failed to update your profile']);
		}

		return redirect('/account');

	}

	public function displayPublicProfile($userid) {

		try {
			$user = DB::table('users')->where('id', $userid)->first();
		} catch (\Illuminate\Database\QueryException $e) {
			//Wrong uuid representation. Todo verify userid instead
			$user = false;
		}

		if (!$user) {
			return view('home', ['error_message' => 'User not found']);
		}

		$skills  = $this->getAllSkills($user->id);
		$reviews = DB::table('requests')->where([
			['author_id', '=', $userid],
			['status', '=', 'open'],
		])->get();

		return view('profile', [
			'user'    => $user,
			'skills'  => $skills,
			'reviews' => $reviews]);
	}

	private function getAllSkills($id) {
		return DB::table('user_skills')
			->join('skills', 'user_skills.skill_id', '=', 'skills.id')
			->select('user_skills.*', 'skills.name')
			->where('user_skills.user_id', $id)->get();
	}

	public function addSkill(Request $request) {
		$skill_id = $request->input('skill');
		$level    = $request->input('level');

		$user_id = session('user_id');

		$allowed_level = [1, 2, 3];

//Have I mentionned to never trust the client ?
		if (!in_array($level, $allowed_level)) {
			Log::warning("[USER $user_id] Out of range skill level : $level");

			return response()->json([
				'success' => 0,
				'message' => 'Skill level error',
			]);
		}

		$duplicate_skill = DB::table('user_skills')->where([
			['user_id', '=', $user_id],
			['skill_id', '=', $skill_id],
		])->first();

		if ($duplicate_skill) {
			return response()->json([
				'success' => 0,
				'message' => 'You already have that skill',
			]);
		}

		try {
			DB::table('user_skills')->insert(
				[
					'user_id'     => $user_id,
					'is_verified' => false,
					'level'       => $level,
					'skill_id'    => $skill_id,
				]
			);

		} catch (\Illuminate\Database\QueryException $e) {
			Log::error("[USER $user_id ] - Error caught while adding skill : " . $e->getMessage());

			return response()->json([
				'success' => 0,
				'message' => 'Failed to add skill',
			]);
		}

		Log::info("[USER $user_id] Added Skill $skill_id / level $level");

		return response()->json([
			'success' => 1,
			'message' => 'Skill added with success',
		]);

	}

	public function deleteSkill($skill_id) {
		$user_id = session('user_id');

		$skill_to_delete = DB::table('user_skills')
			->where('id', $skill_id)
			->first();

		if ($skill_to_delete->user_id != $user_id) {
			Log::warning("[USER $user_id] User tried to delete someone else skill ($skill_id)");

			return response()->json([
				'success' => 0,
				'message' => "You can only delete your own skills, Don't be that guy.",
			]);
		}

		try {
			DB::table('user_skills')
				->where('id', $skill_id)
				->delete();
		} catch (\Illuminate\Database\QueryException $e) {
			Log::error("[USER $user_id] - Error caught while trying to delete skill $skill_id: " . $e->getMessage());

			return response()->json([
				'success' => 0,
				'message' => 'Failed to delete skill',
			]);
		}

		Log::info("[USER $user_id] Deleted skill $skill_id");

		return response()->json([
			'success' => 1,
			'message' => 'Skill deleted',
		]);

	}

}
