<?php

/*
|--------------------------------------------------------------------------
| Members routes
|--------------------------------------------------------------------------
|
| Routes requiring the user to be logged in. (Using middleware logged)
 */

Route::get('/account', 'Profile@summary');
Route::post('/account/skills', 'Profile@addSkills');

Route::get('/reviews/mine', 'ReviewRequest@viewAllMine');
Route::get('/reviews/tracked', 'ReviewRequest@viewAllTracked');
Route::get('/reviews/create', 'ReviewRequest@createForm');
Route::post('/reviews/create', 'ReviewRequest@create');

Route::get('/oauth/callback/{provider}/add', 'OAuthLogin@stepTwoNewAccount');

Route::get('/ajax/reviews/pulls/{owner}/{repository}/{account_id}', 'ReviewRequest@getOpenedPullRequestForRepo');
Route::get('/ajax/reviews/branches/{owner}/{repository}/{account_id}', 'ReviewRequest@getBranches');
Route::post('/ajax/reviews/{reviewid}/track', 'ReviewRequest@track');
Route::post('/ajax/reviews/{reviewid}/approve', 'ReviewRequest@approve');
Route::post('/ajax/reviews/{reviewid}/close', 'ReviewRequest@close');