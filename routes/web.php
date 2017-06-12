<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Routes that require no particular security
|
 */

Route::get('/', 'Home@displayDiscover');

Route::get('/choose-auth', 'Home@chooseAuthProvider');
Route::get('/about', 'Home@about');

Route::post('/register', 'Home@register');
Route::get('/logout', 'OAuthLogin@logout');
Route::get('/oauth/{provider}', 'OAuthLogin@stepOne');
Route::get('/oauth/callback/{provider}', 'OAuthLogin@stepTwo');

Route::get('/reviews/{reviewid}/view', 'ReviewRequest@displayReview');
Route::get('/members/{userid}/profile', 'Profile@displayPublicProfile');

Route::post('/api/reviews/search','Home@search');