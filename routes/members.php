<?php

/*
|--------------------------------------------------------------------------
| Members routes
|--------------------------------------------------------------------------
|
| Routes requiring the user to be logged in. (Using middleware logged)
 */

Route::get('/account', 'Profile@summary');
Route::post('/account', 'Profile@updateProfile');

Route::get('/reviews/mine', 'ReviewRequestController@viewAllMine');
Route::get('/reviews/tracked', 'ReviewRequestController@viewAllTracked');