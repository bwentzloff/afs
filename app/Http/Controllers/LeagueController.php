<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Auth;
use App\Models\League;
use App\Models\User;
use App\Models\Player;
use App\Models\LeagueUser;
use App\Models\DraftPick;
use App\Models\RosterItem;
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
        $league->draftpick_time = $request->input('draftpickTime');
        $league->league_type = $request->input('league_type');
        $league->playoff_length = $request->input('playoff_length');
        $league->waiver_day = $request->input('waiver_day');

        $league->qbs = $request->input('qbs');
        $league->rbs = $request->input('rbs');
        $league->wrs = $request->input('wrs');
        $league->tes = $request->input('tes');
        $league->flex = $request->input('flex');
        $league->ks = $request->input('ks');
        $league->def = $request->input('def');
        $league->bench = $request->input('bench');

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
        $leagueUser->name = $request->input('teamname');
        
        $leagueUser->save();
        $this->setDraftOrder($league->id);
    }

    public function getUserLeagues(Request $request) {
        $leagues = array();
        $league_users = LeagueUser::where('user_id',Auth::user()->id)->get();

        foreach($league_users as $lu) {
            $leagues[] = League::where('id',$lu->league_id)->first();
        }

        return response()->json($leagues);
    }

    public function getrosters(Request $request) {
        $teamInfo = LeagueUser::where('league_id',$request->input('leagueId'))->get();
        $teams = array();
        foreach ($teamInfo as $team) {
            $rosterItems = RosterItem::where('team_id',$team->id)->get();
            $teams[$team->id] = $rosterItems;
        }
        return response()->json($teams);
    }

    public function getLeagueInfoFromCode($code) {
        $league = League::where('invite_code',$code)->first();

        return response()->json($league);
    }

    public function joinLeagueFromCode($code, Request $request) {
        $league = League::where('invite_code',$code)->first();

        $count = LeagueUser::where('league_id', $league->id)->count();
        $alreadyJoined = LeagueUser::where('league_id', $league->id)->where('user_id',Auth::user()->id)->first();
        if ($count < $league->maxSize && !$alreadyJoined) {

            $teams = LeagueUser::where('league_id',$league->id);
            $highestDraft = 0;
            foreach ($teams as $team) {
                if ($team->draft_order > $highestDraft) $highestDraft = $team->draft_order;
            }
            $leagueUser = new LeagueUser;
            $leagueUser->user_id = Auth::user()->id;
            $leagueUser->league_id = $league->id;
            $leagueUser->draft_order = $count + 1;
            $leagueUser->name = $request->input('teamname');
            
            $leagueUser->save();

            $this->setDraftOrder($league->id);
        }
    }

    public function getLeagueInfo($id) {
        $league = League::where('id',$id)->first();
        $teams = LeagueUser::where('league_id',$league->id)->get();
        $league->teams = $teams;
        return response()->json($league);
    }

    public function getTeamInfo($id) {
        $leagueUsers = LeagueUser::where('league_id',$id)->get();
        return response()->json($leagueUsers);
    }

    public function getDraftOrder($id) {
        $draftOrder = DraftPick::where('league_id',$id)->orderBy('pick_order')->get();

        return response()->json($draftOrder);
    }

    public function getLastUpdate($id) {
        $lastUpdate = Cache::get('leagueUpdate'.$id);

        if (!$lastUpdate) {
            $lastUpdate = uniqid();
            Cache::put('leagueUpdate'.$id, $lastUpdate,600);
        }
        print_r($lastUpdate);die();
    }

    public function getMyTeam($id) {
        $team = LeagueUser::where('league_id',$id)->where('user_id',Auth::user()->id)->first();

        return response()->json($team);
    }

    function setDraftOrder($leagueId) {
        $league_users = LeagueUser::where('league_id',$leagueId)->orderBy('draft_order')->get();

        $highest_draft_order = 0;
        foreach($league_users as $team) {
            if ($team->draft_order == 0) {
                $new_draft_order = $highest_draft_order + 1;
                $highest_draft_order = $new_draft_order;
                LeagueUser::where('id',$team->id)->update([
                    'draft_order'=>$new_draft_order
                ]);
            } else {
                $highest_draft_order = $team->draft_order;
            }
        }

        $this->createDraftPicks($leagueId);
    }

    function createDraftPicks($leagueId) {
        $league = League::where('id',$leagueId)->first();

        $oldPicks = DraftPick::where('league_id',$leagueId)->delete();

        $numPicks = $league->qbs + $league->rbs + $league->wrs + $league->tes + $league->flex + $league->ks + $league->def;

        $order = "asc";
        $pick = 1;
        for ( $i = 0; $i < $numPicks; $i++) {
            $league_users = LeagueUser::where('league_id',$leagueId)->orderBy('draft_order',$order)->get();

            foreach ($league_users as $team) {
                $newDraftPick = new DraftPick;
                $newDraftPick->team_id = $team->id;
                $newDraftPick->league_id = $leagueId;
                $newDraftPick->pick_order = $pick;
                $newDraftPick->save();

                $pick++;
            }
            if ($order == "asc") {
                $order = "desc";
            } else {
                $order = "asc";
            }
        }
        $pick = 0;
    }

    function updateDraftStatuses() {
        
            // Start drafts
        for ($i = 0; $i < 5; $i++) {    
            $leagues = League::where('draft_status',0)
                ->where('draft_datetime','<=',Carbon::now())
                ->get();
            
            foreach($leagues as $league) {
                

                // update draft_current_drafter
                $draftPicks = DraftPick::where('league_id',$league->id)
                    ->whereNull('player_id')
                    ->orderBy('pick_order','asc')
                    ->first();

                $update = League::where('id',$league->id)->update([ 
                    'draft_status'=>1,
                    'draft_nextpick'=>Carbon::now()->addMinutes($league->draftpick_time),
                    'draft_current_drafter'=>$draftPicks->team_id
                ]);

                $lastUpdate = uniqid();
                Cache::put('leagueUpdate'.$league->id, $lastUpdate,600);
            }


            // Autopicks

            $autopick_leagues = League::where('draft_status',1)
                ->where('draft_nextpick','<=',Carbon::now())
                ->get();

            foreach($autopick_leagues as $league) {
                // get all drafted player_ids
                $draftPicks = DraftPick::where('league_id',$league->id)
                    ->whereNotNull('player_id')
                    ->get();

                $player_ids = array();
                foreach($draftPicks as $pick) {
                    $player_ids[] = $pick->player_id;
                }
                // pick a player not in that list
                $player = Player::whereNotIn('id',$player_ids)
                    ->whereIn('position',array("QB","WR","RB","TE","K","DEF"))
                    ->first();

                $draftPick = DraftPick::where('league_id',$league->id)
                    ->whereNull('player_id')
                    ->orderBy('pick_order','asc')
                    ->first();

                $update = DraftPick::where('id',$draftPick->id)
                    ->update([
                        'player_id'=>$player->id
                    ]);
                    $rosteritem = new RosterItem;
                    $rosteritem->team_id = $draftPick->team_id;
                    $rosteritem->league_id = $league->id;
                    $rosteritem->player_id = $player->id;
                    $rosteritem->save();

                // update nextpick and current_drafter
                $nextPick = DraftPick::where('league_id',$league->id)
                    ->whereNull('player_id')
                    ->orderBy('pick_order','asc')
                    ->first();
                if ($nextPick) {
                    $update = League::where('id',$league->id)->update([ 
                        'draft_nextpick'=>Carbon::now()->addMinutes($league->draftpick_time),
                        'draft_current_drafter'=>$nextPick->team_id
                    ]);
                } else {
                    $update = League::where('id',$league->id)->update([
                        'draft_status'=>2
                    ]);
                }

                $lastUpdate = uniqid();
                Cache::put('leagueUpdate'.$league->id, $lastUpdate,600);

                    // update lastUpdate
            }
            sleep(10);
        }
    }

    function stats() {
        $leagues = League::count();
        $users = User::count();
        $teams = LeagueUser::count();

        print_r("Leagues: ".$leagues."<br />Users: ".$users."<br />Teams: ".$teams);die();
    }
}
