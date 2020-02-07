<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\League;
use App\Models\User;
use App\Models\LeagueUser;
use App\Models\Player;
use App\Models\PlayerStat;

class PlayerController extends Controller
{
    public function xflPlayers() {
        $players = Player::where('sport_id',8)->get();

        return response()->json($players);
    }

    public function getWeeklyStats($week) {
        $stats = PlayerStat::where('week',$week)->get();
        return $stats;
    }
}
