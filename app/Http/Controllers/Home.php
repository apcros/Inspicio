<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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

}