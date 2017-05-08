<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/','Home@displayDiscover');

Route::get('/choose-auth','Home@chooseAuthProvider');

Route::get('/logout','OAuthLogin@logout');
Route::get('/login/oauth/{authvendor}','OAuthLogin@step_one');
Route::get('/oauth/callback/{authvendor}','OAuthLogin@step_two');