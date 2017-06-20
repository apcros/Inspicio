<?php

/*
|--------------------------------------------------------------------------
| Members routes
|--------------------------------------------------------------------------
|
| Routes requiring the user to be logged in. (Using middleware logged)
 */

Route::get('/account', 'Profile@summary');

Route::get('/reviews/mine', 'ReviewRequest@viewAllMine');
Route::get('/reviews/tracked', 'ReviewRequest@viewAllTracked');
Route::get('/reviews/create', 'ReviewRequest@createForm');
Route::post('/reviews/create', 'ReviewRequest@create');

Route::get('/ajax/reviews/pulls/{owner}/{repository}/{account_id}', 'ReviewRequest@getOpenedPullRequestForRepo');
Route::get('/ajax/reviews/branches/{owner}/{repository}/{account_id}', 'ReviewRequest@getBranches');

Route::post('/ajax/reviews/{reviewid}/track', 'ReviewRequestApi@track');
Route::post('/ajax/reviews/{reviewid}/untrack', 'ReviewRequestApi@untrack');
Route::post('/ajax/reviews/{reviewid}/approve', 'ReviewRequestApi@approve');
Route::post('/ajax/reviews/{reviewid}/reopen', 'ReviewRequestApi@reopen');
Route::post('/ajax/reviews/{reviewid}/close', 'ReviewRequestApi@close');

Route::post('/ajax/account/skills', 'Profile@addSkill');
Route::post('/ajax/account/skills/{id}/delete', 'Profile@deleteSkill');