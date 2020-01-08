<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\League;
use App\Models\User;
use App\Models\LeagueUser;
use Carbon\Carbon;

use Illuminate\Support\Facades\Log;

class LeagueController extends Controller
{
    public function create(Request $request)
    {

        $league = new League;
        $league->name = $request->input('name');
        $league->maxSize = $request->input('maxSize');
        $league->commish_id = Auth::user()->id;
        $league->invite_code = uniqid();
        $league->draft_datetime = Carbon::parse($request->input('draft_datetime'));

        $league->qbs = $request->input('qbs');
        $league->rbs = $request->input('rbs');
        $league->wrs = $request->input('wrs');
        $league->tes = $request->input('tes');
        $league->flex = $request->input('flex');
        $league->ks = $request->input('ks');
        $league->def = $request->input('def');

        $league->rule1 = $request->input('rule1');
        $league->rule2 = $request->input('rule2');
        $league->rule3 = $request->input('rule3');
        $league->rule4 = $request->input('rule4');
        $league->rule5 = $request->input('rule5');
        $league->rule6 = $request->input('rule6');
        $league->rule7 = $request->input('rule7');
        $league->rule8 = $request->input('rule8');
        $league->rule9 = $request->input('rule9');
        $league->rule10 = $request->input('rule10');
        $league->rule11 = $request->input('rule11');
        $league->rule12 = $request->input('rule12');
        $league->rule13 = $request->input('rule13');
        $league->rule14 = $request->input('rule14');
        $league->rule15 = $request->input('rule15');
        $league->rule16 = $request->input('rule16');
        $league->rule17 = $request->input('rule17');
        $league->rule18 = $request->input('rule18');
        $league->rule19 = $request->input('rule19');
        $league->rule20 = $request->input('rule20');
        $league->rule21 = $request->input('rule21');
        $league->rule22 = $request->input('rule22');
        $league->rule23 = $request->input('rule23');
        $league->rule24 = $request->input('rule24');
        $league->rule25 = $request->input('rule25');

        $league->save();

        // Add commish to league
        $leagueUser = new LeagueUser;
        $leagueUser->user_id = Auth::user()->id;
        $leagueUser->league_id = $league->id;
        
        $leagueUser->save();
    }

    public function getUserLeagues(Request $request) {
        $leagues = array();
        $league_users = LeagueUser::where('user_id',Auth::user()->id)->get();

        foreach($league_users as $lu) {
            $leagues[] = League::where('id',$lu->league_id)->first();
        }

        return response()->json($leagues);
    }

    public function getLeagueInfoFromCode($code) {
        $league = League::where('invite_code',$code)->first();

        return response()->json($league);
    }

    public function joinLeagueFromCode($code) {
        $league = League::where('invite_code',$code)->first();

        $leagueUser = new LeagueUser;
        $leagueUser->user_id = Auth::user()->id;
        $leagueUser->league_id = $league->id;
        
        $leagueUser->save();
    }

    public function getLeagueInfo($id) {
        $league = League::where('id',$id)->first();
        return response()->json($league);
    }
}
