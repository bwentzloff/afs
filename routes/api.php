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
            Route::get('guardedRefresh', 'AuthController@guardedRefresh')->name('api.jwt.refresh');
        });

        

    });

    Route::post('league/create', 'LeagueController@create');
    Route::post('league/updateDraft', 'LeagueController@updateDraft');
    Route::post('league/updateSize', 'LeagueController@updateSize');
    Route::post('league/assignPlayer', ['uses'=>'LeagueController@assignPlayer']);
    Route::post('user/sendEmailLink', 'AuthController@sendPasswordResetLink');
    
    Route::post('league/getUserLeagues', 'LeagueController@getUserLeagues');

    // Join leagues
    Route::post('league/fromInvite/{code}', ['uses'=>'LeagueController@getLeagueInfoFromCode']);
    Route::post('league/join/{code}', ['uses'=>'LeagueController@joinLeagueFromCode']);
    Route::post('league/moveUpDraftOrder', ['uses'=>'LeagueController@moveUpDraftOrder']);
    Route::post('league/moveDownDraftOrder', ['uses'=>'LeagueController@moveDownDraftOrder']);
    Route::post('league/updateSettings', ['uses'=>'LeagueController@updateSettings']);
    Route::post('league/getMatchups', ['uses'=>'LeagueController@getMatchups']);
    Route::post('league/createClaim', ['uses'=>'LeagueController@createClaim']);
    Route::post('league/addFreeAgent', ['uses'=>'LeagueController@addFreeAgent']);
    Route::post('league/updateMatchup', ['uses'=>'LeagueController@updateMatchup']);
    Route::post('league/cancelWaiver', ['uses'=>'LeagueController@cancelWaiver']);
    Route::post('league/createTrade', ['uses'=>'LeagueController@createTrade']);
    Route::post('league/getTrades', ['uses'=>'LeagueController@getTrades']);
    Route::post('league/cancelTrade', ['uses'=>'LeagueController@cancelTrade']);
    Route::post('league/acceptTrade', ['uses'=>'LeagueController@acceptTrade']);

    Route::post('league/updatePlayerEligibility', ['uses'=>'LeagueController@updatePlayerEligibility']);
    Route::post('league/getEligibilities', ['uses'=>'LeagueController@getPlayerEligibilities']);
    
    
    
    Route::post('player/queue', 'LeagueUserController@queuePlayer');
    Route::post('player/draft', 'LeagueUserController@draftPlayer');
    Route::post('player/moveUpQueue', 'LeagueUserController@moveUpQueue');
    Route::post('player/moveDownQueue', 'LeagueUserController@moveDownQueue');
    Route::post('player/removeFromQueue', 'LeagueUserController@removeFromQueue');
    Route::post('player/getqueue', 'LeagueUserController@getQueue');
    Route::post('player/getwaivers', 'LeagueUserController@getWaivers');

    // Scraping routes
    Route::get('scrape/xfl/players', 'ScrapeController@xflPlayers');
    Route::get('league/updateDraftStatus', 'LeagueController@updateDraftStatuses');
    Route::get('league/getLastUpdate/{id}', ['uses'=>'LeagueController@getLastUpdate']);

    // Get league info
    Route::get('league/info/{id}', ['uses'=>'LeagueController@getLeagueInfo']);
    Route::get('league/myuserid', ['uses'=>'LeagueController@getUserId']);
    Route::get('league/teams/{id}', ['uses'=>'LeagueController@getTeamInfo']);
    Route::get('league/draftOrder/{id}', ['uses'=>'LeagueController@getDraftOrder']);
    Route::get('league/myteam/{id}', ['uses'=>'LeagueController@getMyTeam']);
    Route::post('league/getrosters', ['uses'=>'LeagueController@getrosters']);
    Route::post('league/updateRoster', ['uses'=>'LeagueController@updateRoster']);
    Route::post('league/updateRules', ['uses'=>'LeagueController@updateRules']);
    Route::post('league/updateName', ['uses'=>'LeagueController@updateName']);
    Route::post('league/remove', ['uses'=>'LeagueController@removeTeam']);
    Route::post('league/fixMatchups', ['uses'=>'LeagueController@fixMatchups']);
    Route::post('league/getLineup', ['uses'=>'LeagueController@getLineup']);
    Route::post('league/startPlayer', ['uses'=>'LeagueController@startPlayer']);
    Route::post('league/benchPlayer', ['uses'=>'LeagueController@benchPlayer']);

    Route::get('stats', ['uses'=>'LeagueController@stats']);

    // Get player info
    Route::get('players/xfl', 'PlayerController@xflPlayers');
    Route::get('players/stats', 'ScrapeController@calculatePercent');
    Route::get('players/processWaivers/{day}', 'ScrapeController@processWaivers');

    // Cronjobs
    //Route::get('cron/drafts/updateStatuses', 'DraftController@updateStatuses');

    /**
     * Basic Routes
     **/    
    Route::middleware('auth:api')->group(function () {
        Route::resource('user', 'UserController')->only(['index','show']);
    });
//    Route::group(['middleware' => 'jwt'], function () {
//        // Protected routes
//       Route::resource('index', 'IndexController');
//     });
});