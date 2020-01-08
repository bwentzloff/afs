<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        // Below mention routes are public, user can access those without any restriction.
        // Create New User
        Route::post('register', 'AuthController@register');
        // Login User
        Route::post('login', 'AuthController@login');
        
        // Refresh the JWT Token
        Route::get('refresh', 'AuthController@refresh');
        
        // Below mention routes are available only for the authenticated users.
        Route::middleware('auth:api')->group(function () {
            // Get user info
            Route::get('user', 'AuthController@user');
            // Logout user from application
            Route::post('logout', 'AuthController@logout');
        });

        

    });

    Route::post('league/create', 'LeagueController@create');
    Route::post('league/getUserLeagues', 'LeagueController@getUserLeagues');

    // Join leagues
    Route::post('league/fromInvite/{code}', ['uses'=>'LeagueController@getLeagueInfoFromCode']);
    Route::post('league/join/{code}', ['uses'=>'LeagueController@joinLeagueFromCode']);

    // Scraping routes
    Route::get('scrape/xfl/players', 'ScrapeController@xflPlayers');

    // Get league info
    Route::get('league/info/{id}', ['uses'=>'LeagueController@getLeagueInfo']);

    // Get player info
    Route::get('players/xfl', 'PlayerController@xflPlayers');

    // Cronjobs
    Route::get('cron/drafts/updateStatuses', 'DraftController@updateStatuses');

    /**
     * Basic Routes
     **/    
    Route::middleware('auth:api')->group(function () {
        Route::resource('user', 'UserController')->only(['index','show']);
    });
});