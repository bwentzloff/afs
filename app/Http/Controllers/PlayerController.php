<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\League;
use App\Models\User;
use App\Models\LeagueUser;
use App\Models\Player;
use App\Models\PlayerStat;
use Illuminate\Support\Facades\Cache;

class PlayerController extends Controller
{
    public function xflPlayers() {
        $player_list = Cache::get('player_list');

        if (!$player_list) {
            $player_list = Player::where('sport_id',8)->get();
            Cache::put('player_list', $player_list,600);
        }
        

        return response()->json($player_list);
    }

    public function getWeeklyStats($week) {
        $stats = PlayerStat::where('week',$week)->get();
        return $stats;
    }
}
