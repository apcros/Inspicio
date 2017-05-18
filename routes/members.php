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

Route::get('/reviews/ajax/pulls/{owner}/{repository}/{account_id}', 'ReviewRequest@getOpenedPullRequestForRepo');
Route::post('/reviews/{reviewid}/track', 'ReviewRequest@track');
Route::post('/reviews/{reviewid}/approve', 'ReviewRequest@approve');