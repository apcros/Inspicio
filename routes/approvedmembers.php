<?php

//All the routes that require the user to have a confirmed account.

Route::get('/reviews/create', 'ReviewRequestController@createForm');
Route::post('/reviews/create', 'ReviewRequestController@create');

Route::get('/reviews/bulk-import', 'ReviewRequestController@bulkImportForm');
Route::post('/reviews/bulk-import', 'ReviewRequestController@bulkImport');

Route::get('/reviews/{reviewid}/edit', 'ReviewRequestController@editForm');
Route::post('/reviews/{reviewid}/edit', 'ReviewRequestController@edit');

Route::get('/ajax/reviews/pulls/{owner}/{repository}/{account_id}', 'ReviewRequestController@getOpenedPullRequestForRepo');
Route::get('/ajax/reviews/branches/{owner}/{repository}/{account_id}', 'ReviewRequestController@getBranches');

Route::post('/ajax/reviews/{reviewid}/track', 'ReviewRequestApi@track');
Route::post('/ajax/reviews/{reviewid}/untrack', 'ReviewRequestApi@untrack');
Route::post('/ajax/reviews/{reviewid}/approve', 'ReviewRequestApi@approve');
Route::post('/ajax/reviews/{reviewid}/reopen', 'ReviewRequestApi@reopen');
Route::post('/ajax/reviews/{reviewid}/close', 'ReviewRequestApi@close');
Route::get('/ajax/reviews/available-for-import', 'ReviewRequestApi@listAllAvailablePrsForImport');

Route::post('/ajax/account/skills', 'Profile@addSkill');
Route::post('/ajax/account/skills/{id}/delete', 'Profile@deleteSkill');