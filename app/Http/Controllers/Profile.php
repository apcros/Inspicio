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

		$skills           = $this->getAllSkills($user_id);
		$available_skills = DB::table('skills')->get();

		return view('my-account', [
			'user'             => $user,
			'accounts'         => $accounts,
			'skills'           => $skills,
			'available_skills' => $available_skills,
		]);
	}

	public function displayPublicProfile($userid) {

		$user = DB::table('users')->where('id', $userid)->first();

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
			Log::error("Error caught while adding skill (user $user_id) : " . $e->getMessage());

			return response()->json([
				'success' => 0,
				'message' => 'Failed to add skill',
			]);
		}

		return response()->json([
			'success' => 1,
			'message' => 'Skill added with success',
		]);

	}

	public function deleteSkill(Request $request, $skill_id) {
		$user_id = session('user_id');

		$skill_to_delete = DB::table('user_skills')
			->where('id', $skill_id)
			->first();

		if ($skill_to_delete->user_id != $user_id) {
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
			Log::error("Error caught while trying to delete skill (user $user_id) : " . $e->getMessage());

			return response()->json([
				'success' => 0,
				'message' => 'Failed to delete skill',
			]);
		}

		return response()->json([
			'success' => 1,
			'message' => 'Skill deleted',
		]);

	}

}
