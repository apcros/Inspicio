<?php

//All the routes that require the user to have a confirmed account.

Route::get('/reviews/create', 'ReviewRequest@createForm');
Route::post('/reviews/create', 'ReviewRequest@create');

Route::get('/reviews/{reviewid}/edit', 'ReviewRequest@editForm');
Route::post('/reviews/{reviewid}/edit', 'ReviewRequest@edit');

Route::get('/ajax/reviews/pulls/{owner}/{repository}/{account_id}', 'ReviewRequest@getOpenedPullRequestForRepo');
Route::get('/ajax/reviews/branches/{owner}/{repository}/{account_id}', 'ReviewRequest@getBranches');

Route::post('/ajax/reviews/{reviewid}/track', 'ReviewRequestApi@track');
Route::post('/ajax/reviews/{reviewid}/untrack', 'ReviewRequestApi@untrack');
Route::post('/ajax/reviews/{reviewid}/approve', 'ReviewRequestApi@approve');
Route::post('/ajax/reviews/{reviewid}/reopen', 'ReviewRequestApi@reopen');
Route::post('/ajax/reviews/{reviewid}/close', 'ReviewRequestApi@close');

Route::post('/ajax/account/skills', 'Profile@addSkill');
Route::post('/ajax/account/skills/{id}/delete', 'Profile@deleteSkill');