<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Routes that require no particular security
|
 */

Route::get('/', 'Home@displayLatest');
Route::get('/trending', 'Home@displayTrending');
Route::get('/reviews/search', 'Home@displaySearch');
Route::get('/tos', 'Home@termsAndConditions');
Route::get('/choose-auth', 'Home@chooseAuthProvider');
Route::get('/about', 'Home@about');

Route::post('/register', 'Home@register');
Route::get('/logout', 'OAuthLogin@logout');
Route::get('/oauth/{provider}', 'OAuthLogin@stepOne');
Route::get('/oauth/callback/{provider}', 'OAuthLogin@stepTwo');

Route::get('/confirm/{user_id}/{confirm_token}', 'Home@confirm');

Route::get('/reviews/{reviewid}/view', 'ReviewRequestController@displayReview');
Route::get('/members/{userid}/profile', 'Profile@displayPublicProfile');

Route::post('/api/reviews/search', 'Home@search');