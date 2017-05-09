<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Routes that require no particular security
|
*/

Route::get('/','Home@displayDiscover');

Route::get('/choose-auth','Home@chooseAuthProvider');
Route::post('/register','Home@register');
Route::get('/logout','OAuthLogin@logout');
Route::get('/login/oauth/{authvendor}','OAuthLogin@stepOne');
Route::get('/oauth/callback/{authvendor}','OAuthLogin@stepTwo');

