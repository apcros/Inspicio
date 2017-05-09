<?php

/*
|--------------------------------------------------------------------------
| Members routes
|--------------------------------------------------------------------------
|
| Routes requiring the user to be logged in. (Using middleware logged)
*/


Route::get('/account','Profile@summary');
Route::post('/account/skills','Profile@addSkills');

Route::get('/reviews/mine','Home@displayMyOpenReviews');
Route::get('/reviews/tracked','Home@displayMyTrackedReviews');
Route::get('/reviews/create','Home@displayNewReview');
Route::post('/reviews/create','Home@createNewReview');