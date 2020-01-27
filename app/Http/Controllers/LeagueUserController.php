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
        $highestqueue = $this->getHighestQueue($leagueuser->id);

        $draftqueue = new DraftQueue;
        $draftqueue->leagueuser_id = $leagueuser->id;
        $draftqueue->player_id = $request->player_id;
        $draftqueue->queue_order = $highestqueue + 1;
        $draftqueue->save();

        $this->reorderDraftQueue($leagueuser->id);
    }

    public function getHighestQueue($team_id) {
        $draftqueues = DraftQueue::where('leagueuser_id',$team_id)->get();
        $highestqueue = 0;
        foreach ($draftqueues as $queue_item) {
            if ($queue_item->queue_order > $highestqueue) $highestqueue = $queue_item->queue_order;
        }

        return $highestqueue;
    }

    public function reorderDraftQueue($team_id) {
        $draftqueues = DraftQueue::where('leagueuser_id',$team_id)->orderBy('queue_order','asc')->get();

        $counter = 1;
        foreach($draftqueues as $queue_item) {
            $update = DraftQueue::where('id',$queue_item->id)
                ->update([
                    'queue_order'=>$counter
                ]);
            $counter++;
        }
    }

    public function draftPlayer(Request $request) {
        $leagueuser = LeagueUser::where('league_id',$request->leagueId)->where('user_id',Auth::user()->id)->first();
        $league = League::where('id',$request->leagueId)->first();
        

        if ($league->draft_current_drafter == $leagueuser->id) {
            $rosteritem = new RosterItem;
            $rosteritem->team_id = $leagueuser->id;
            $rosteritem->league_id = $leagueuser->league_id;
            $rosteritem->player_id = $request->player_id;
            $rosteritem->save();

            // get leagueUsers
            // delete drafted player for draft queues

            $leagueusers = LeagueUser::where('league_id',$request->leagueId)->get();
            foreach($leagueusers as $team) {
                $delete = DraftQueue::where('leagueuser_id',$team->id)
                    ->where('player_id',$request->player_id)
                    ->delete();
                $this->reorderDraftQueue($team->id);
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
        $target_queue_item = DraftQueue::where('leagueuser_id',$leagueuser->id)
            ->where('player_id',$request->player_id)
            ->first();
        
        $queue_item_in_the_way = DraftQueue::where('leagueuser_id',$leagueuser->id)
            ->where('queue_order',$target_queue_item->queue_order-1)
            ->first();

        if ($queue_item_in_the_way) {
            $update = DraftQueue::where('id',$queue_item_in_the_way->id)
                ->update([
                    'queue_order'=>$target_queue_item->queue_order
                ]);
        }
        $secondupdate = DraftQueue::where('id',$target_queue_item->id)
            ->update([
                'queue_order'=>$target_queue_item->queue_order-1
            ]);

            $this->reorderDraftQueue($leagueuser->id);
    }

    public function moveDownQueue(Request $request) {
        $leagueuser = LeagueUser::where('league_id',$request->leagueId)->where('user_id',Auth::user()->id)->first();
        $target_queue_item = DraftQueue::where('leagueuser_id',$leagueuser->id)
            ->where('player_id',$request->player_id)
            ->first();
        
        $queue_item_in_the_way = DraftQueue::where('leagueuser_id',$leagueuser->id)
            ->where('queue_order',$target_queue_item->queue_order+1)
            ->first();

        if ($queue_item_in_the_way) {
            $update = DraftQueue::where('id',$queue_item_in_the_way->id)
                ->update([
                    'queue_order'=>$target_queue_item->queue_order
                ]);
        }
        $secondupdate = DraftQueue::where('id',$target_queue_item->id)
            ->update([
                'queue_order'=>$target_queue_item->queue_order+1
            ]);

            $this->reorderDraftQueue($leagueuser->id);
    }

    public function removeFromQueue(Request $request) {
        $leagueuser = LeagueUser::where('league_id',$request->leagueId)->where('user_id',Auth::user()->id)->first();
        $draftqueue = DraftQueue::where('leagueuser_id',$leagueuser->id)
            ->where('player_id',$request->player_id)
            ->delete();
        $this->reorderDraftQueue($leagueuser->id);
    }
}
