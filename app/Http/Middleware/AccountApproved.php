<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;

class AccountApproved {
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next) {
		$session = $request->session();

		$user_id = $session->get('user_id');
		//TODO cache this
		$user          = DB::table('users')->where('id', $user_id)->first();
		$route_uri     = $request->route()->uri();
		$json_response = false;

		if (0 === strpos($route_uri, 'api/') || 0 === strpos($route_uri, 'ajax/')) {
			$json_response = true;
		}

		if (!$user) {

			if ($json_response) {
				return response()->json([
					'success' => 0,
					'message' => 'Unexpected error ocurred',
				]);
			}

			return response(view('home', ['error_message' => 'Unexpected error ocurred']));
		}

		if ($user->is_confirmed) {
			return $next($request);
		}

		if ($json_response) {
			return response()->json([
				'success' => 0,
				'message' => 'Your account needs to be confirmed to do this (Check your inbox !)',
			]);
		}

		return response(view('home', ['error_message' => 'Your account needs to be confirmed to do this (Check your inbox !)']));

	}

}
