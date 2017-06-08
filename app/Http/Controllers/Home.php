<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use \Ramsey\Uuid\Uuid;

class Home extends Controller {
	public function chooseAuthProvider() {
		return view('choose-auth-provider');
	}

	public function displayDiscover(Request $request) {
		$hot_reviews = DB::table('requests')
			->join('users', 'requests.author_id', '=', 'users.id')
			->join('skills', 'requests.skill_id', '=', 'skills.id')
			->select(
				'requests.*',
				DB::raw('(SELECT count(request_tracking.request_id ) FROM request_tracking WHERE request_tracking.request_id = requests.id) as followers'),
				'users.nickname as author',
				'skills.name as language'
			)
			->orderBy('followers', 'desc')
			->groupBy('skills.name', 'users.nickname', 'requests.id')
			->limit(20)
			->get();

		$latest_reviews = DB::table('requests')
			->join('users', 'requests.author_id', '=', 'users.id')
			->join('skills', 'requests.skill_id', '=', 'skills.id')
			->select(
				'requests.*',
				DB::raw('(SELECT count(request_tracking.request_id ) FROM request_tracking WHERE request_tracking.request_id = requests.id) as followers'),
				'users.nickname as author',
				'skills.name as language'
			)
			->orderBy('requests.created_at', 'desc')
			->groupBy('skills.name', 'users.nickname', 'requests.id')
			->limit(20)
			->get();

		$languages = DB::table('skills')->get();

		return view('home', [
			'hot_reviews'    => $hot_reviews,
			'latest_reviews' => $latest_reviews,
			'languages'      => $languages,
		]);
	}

	public function search(Request $request) {
		$search_str = $request->input('filters.query');
		$languages  = $request->input('filters.languages');

		$reviews = DB::table('requests')
			->join('users', 'requests.author_id', '=', 'users.id')
			->join('skills', 'requests.skill_id', '=', 'skills.id')
			->select(
				'requests.id',
				'requests.name',
				'requests.repository',
				'users.nickname as author',
				'skills.name as language'
			)
			->orderBy('requests.created_at', 'desc')
			->groupBy('skills.name', 'users.nickname', 'requests.id')
			->when(count($languages) > 0, function ($query) use ($languages) {
				return $query->whereIn('skills.id', $languages);
			})
			->when($search_str != '', function ($query) use ($search_str) {
				return $query->where('requests.name', 'like', '%' . $search_str . '%')
					->orWhere('requests.description', 'like', '%' . $search_str . '%'); //TODO : Consider performance impact of that description search
			})
			->limit(20)
			->get();

		return response()->json([
			'success' => 1,
			'reviews' => $reviews]);
	}

	public function register(Request $request) {

		if (!$request->session()->has('user_nickname')) {
			Log::error('No user_nickname set, could not create account');

			return view('home', ['error_message' => "Couldn't register your account, please try again"]);
		}

		$email         = $request->input('email');
		$name          = $request->input('name');
		$auth_token    = $request->input('auth_token');
		$auth_provider = $request->input('auth_provider');
		$user_id       = Uuid::uuid4()->toString();
		$account_id    = Uuid::uuid4()->toString();
		try {
			Log::info("Creating a new user : $email / $name / $user_id");
			DB::table('users')->insert(
				[
					'id'         => $user_id,
					'email'      => $email,
					'name'       => $name,
					'nickname'   => $request->session()->get('user_nickname'),
					'rank'       => 1,
					'points'     => 5,
					'created_at' => \Carbon\Carbon::now(),
					'updated_at' => \Carbon\Carbon::now(),
				]
			);
			DB::table('accounts')->insert(
				[
					'id'         => $account_id,
					'provider'   => $auth_provider,
					'login'      => $request->session()->get('user_nickname'),
					'token'      => $auth_token,
					'user_id'    => $user_id,
					'is_main'    => true,
					'created_at' => \Carbon\Carbon::now(),
					'updated_at' => \Carbon\Carbon::now(),
				]
			);
		} catch (\Illuminate\Database\QueryException $e) {
			Log::error("Error caught while adding user : " . $e->getMessage());
			//TODO catch duplicates and display a nice error message

			return view('home', ['error_message' => 'Failed to register']);
		}

		Session(['user_email' => $email, 'user_id' => $user_id]);

		return redirect('/');
	}

}
