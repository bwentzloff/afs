<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\League;
use App\Models\User;
use App\Models\LeagueUser;
use App\Models\Player;

include(app_path() . '/../vendor/simple-html-dom/simple_html_dom.php');

class DraftController extends Controller
{
    public function updateStatuses() {
        $undrafted_leagues = League::where('draft_status',0)->get();
        foreach($undrafted_leagues as $league) {
            if (isset($league->draft_datetime)) {
                $current_time = time();
                $league_drafttime = strtotime($league->draft_datetime);
                if ($current_time >= $league_drafttime-3600) {
                    $league->draft_status = 1;
                    $league->save();
                }
            }
        }
    }
}
