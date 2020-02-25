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
use App\Models\DraftQueue;
use App\Models\Matchup;
use App\Models\Waiver;
use App\Models\Trade;
use App\Models\Sport;
use App\Models\Lineup;
use App\Models\Eligibility;
use App\Models\PlayerStat;
use App\Models\LeagueTransaction;
use Carbon\Carbon;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

include(app_path() . '/../vendor/round-robin/round-robin/src/round-robin.php');

class LeagueController extends Controller
{
    public function getWaivers(Request $request) {
        $league = League::where('id',$request->leagueId)->first();
        if ($league->commish_id == Auth::user()->id) {
            $waivers = Waiver::where('league_id',$request->leagueId)
                ->get();

            return response()->json($waivers);
        }
    }
    public function updateWaiverStatus(Request $request) {
        $league = League::where('id',$request->leagueId)->first();
        if ($league->commish_id == Auth::user()->id) {
            $update = League::where('id',$league->id)
                ->update([
                    'waiver_status'=>$request->status
                ]);
            $lastUpdate = uniqid();
            Cache::put('leagueUpdate'.$request->input('leagueId'), $lastUpdate,600);
        }
    }
    public function getTransactions(Request $request) {
        $transactions = LeagueTransaction::where('league_id',$request->leagueId)
            ->orderBy('created_at','DESC')
            ->get();

        foreach ($transactions as $key=>$value) {
            $transactions[$key]->team1_selected = unserialize($transactions[$key]->team1_selected);
            $transactions[$key]->team2_selected = unserialize($transactions[$key]->team2_selected);
        }
        return response()->json($transactions);
    }
    public function processWaiver(Request $request) {
        $league = League::where('id',$request->leagueId)->first();
        if ($league->commish_id == Auth::user()->id) {
            // get waiver
            $waiver = Waiver::where('id',$request->waiver_id)->first();
            $team = LeagueUser::where('league_id',$request->leagueId)
                ->where('id',$waiver->team_id)
                ->first();

            $newRosterItem = new RosterItem;
            $newRosterItem->team_id = $team->id;
            $newRosterItem->league_id = $league->id;
            $newRosterItem->player_id = $waiver->player_id;
            $newRosterItem->save();
            
            // drop the drop_id player
            if (isset($waiver->drop_player_id) && $waiver->drop_player_id > 0) {
                $drop_player = RosterItem::where('team_id',$team->id)
                    ->where('league_id',$league->id)
                    ->where('player_id',$waiver->drop_player_id)
                    ->delete();
            }

            // create the league transaction
            $transaction = new LeagueTransaction;
            $transaction->league_id = $request->leagueId;
            $transaction->type = 2;
            $transaction->team1_id = $waiver->team_id;
            $transaction->player_id = $waiver->player_id;
            $transaction->drop_player_id = $waiver->drop_player_id;
            $transaction->save();

            // remove from this week's lineup
            $sport = Sport::where('id',8)->first();

            $delete = Lineup::where('league_id',$league->id)
                ->where('player_id',$waiver->drop_player_id)
                ->where('week','>=',$sport->current_week)
                ->delete();

            // delete any other waivers in the league with the same player
            $deleteWaivers = Waiver::where('league_id',$league->id)
                ->where('player_id',$waiver->player_id)
                ->delete();

            // delete any trades with the drop player
            $all_trades = Trade::where('league_id',$league->id)
                ->get();

            foreach($all_trades as $trade) {
                $foundPlayer = false;
                foreach (unserialize($trade->team1_selected) as $player_id) {
                    if ($player_id == $waiver->drop_player_id) $foundPlayer = true;
                }
                foreach (unserialize($trade->team2_selected) as $player_id) {
                    if ($player_id == $waiver->drop_player_id) $foundPlayer = true;
                }
                if ($foundPlayer) {
                    $delete = Trade::where('league_id',$league->id)
                        ->where('id',$trade->id)
                        ->delete();
                }
            }

        }
    }
    public function denyWaiver(Request $request) {
        $league = League::where('id',$request->leagueId)->first();
        if ($league->commish_id == Auth::user()->id) {
            $delete = Waiver::where('id',$request->waiver_id)
                ->where('league_id',$request->leagueId)
                ->delete();
        }
    }
    public function updatePlayerEligibility(Request $request) {
        $search = Eligibility::where('league_id',$request->leagueId)
            ->where('player_id',$request->player_id)
            ->first();

        if ($search) {
            // update
            $update = Eligibility::where('league_id',$request->leagueId)
                ->where('player_id',$request->player_id)
                ->update([
                    'position'=>$request->position
                ]);
        } else {
            // insert
            $newE = new Eligibility;
            $newE->player_id = $request->player_id;
            $newE->league_id = $request->leagueId;
            $newE->position = $request->position;
            $newE->save();
        }
        // get current week
        $sport = Sport::where('id',8)->first();
        $update = Lineup::where('league_id',$request->leagueId)
            ->where('week',$sport->current_week)
            ->where('player_id',$request->player_id)
            ->update([
                'position'=>"BENCH"
            ]);

    }
    public function getPlayerEligibilities(Request $request) {
        $elig = Eligibility::where('league_id',$request->leagueId)
            ->get();
        return response()->json($elig);
    }
    public function getuserid() {
        return response()->json(Auth::user()->id);
    }
    public function calculatePlayerScore($league_id, $player_id, $week) {
        $league = League::where('id',$league_id)->first();
        $stats = PlayerStat::where('player_id',$player_id)
            ->where('week',$week)
            ->first();
        $score = 0;

        if ($stats) {

            $score = $score +
                $league->rule1 * $stats->rule1 +
                $league->rule2 * $stats->rule2 +
                $league->rule3 * $stats->rule3 +
                $league->rule4 * $stats->rule4 +
                $league->rule5 * $stats->rule5 +
                $league->rule6 * $stats->rule6 +
                $league->rule7 * $stats->rule7 +
                $league->rule8 * $stats->rule8 +
                $league->rule9 * $stats->rule9 +
                $league->rule10 * $stats->rule10 +
                $league->rule11 * $stats->rule11 +
                $league->rule12 * $stats->rule12 +
                $league->rule13 * $stats->rule13 +
                $league->rule14 * $stats->rule14 +
                $league->rule15 * $stats->rule15 +
                $league->rule16 * $stats->rule16 +
                $league->rule17 * $stats->rule17 +
                $league->rule18 * $stats->rule18 +
                $league->rule19 * $stats->rule19 +
                $league->rule20 * $stats->rule20 +
                $league->rule21 * $stats->rule21 +
                $league->rule22 * $stats->rule22 +
                $league->rule23 * $stats->rule23 +
                $league->rule24 * $stats->rule24 +
                $league->rule25 * $stats->rule25 +
                $league->rule26 * $stats->rule26 +
                $league->rule27 * $stats->rule27 +
                $league->rule28 * $stats->rule28 +
                $league->rule29 * $stats->rule29 +
                $league->rule30 * $stats->rule30 +
                $league->rule31 * $stats->rule31 +
                $league->rule32 * $stats->rule32 +
                $league->rule33 * $stats->rule33 +
                $league->rule34 * $stats->rule34;
        }
        return $score;
    }
    public function getLineup(Request $request) {
        $players = Lineup::where('league_id',$request->leagueId)
            ->where('team_id',$request->team_id)
            ->where('week', $request->week)
            ->get();

        if ($players->count() == 0) {
            // put all players on bench
            $roster = RosterItem::where('league_id',$request->leagueId)
                ->where('team_id',$request->team_id)
                ->get();
            foreach ($roster as $player) {
                $lineup = new Lineup;
                $lineup->week = $request->week;
                $lineup->team_id = $request->team_id;
                $lineup->league_id = $request->leagueId;
                $lineup->player_id = $player->player_id;
                $lineup->position = "BENCH";
                $lineup->save();
            }
            $players = Lineup::where('league_id',$request->leagueId)
                ->where('team_id',$request->team_id)
                ->where('week', $request->week)
                ->get();
        } else {
            $roster = RosterItem::where('league_id',$request->leagueId)
                ->where('team_id',$request->team_id)
                ->get();
            foreach ($roster as $player) {
                $check = Lineup::where('league_id',$request->leagueId)
                    ->where('team_id',$request->team_id)
                    ->where('player_id',$player->player_id)
                    ->where('week',$request->week)
                    ->get();
                if ($check->count() == 0) {
                    $lineup = new Lineup;
                    $lineup->week = $request->week;
                    $lineup->team_id = $request->team_id;
                    $lineup->league_id = $request->leagueId;
                    $lineup->player_id = $player->player_id;
                    $lineup->position = "BENCH";
                    $lineup->save();
                }
            }
            $players = Lineup::where('league_id',$request->leagueId)
                ->where('team_id',$request->team_id)
                ->where('week', $request->week)
                ->get();
        }
        // get week 1 score for each player
        for ($i = 0; $i < $players->count(); $i++) {
            $players[$i]->week1_score = $this->calculatePlayerScore($request->leagueId, $players[$i]->player_id, 1);
        }
        // get week 2 score for each player
        for ($i = 0; $i < $players->count(); $i++) {
            $players[$i]->week2_score = $this->calculatePlayerScore($request->leagueId, $players[$i]->player_id, 2);
        }
        for ($i = 0; $i < $players->count(); $i++) {
            $players[$i]->week3_score = $this->calculatePlayerScore($request->leagueId, $players[$i]->player_id, 3);
        }
        return $players;
    }
    
    public function startPlayer(Request $request) {
        $update = Lineup::where('league_id',$request->leagueId)
            ->where('team_id',$request->team_id)
            ->where('week',$request->week)
            ->where('player_id',$request->player_id)
            ->update([
                'position'=>$request->position
            ]);
        $this->calculateTeamScore($request->leagueId,$request->week);

    }
    public function benchPlayer(Request $request) {
        $update = Lineup::where('league_id',$request->leagueId)
            ->where('team_id',$request->team_id)
            ->where('week',$request->week)
            ->where('player_id',$request->player_id)
            ->update([
                'position'=>"BENCH"
            ]);
        $this->calculateTeamScore($request->leagueId,$request->week);

    }
    public function calculateTeamScore($leagueId,$week) {
        $teams = LeagueUser::where('league_id',$leagueId)->get();
        $sport = Sport::where('id',8)->first();

        foreach ($teams as $team) {
            $lineup = Lineup::where('team_id',$team->id)
                ->where('position','<>','BENCH')
                ->where('week', $week)
                ->get();
            $score = 0;
            foreach($lineup as $player) {
                $score = $score + $this->calculatePlayerScore($leagueId, $player->player_id, $week);
            }
            DB::transaction(function () use ($leagueId, $week, $team, $score) {
                $update_home_team = Matchup::where('league_id',$leagueId)
                    ->where('week',$week)
                    ->where('home_id',$team->id)
                    ->update([
                        'home_score'=>$score
                    ]);
                $update_away_team = Matchup::where('league_id',$leagueId)
                    ->where('week',$week)
                    ->where('away_id',$team->id)
                    ->update([
                        'away_score'=>$score
                    ]);
            }, 5);
        }
        if ($week < $sport->current_week) $this->calculateStandings($leagueId);

    }
    public function calculateStandings($leagueId) {
        $league = League::where('id',$leagueId)->first();

        $teams = LeagueUser::where('league_id',$league->id)->get();
        foreach ($teams as $team) {
            $update = LeagueUser::where('id',$team->id)
                ->update([
                    'wins'=>0,
                    'losses'=>0,
                    'ties'=>0,
                    'pf'=>0,
                    'pa'=>0
                ]);
        }
        for ($week = 1; $week < $league->week; $week++) {
            $matchups = Matchup::where('league_id',$league->id)
                ->where('week',$week)
                ->get();

            foreach ($matchups as $matchup) {
                $home_team = "";
                $away_team = "";
                if ($matchup->home_id) $home_team = LeagueUser::where('id',$matchup->home_id)->first();
                if ($matchup->away_id) $away_team = LeagueUser::where('id',$matchup->away_id)->first();
                
                if ($matchup->home_score > $matchup->away_score) {
                    // home wins
                    if ($home_team) {
                        $update = LeagueUser::where('id',$matchup->home_id)
                            ->update([
                                'wins'=>$home_team->wins + 1,
                                'pf'=>$home_team->pf + $matchup->home_score,
                                'pa'=>$home_team->pa + $matchup->away_score
                            ]);
                    }
                    if ($away_team) {
                        $update = LeagueUser::where('id',$matchup->away_id)
                            ->update([
                                'losses'=>$away_team->losses + 1,
                                'pf'=>$away_team->pf + $matchup->away_score,
                                'pa'=>$away_team->pa + $matchup->home_score
                            ]);
                    }
                } else if ($matchup->away_score > $matchup->home_score) {
                    // away wins
                    if ($away_team) {
                        $update = LeagueUser::where('id',$matchup->away_id)
                            ->update([
                                'wins'=>$away_team->wins + 1,
                                'pf'=>$away_team->pf + $matchup->away_score,
                                'pa'=>$away_team->pa + $matchup->home_score
                            ]);
                    }
                    if ($home_team) {
                        $update = LeagueUser::where('id',$matchup->home_id)
                            ->update([
                                'losses'=>$home_team->losses + 1,
                                'pf'=>$home_team->pf + $matchup->home_score,
                                'pa'=>$home_team->pa + $matchup->away_score
                            ]);
                    }
                } else if ($matchup->away_score == $matchup->home_score) {
                    // tie
                    if ($home_team) {
                        $update = LeagueUser::where('id',$matchup->home_id)
                            ->update([
                                'ties'=>$home_team->ties + 1,
                                'pf'=>$home_team->pf + $matchup->home_score,
                                'pa'=>$home_team->pa + $matchup->away_score
                            ]);
                    }
                    if ($away_team) {
                        $update = LeagueUser::where('id',$matchup->away_id)
                            ->update([
                                'ties'=>$away_team->ties + 1,
                                'pf'=>$away_team->pf + $matchup->away_score,
                                'pa'=>$away_team->pa + $matchup->home_score
                            ]);
                    }
                }
            }
        }
    }
    public function createTrade(Request $request) {
        $trade = new Trade;
        $trade->league_id = $request->leagueId;
        $trade->team1_id = $request->team1;
        $trade->team2_id = $request->team2;
        $trade->team1_selected = serialize($request->team1_players);
        $trade->team2_selected = serialize($request->team2_players);
        $trade->save();
    }
    public function cancelTrade(Request $request) {
        $delete = Trade::where('league_id', $request->leagueId)
            ->where('id',$request->trade_id)
            ->delete();
    }
    public function acceptTrade(Request $request) {
        // get the trade
        $trade = Trade::where('league_id',$request->leagueId)
            ->where('id',$request->trade_id)
            ->first();
        // get the teams
        $team1 = LeagueUser::where('league_id',$request->leagueId)
            ->where('id',$trade->team1_id)
            ->first();
        $team2 = LeagueUser::where('league_id',$request->leagueId)
            ->where('id',$trade->team2_id)
            ->first();
        // exchange players
        $team1_player_ids_to_get = unserialize($trade->team1_selected);
        $team2_player_ids_to_get = unserialize($trade->team2_selected);

        foreach($team1_player_ids_to_get as $player_id) {
            $update = RosterItem::where('player_id',$player_id)
                ->where('league_id',$request->leagueId)
                ->update([
                    'team_id'=>$team1->id
                ]);
        }

        foreach($team2_player_ids_to_get as $player_id) {
            $update = RosterItem::where('player_id',$player_id)
                ->where('league_id',$request->leagueId)
                ->update([
                    'team_id'=>$team2->id
                ]);
        }

        // Make a record of the transaction
        $transaction = new LeagueTransaction;
        $transaction->league_id = $request->leagueId;
        $transaction->type = 1;
        $transaction->team1_selected = $trade->team1_selected;
        $transaction->team2_selected = $trade->team2_selected;
        $transaction->team1_id = $trade->team1_id;
        $transaction->team2_id = $trade->team2_id;
        $transaction->save();

        

        // delete other trades in that league with either of those players in it
        $all_trades = Trade::where('league_id',$request->leagueId)
            ->get();

        foreach($all_trades as $trade) {
            $foundPlayer = false;
            foreach (unserialize($trade->team1_selected) as $player_id) {
                if (in_array($player_id, $team1_player_ids_to_get)) $foundPlayer = true;
                if (in_array($player_id, $team2_player_ids_to_get)) $foundPlayer = true;
            }
            if ($foundPlayer) {
                $delete = Trade::where('league_id',$request->leagueId)
                    ->where('id',$trade->id)
                    ->delete();
            }
        }

        // delete lineups with those players in it
        $sport = Sport::where('id',8)->first();
        foreach ($team1_player_ids_to_get as $player_id) {
            $delete = Lineup::where('week',$sport->current_week)
                ->where('league_id',$request->leagueId)
                ->where('player_id',$player_id)
                ->delete();
        }
        foreach ($team2_player_ids_to_get as $player_id) {
            $delete = Lineup::where('week',$sport->current_week)
                ->where('league_id',$request->leagueId)
                ->where('player_id',$player_id)
                ->delete();
        }
    }
    public function fixMatchups(Request $request) {
        $delete = Matchup::where('league_id',$request->leagueId)->delete();
    }
    public function getTrades(Request $request) {
        $team = LeagueUser::where('league_id',$request->leagueId)
            ->where('user_id',Auth::user()->id)
            ->first();
        if ($team) {
            $trades = Trade::where('league_id',$request->leagueId)
                ->where('team1_id',$team->id)
                ->orWhere('team2_id',$team->id)
                ->get();

            for ($i = 0; $i < $trades->count(); $i++) {
                $trades[$i]->team1_selected = unserialize($trades[$i]->team1_selected);
                $trades[$i]->team2_selected = unserialize($trades[$i]->team2_selected);
            }

            return response()->json($trades);
        }
    }
    public function cancelWaiver(Request $request) {
        $delete = Waiver::where('id',$request->waiver_id)->delete();
    }
    public function updateMatchup(Request $request) {
        if ($request->homeOrAway == "home") {
            $update = Matchup::where('id',$request->matchupId)
                ->update([
                    'home_id'=>$request->targetTeam
                ]);
        } else {
            $update = Matchup::where('id',$request->matchupId)
                ->update([
                    'away_id'=>$request->targetTeam
                ]);
        }
            
    }
    public function generateMatchups($leagueId) {
        

        $teams = LeagueUser::where('league_id',$leagueId)->get();
        $league = League::where('id',$leagueId)->first();
        $teamids = [];
        $numOfRegularWeeks = 10 - $league->playoff_length;

        if ($league->league_type > 1) {
            for ($week = 1; $week <= 10; $week++) {
                foreach($teams as $team) {
                    $newmatchup = new Matchup;
                    $newmatchup->home_id = $team->id;
                    $newmatchup->away_id = 0;
                    $newmatchup->league_id = $leagueId;
                    $newmatchup->week = $week;
                    $newmatchup->save();
                }
            }
        } else {
            foreach($teams as $aTeam) {
                $teamids[] = $aTeam->id;
            }
            $scheduleBuiler = new \ScheduleBuilder($teamids,$numOfRegularWeeks);
            $schedule = $scheduleBuiler->build();

            foreach($schedule as $week=>$matchups) {
                foreach($matchups as $matchup) {
                    $newmatchup = new Matchup;
                    if (!$matchup[0]) {
                        $newmatchup->home_id = 0;
                    } else {
                        $newmatchup->home_id = $matchup[0];
                    }
                    if (!$matchup[1]) {
                        $newmatchup->away_id = 0;
                    } else {
                        $newmatchup->away_id = $matchup[1];
                    }
                    $newmatchup->league_id = $leagueId;
                    $newmatchup->week = $week;
                    $newmatchup->save();
                }
            }
        }
    }
    public function tempClearMatchups() {
        $leagues = League::where('league_type','>',1)->get();
        foreach($leagues as $league) {
            $delete = Matchup::where('league_id',$league->id)->delete();
            $league->week = 1;
            $league->save();
        }
    }
    public function getMatchups(Request $request) {
        $matchups = Matchup::where('league_id',$request->leagueId)->get();
        

        if ($matchups->count() == 0) {
            $this->generateMatchups($request->leagueId);
            $matchups = Matchup::where('league_id',$request->leagueId)->get();
            return $matchups;
        } else {
            return $matchups;
        }
    }
    public function assignPlayer(Request $request) {
        // first, remove that player_id from the league
        $delete = RosterItem::where('league_id',$request->leagueId)
            ->where('player_id',$request->player_id)
            ->delete();
        $sport = Sport::where('id',8)->first();
        $delete = Lineup::where('week',$sport->current_week)
            ->where('league_id',$request->leagueId)
            ->where('player_id',$request->player_id)
            ->delete();
        // then, add that player to the team
        if ($request->team_id > 0) {
            $newrosteritem = new RosterItem;
            $newrosteritem->league_id = $request->leagueId;
            $newrosteritem->player_id = $request->player_id;
            $newrosteritem->team_id = $request->team_id;
            $newrosteritem->save();
        }

        $lastUpdate = uniqid();
        Cache::put('leagueUpdate'.$request->input('leagueId'), $lastUpdate,600);
    }
    public function removeTeam(Request $request) {
        

        // first, change draft orders
        $teamToRemove = LeagueUser::where('id',$request->input('team'))
            ->where('league_id',$request->input('leagueId'))->first();

        // get all teams in league
        $allTeamsInLeague = LeagueUser::where('league_id',$request->input('leagueId'))->get();

        foreach($allTeamsInLeague as $singleTeam) {
            if ($singleTeam->draft_order > $teamToRemove->draft_order) {
                $update = LeagueUser::where('id',$singleTeam->id)
                    ->where('league_id',$request->input('leagueId'))
                    ->update([
                        "draft_order"=>$singleTeam->draft_order - 1
                    ]);
            }
        }

        $team = LeagueUser::where('id',$request->input('team'))
            ->where('league_id',$request->input('leagueId'))->delete();


        $this->setDraftOrder($request->input('leagueId'));
        $league = League::where('id',$request->input('leagueId'))->first();
        if ($league->draft_status < 1) $matchups = Matchup::where('league_id',$request->input('leagueId'))->delete();
        $lastUpdate = uniqid();
        Cache::put('leagueUpdate'.$request->input('leagueId'), $lastUpdate,600);
    }

    public function deleteLeague(Request $request) {
        $league = League::where('id',$request->leagueId)->delete();
        
        $teams = LeagueUser::where('league_id',$request->leagueId)->delete();

    }
    public function updateDraft(Request $request) {
        $league = League::where('id',$request->input('leagueId'))
            ->update([
                'draft_datetime'=>Carbon::parse($request->input('datetime')),
                'draft_status'=>0,
            ]);

            $rosters = RosterItem::where('league_id',$request->input('leagueId'))->delete();

            $this->setDraftOrder($request->input('leagueId'));
        
            $lastUpdate = uniqid();
            Cache::put('leagueUpdate'.$request->input('leagueId'), $lastUpdate,600);
    }
    public function dropPlayer(Request $request) {
        $team = LeagueUser::where('league_id',$request->leagueId)->where('user_id',Auth::user()->id)->first();
        // remove roster item
        $player = RosterItem::where('league_id',$request->leagueId)
            ->where('player_id',$request->player_id)
            ->first();
        
        $delete = RosterItem::where('league_id',$request->leagueId)
            ->where('player_id',$request->player_id)
            ->delete();
        // remove from this week's lineups
        if ($player) {
            $sport = Sport::where('id',8)->first();

            $delete = Lineup::where('week',$sport->current_week)
                ->where('league_id',$request->leagueId)
                ->where('player_id',$request->player_id)
                ->delete();
            // remove any trades associated with him
            $all_trades = Trade::where('league_id',$request->leagueId)
                ->get();

            foreach($all_trades as $trade) {
                $foundPlayer = false;
                foreach (unserialize($trade->team1_selected) as $player_id) {
                    if (in_array($request->player_id, $team1_player_ids_to_get)) $foundPlayer = true;
                    if (in_array($request->player_id, $team2_player_ids_to_get)) $foundPlayer = true;
                }
                if ($foundPlayer) {
                    $delete = Trade::where('league_id',$request->leagueId)
                        ->where('id',$trade->id)
                        ->delete();
                }
            }
            // remove any waivers where he's the drop player
            $delete = Waiver::where('league_id',$request->leagueId)
                ->where('drop_player_id',$request->player_id)
                ->delete();

            $lastUpdate = uniqid();
            Cache::put('leagueUpdate'.$request->leagueId, $lastUpdate,600);
        }
    }
    public function updateRoster(Request $request) {
        $league = League::where('id',$request->input('leagueId'))
            ->update([
                'qbs'=>$request->input('qbs'),
                'rbs'=>$request->input('rbs'),
                'wrs'=>$request->input('wrs'),
                'tes'=>$request->input('tes'),
                'flex'=>$request->input('flex'),
                'superflex'=>$request->input('superflex'),
                'ks'=>$request->input('ks'),
                'def'=>$request->input('def'),
                'bench'=>$request->input('bench'),
                'teamQbs'=>$request->input('teamQbs'),
                'teamKs'=>$request->input('teamKs')
            ]);
        
            $this->createDraftPicks($request->input('leagueId'));
            
            $lastUpdate = uniqid();
            Cache::put('leagueUpdate'.$request->input('leagueId'), $lastUpdate,600);
    }
    public function updateSettings(Request $request) {
        $league = League::where('id',$request->input('leagueId'))
            ->update([
                'waiver_day'=>$request->input('waiver_day'),
                'draftpick_time'=>$request->input('draftpick_time')
            ]);
        
            $lastUpdate = uniqid();
            Cache::put('leagueUpdate'.$request->input('leagueId'), $lastUpdate,600);
    }
    public function updateRules(Request $request) {
        $league = League::where('id',$request->input('leagueId'))
            ->update([
                'rule1'=>$request->input('rule1'),
                'rule2'=>$request->input('rule2'),
                'rule3'=>$request->input('rule3'),
                'rule4'=>$request->input('rule4'),
                'rule5'=>$request->input('rule5'),
                'rule6'=>$request->input('rule6'),
                'rule7'=>$request->input('rule7'),
                'rule8'=>$request->input('rule8'),
                'rule9'=>$request->input('rule9'),
                'rule10'=>$request->input('rule10'),
                'rule11'=>$request->input('rule11'),
                'rule12'=>$request->input('rule12'),
                'rule13'=>$request->input('rule13'),
                'rule14'=>$request->input('rule14'),
                'rule15'=>$request->input('rule15'),
                'rule16'=>$request->input('rule16'),
                'rule17'=>$request->input('rule17'),
                'rule18'=>$request->input('rule18'),
                'rule19'=>$request->input('rule19'),
                'rule20'=>$request->input('rule20'),
                'rule21'=>$request->input('rule21'),
                'rule22'=>$request->input('rule22'),
                'rule23'=>$request->input('rule23'),
                'rule24'=>$request->input('rule24'),
                'rule25'=>$request->input('rule25'),
                'rule26'=>$request->input('rule26'),
                'rule27'=>$request->input('rule27'),
                'rule28'=>$request->input('rule28'),
                'rule29'=>$request->input('rule29'),
                'rule30'=>$request->input('rule30'),
                'rule31'=>$request->input('rule31'),
                'rule32'=>$request->input('rule32'),
                'rule33'=>$request->input('rule33'),
                'rule34'=>$request->input('rule34'),
            ]);
                    
            $lastUpdate = uniqid();
            Cache::put('leagueUpdate'.$request->input('leagueId'), $lastUpdate,600);
    }
    public function moveUpDraftOrder(Request $request) {
        $requested_team = LeagueUser::where('id',$request->input('team_id'))->first();
        $other_team = LeagueUser::where('draft_order',$requested_team->draft_order-1)
            ->where('league_id',$requested_team->league_id)
            ->first();

        $other_draft_order = $requested_team->draft_order;

        $update_one = LeagueUser::where('id',$requested_team->id)->update([
            'draft_order'=>$other_team->draft_order
        ]);
        $update_two = LeagueUser::where('id',$other_team->id)->update([
            'draft_order'=>$other_draft_order
        ]);

        $this->createDraftPicks($requested_team->league_id);
        $lastUpdate = uniqid();
        Cache::put('leagueUpdate'.$request->input('leagueId'), $lastUpdate,600);
    }
    public function moveDownDraftOrder(Request $request) {
        $requested_team = LeagueUser::where('id',$request->input('team_id'))->first();
        $other_team = LeagueUser::where('draft_order',$requested_team->draft_order+1)
            ->where('league_id',$requested_team->league_id)
            ->first();
        $other_draft_order = $requested_team->draft_order;

        $update_one = LeagueUser::where('id',$requested_team->id)->update([
            'draft_order'=>$other_team->draft_order
        ]);
        $update_two = LeagueUser::where('id',$other_team->id)->update([
            'draft_order'=>$other_draft_order
        ]);

        $this->createDraftPicks($requested_team->league_id);
        $lastUpdate = uniqid();
        Cache::put('leagueUpdate'.$request->input('leagueId'), $lastUpdate,600);
    }
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
        $league->superflex = $request->input('superflex');

        $league->teamQbs = $request->input('teamQbs');
        $league->teamKs = $request->input('teamKs');

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
        $league->rule26 = $request->input('rule26');
        $league->rule27 = $request->input('rule27');
        $league->rule28 = $request->input('rule28');
        $league->rule29 = $request->input('rule29');
        $league->rule30 = $request->input('rule30');
        $league->rule31 = $request->input('rule31');
        $league->rule32 = $request->input('rule32');
        $league->rule33 = $request->input('rule33');
        $league->rule34 = $request->input('rule34');
        

        $league->save();

        if (!$request->input('justCommish')) {
            // Add commish to league
            $leagueUser = new LeagueUser;
            $leagueUser->user_id = Auth::user()->id;
            $leagueUser->league_id = $league->id;
            $leagueUser->name = $request->input('teamname');
            
            $leagueUser->save();
            $this->setDraftOrder($league->id);
        }
    }

    public function getUserLeagues(Request $request) {
        $leagues = array();
        $league_users = LeagueUser::where('user_id',Auth::user()->id)->get();
        $league_ids = array();
        foreach($league_users as $lu) {
            $leagues[] = League::where('id',$lu->league_id)->first();
            $league_ids[] = $lu->league_id;
        }

        // get commissioned leagues
        $commishLeagues = League::where('commish_id',Auth::user()->id)->get();
        foreach($commishLeagues as $commishLeague) {
            if (!in_array($commishLeague->id,$league_ids)) $leagues[] = $commishLeague;
        }

        return response()->json($leagues);
    }

    public function getrosters(Request $request) {
        $teamInfo = LeagueUser::where('league_id',$request->input('leagueId'))->get();
        $teams = array();
        foreach ($teamInfo as $team) {
            $rosterItems = RosterItem::where('team_id',$team->id)->get();
            foreach ($rosterItems as $key=>$item) {
                $player = Player::where('id',$item->player_id)->first();
                if ($player) {
                    $rosterItems[$key]['player_name'] = $player->name;
                }
            }
            $teams[$team->id] = $rosterItems;
        }
        return response()->json($teams);
    }

    public function getLeagueInfoFromCode($code) {
        $league = League::where('invite_code',$code)->first();

        return response()->json($league);
    }

    public function updateName(Request $request) {
        $updatedTeam = LeagueUser::where('league_id',$request->leagueId)
            ->where('user_id',Auth::user()->id)
            ->update([
                'name'=>$request->newName
            ]);
            $lastUpdate = uniqid();
            Cache::put('leagueUpdate'.$request->input('leagueId'), $lastUpdate,600);
    }

    public function updateSize(Request $request) {
        $updatedLeague = League::where('id',$request->leagueId)
            ->update([
                'maxSize'=>$request->maxSize
            ]);
            $lastUpdate = uniqid();
            Cache::put('leagueUpdate'.$request->input('leagueId'), $lastUpdate,600);
    }

    public function updateType(Request $request) {
        $updatedLeague = League::where('id',$request->leagueId)
            ->update([
                'league_type'=>$request->league_type
            ]);
            $lastUpdate = uniqid();
            Cache::put('leagueUpdate'.$request->input('leagueId'), $lastUpdate,600);
    }

    public function updatePlayoffLength(Request $request) {
        $updatedLeague = League::where('id',$request->leagueId)
            ->update([
                'playoff_length'=>$request->playoff_length
            ]);

        $delete = Matchup::where('league_id',$request->leagueId)
                ->where('week','>',10-$request->playoff_length)
                ->delete();
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
            $matchups = Matchup::where('league_id',$request->input('leagueId'))->delete();
            $lastUpdate = uniqid();
            Cache::put('leagueUpdate'.$league->id, $lastUpdate,600);
        }
    }

    public function getLeagueInfo($id) {
        $league = League::where('id',$id)->first();
        if ($league) {
            $teams = LeagueUser::where('league_id',$league->id)->get();
            $sport = Sport::where('id',8)->first();
            $league->current_week = $sport->current_week;
            $league->teams = $teams;
        }
        return response()->json($league);
    }

    public function getTeamInfo($id) {
        $leagueUsers = LeagueUser::where('league_id',$id)
            ->orderBy('wins','desc')
            ->orderBy('pf','desc')
            ->get();
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

    function addFreeAgent(Request $request) {
        $team = LeagueUser::where('league_id',$request->leagueId)->where('user_id',Auth::user()->id)->first();

        $pickup = new RosterItem;
        $pickup->team_id = $team->id;
        $pickup->league_id = $request->leagueId;
        $pickup->player_id = $request->player_id;
        $pickup->save();

        // create transaction
        $transaction = new LeagueTransaction;
        $transaction->league_id = $request->leagueId;
        $transaction->type = 3;
        $transaction->team1_id = $team->id;
        $transaction->player_id = $request->player_id;
        if (isset($request->drop_player_id) && $request->drop_player_id) {
            $transaction->drop_player_id = $request->drop_player_id;
        }
        $transaction->save();

        if (isset($request->drop_player_id) && $request->drop_player_id) {
            $drop_player = RosterItem::where('league_id',$request->leagueId)
                ->where('team_id',$team->id)
                ->where('player_id',$request->drop_player_id)
                ->delete();
            
            $sport = Sport::where('id',8)->first();
            $delete_lineup = Lineup::where('week',$sport->current_week)
                ->where('league_id',$request->leagueId)
                ->where('player_id',$request->drop_player_id)
                ->delete();
        }

        $lastUpdate = uniqid();
        Cache::put('leagueUpdate'.$request->leagueId, $lastUpdate,600);
    }

    function createClaim(Request $request) {
        $team = LeagueUser::where('league_id',$request->leagueId)->where('user_id',Auth::user()->id)->first();

        $waiver = new Waiver;
        $waiver->league_id = $request->leagueId;
        $waiver->team_id = $team->id;
        $waiver->player_id = $request->player_id;
        $waiver->drop_player_id = $request->drop_player_id;
        $waiver->save();

        $lastUpdate = uniqid();
        Cache::put('leagueUpdate'.$request->leagueId, $lastUpdate,600);
    }

    function createDraftPicks($leagueId) {
        $league = League::where('id',$leagueId)->first();

        $oldPicks = DraftPick::where('league_id',$leagueId)->delete();

        $numPicks = $league->qbs + $league->rbs + $league->wrs + $league->tes + $league->flex + $league->superflex + $league->ks + $league->def + $league->bench;

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
        $end_time = Carbon::now()->addSeconds(10);
            // Start drafts
        //while (Carbon::now() < $end_time) {    
            $leagues = League::where('draft_status',0)
                ->where('draft_datetime','<=',Carbon::now())
                ->get();
            
            foreach($leagues as $league) {
                

                // update draft_current_drafter
                $draftPicks = DraftPick::where('league_id',$league->id)
                    ->whereNull('player_id')
                    ->orderBy('pick_order','asc')
                    ->first();
                
                if ($draftPicks) {
                    $update = League::where('id',$league->id)->update([ 
                        'draft_status'=>1,
                        'draft_nextpick'=>Carbon::now()->addMinutes($league->draftpick_time),
                        'draft_current_drafter'=>$draftPicks->team_id
                    ]);
                }

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
                

                $draftPick = DraftPick::where('league_id',$league->id)
                    ->whereNull('player_id')
                    ->orderBy('pick_order','asc')
                    ->first();
                
                $queue_item = "";
                if ($draftPick) {
                    // pick a player from the draft queue first
                $queue_item = DraftQueue::where('leagueuser_id',$draftPick->team_id)
                    ->orderBy('queue_order','asc')
                    ->first();
                }

                $player = "";
                if ($queue_item) {
                    $player = Player::where('id',$queue_item->player_id)
                        ->first();
                    
                }
                if (!$player) {
                    // pick a player not in that list
                    $player = Player::whereNotIn('id',$player_ids)
                        ->whereIn('position',array("WR","RB","TE","K","DEF"))
                        ->first();

                }
                if (!$player) {
                    $player_id = 0;
                } else {
                    $player_id = $player->id;
                }
                if ($draftPick && $player_id) {
                    $update = DraftPick::where('id',$draftPick->id)
                        ->update([
                            'player_id'=>$player_id
                        ]);
                        $rosteritem = new RosterItem;
                        $rosteritem->team_id = $draftPick->team_id;
                        $rosteritem->league_id = $league->id;
                        $rosteritem->player_id = $player_id;
                        $rosteritem->save();
                    
                    // delete players from draft queues
                    $leagueusers = LeagueUser::where('league_id',$league->id)->get();
                    foreach($leagueusers as $team) {
                        $delete = DraftQueue::where('leagueuser_id',$team->id)
                            ->where('player_id',$player_id)
                            ->delete();
                    }
                }

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
        //}
    }

    function stats() {
        $leagues = League::count();
        $users = User::count();
        $teams = LeagueUser::count();
        $drafted = League::where('draft_status',2)->count();
        $drafting = League::where('draft_status',1)->count();

        print_r("Leagues: ".$leagues."<br />Users: ".$users."<br />Teams: ".$teams."<br />Drafted: ".$drafted."<br />Drafting: ".$drafting);die();
    }
}
