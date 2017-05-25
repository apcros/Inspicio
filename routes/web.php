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
Route::post('/register', 'Home@register');
Route::get('/logout', 'OAuthLogin@logout');
Route::get('/oauth/{provider}/{type}', 'OAuthLogin@stepOne');
Route::get('/oauth/callback/{provider}/login', 'OAuthLogin@stepTwo');

Route::get('/reviews/{reviewid}/view', 'ReviewRequest@displayReview');
Route::get('/members/{userid}/profile', 'Profile@displayPublicProfile');