<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Auth;
use App\Models\League;
use App\Models\User;
use App\Models\LeagueUser;
use App\Models\Player;
use App\Models\DraftQueue;
use App\Models\DraftPick;
use App\Models\RosterItem;
use Carbon\Carbon;

class LeagueUserController extends Controller
{
    public function queuePlayer(Request $request) {
        $leagueuser = LeagueUser::where('league_id',$request->leagueId)->where('user_id',Auth::user()->id)->first();
        $draftqueue = new DraftQueue;
        $draftqueue->leagueuser_id = $leagueuser->id;
        $draftqueue->player_id = $request->player_id;
        $draftqueue->save();
    }

    public function draftPlayer(Request $request) {
        $leagueuser = LeagueUser::where('league_id',$request->leagueId)->where('user_id',Auth::user()->id)->first();
        $league = League::where('id',$request->leagueId)->first();
        $rosteritem = new RosterItem;
        $rosteritem->team_id = $leagueuser->id;
        $rosteritem->league_id = $leagueuser->league_id;
        $rosteritem->player_id = $request->player_id;
        $rosteritem->save();

        if ($league->draft_current_drafter == $leagueuser->id) {

            // get leagueUsers
            // delete drafted player for draft queues

            $leagueusers = LeagueUser::where('league_id',$request->leagueId)->get();
            foreach($leagueusers as $team) {
                $delete = DraftQueue::where('leagueuser_id',$team->id)
                    ->where('player_id',$request->player_id)
                    ->delete();
            }

            // set draft pick
            $draftPick = DraftPick::where('league_id',$leagueuser->league_id)
                ->whereNull('player_id')
                ->where('team_id',$leagueuser->id)
                ->orderBy('pick_order','asc')
                ->first();
            
            $update = DraftPick::where('id',$draftPick->id)
                ->update([
                    'player_id'=>$request->player_id
                ]);

            // then all this

            // update draft_current_drafter
            $draftPicks = DraftPick::where('league_id',$leagueuser->league_id)
                ->whereNull('player_id')
                ->orderBy('pick_order','asc')
                ->first();

            if ($draftPicks) {
                $update = League::where('id',$leagueuser->league_id)->update([ 
                    'draft_nextpick'=>Carbon::now()->addMinutes($league->draftpick_time),
                    'draft_current_drafter'=>$draftPicks->team_id
                ]);
            } else {
                $update = League::where('id',$leagueuser->league_id)->update([
                    'draft_status'=>2
                ]);
            }
            

            $lastUpdate = uniqid();
            Cache::put('leagueUpdate'.$leagueuser->league_id, $lastUpdate,600);
        }
    }

    public function getQueue(Request $request) {
        $leagueuser = LeagueUser::where('league_id',$request->leagueId)->where('user_id',Auth::user()->id)->first();

        if ($leagueuser) {
            $draftqueue = DraftQueue::where('leagueuser_id',$leagueuser->id)
                ->orderBy('id')
                ->get();
        } else {
            $draftqueue = "";
        }
        return response()->json($draftqueue);
    }

    public function moveUpQueue(Request $request) {
        $leagueuser = LeagueUser::where('league_id',$request->leagueId)->where('user_id',Auth::user()->id)->first();
        $draftqueue = DraftQueue::where('leagueuser_id',$leagueuser->id)
            ->orderBy('id')
            ->get();

        $targetPlayerId = '';
        $previousPlayerId = '';
        $previousPick = 0;
        $highestId = 0;
        
        foreach ($draftqueue as $pick) {
            if ($pick->player_id == $request->player_id) {
                $targetPlayerId = $pick->id;
                $previousPlayerId = $previousPick;
            }
            $previousPick = $pick->id;
            if ($pick->id > $highestId) $highestId = $pick->id;
        }
        $updateone = DraftQueue::where('id',$previousPlayerId)
            ->update([
                'id' => $highestId + 1
            ]);
        $updatetwo = DraftQueue::where('id',$targetPlayerId)
                ->update([
                    'id' => $previousPlayerId
                ]);
        $updatethree = DraftQueue::where('id',$previousPlayerId)
                ->update([
                    'id' => $targetPlayerId
                ]);
    }

    public function moveDownQueue(Request $request) {
        $leagueuser = LeagueUser::where('league_id',$request->leagueId)->where('user_id',Auth::user()->id)->first();
        $draftqueue = DraftQueue::where('leagueuser_id',$leagueuser->id)
            ->orderBy('id')
            ->get();

        $targetPlayerId = '';
        $previousPlayerId = '';
        $previousPick = 0;
        $highestId = 0;

        $grabNext = false;
        
        foreach ($draftqueue as $pick) {
            if ($pick->player_id == $request->player_id) {
                $targetPlayerId = $pick->id;
                //$previousPlayerId = $previousPick;
                $grabNext = true;
            }
            if ($grabNext) $previousPlayerId = $pick->player_id;
            $previousPick = $pick->id;
            if ($pick->id > $highestId) $highestId = $pick->id;
        }
        $updateone = DraftQueue::where('id',$previousPlayerId)
            ->update([
                'id' => $highestId + 1
            ]);
        $updatetwo = DraftQueue::where('id',$targetPlayerId)
                ->update([
                    'id' => $previousPlayerId
                ]);
        $updatethree = DraftQueue::where('id',$previousPlayerId)
                ->update([
                    'id' => $targetPlayerId
                ]);
    }

    public function removeFromQueue(Request $request) {
        $leagueuser = LeagueUser::where('league_id',$request->leagueId)->where('user_id',Auth::user()->id)->first();
        $draftqueue = DraftQueue::where('leagueuser_id',$leagueuser->id)
            ->where('player_id',$request->player_id)
            ->delete();
    }
}
