<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

/**
 * Base domain redirected to the subreddits list, page 1
 */
Route::get('/', function () {
    return redirect('s/');
});

/**
 * Base URL, will load the subreddits list
 * Optional: a page number as part of the url and the criteria to sort the subreddits
 */
Route::get('s', 'SubredditController@index');

/**
 * Will load the subreddit publications
 * Necessary: the subreddit name
 * Optional: a page number as part of the url and the criteria to sort the subreddits
 */

Route::get('r', 'SubredditController@subredditPosts');

/**
 * Load the 404 page
 */

Route::get('404', function () {
    return view('404');
});
