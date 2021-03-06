<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\League;
use App\Models\User;
use App\Models\LeagueUser;
use App\Models\Player;
use App\Models\DraftPick;
use App\Models\Waiver;
use App\Models\RosterItem;
use App\Models\Trade;
use App\Models\Lineup;
use App\Models\PlayerStat;
use App\Models\Sport;
use App\Models\Matchup;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use App\Mail\TestEmail;

include(app_path() . '/../vendor/simple-html-dom/simple_html_dom.php');

class ScrapeController extends Controller
{
    public function convertTeamName($team_name) {
        if ($team_name == "SEA") {
            $team_name = "Seattle";
        } else if ($team_name == "TB") {
            $team_name = "Tampa Bay";
        } else if ($team_name == "NY") {
            $team_name = "New York";
        } else if ($team_name == "HOU") {
            $team_name = "Houston";
        } else if ($team_name == "STL") {
            $team_name = "St Louis";
        } else if ($team_name == "DAL") {
            $team_name = "Dallas";
        }
        return $team_name;
    }
    public function setStat($week,$player_name,$team_name,$stat_id,$stat_value) {
        $player_name_split = explode(".",$player_name);
        if (count($player_name_split) > 1) {
            $player_name = $player_name_split[1].", ".$player_name_split[0];
        }
        
        if ($player_name == "SEA" || $player_name == "TB" || $player_name == "NY" || $player_name == "HOU" || $player_name == "STL" || $player_name == "DAL" || $player_name == "DC" || $player_name == "LA") {
            $player = Player::where('name', 'LIKE', $this->convertTeamName($player_name).'%')
                ->where('position','QB')
                ->where('team',$this->convertTeamName($team_name))->first();
        } else {
            $player = Player::where('name', 'LIKE', $this->convertTeamName($player_name).'%')
                ->where('team',$this->convertTeamName($team_name))->first();
        }
        if ($player) {
            $current_record = PlayerStat::where('week',$week)->where('player_id',$player->id)->first();
            if (!$current_record) {
                $stat = new PlayerStat;
                $stat->week = $week;
                $stat->player_id = $player->id;
                $stat->save();
                
            }
            $res = PlayerStat::where('week',$week)->where('player_id',$player->id)->update([
                $stat_id=>$stat_value
            ]);
        }
    }
    public function lockTeam($team) {
        $sport = Sport::where('id',8)->first();
        $players = Player::where('team',$team)->get();
        foreach ($players as $player) {
            $update = Lineup::where('player_id',$player->id)
                ->where('week',$sport->current_week)
                ->update([
                    'locked'=>1
                ]);
        }
    }
    public function getStats($game) {
        $page = "https://stats.xfl.com/".$game;

        $html = file_get_html($page);

        print($html);die();


    }
    public function transferLineups() {
        $lastChecked = Cache::get('transferLineups');
        $teams = League::count();
        if (!$lastChecked) {
            $lastChecked = 0;
            
        }
        if ($lastChecked <= $teams) {
            Cache::put('transferLineups', $lastChecked+5,6000);
        } else {
            Cache::put('transferLineups', 0,6000);
        }

        $teams = LeagueUser::skip($lastChecked)
            ->take(5)
            ->get();

        foreach($teams as $team) {
            print($team->id."<br />");
            $league = League::where('id',$team->league_id)->first();
            if ($league) {
                if ($league->draft_status > 1) {
                    $players = Lineup::where('league_id',$league->id)
                        ->where('team_id',$team->id)
                        ->where('week', $league->week)
                        ->get();
                
                    $starting_players = 0;
                    foreach($players as $player) {
                        if ($player->position != "BENCH") {
                            $starting_players = $starting_players + 1;
                        }
                    }
                    if ($starting_players == 0) {
                        // pull in starting lineup from previous week
                        $old_lineup = Lineup::where('league_id',$league->id)
                            ->where('team_id',$team->id)
                            ->where('week',$league->week - 1)
                            ->where('position','<>','BENCH')
                            ->get();

                        foreach ($old_lineup as $transfer) {
                            $update = Lineup::where('league_id',$league->id)
                                ->where('team_id',$team->id)
                                ->where('week',$league->week)
                                ->where('player_id', $transfer->player_id)
                                ->where('locked',0)
                                ->update([
                                    'position'=>$transfer->position
                                ]);
                        }
                    }
                }

            }
            
        }
    }
    public function calculateScores() {
        $lastChecked = Cache::get('calculateScores');
        $leagues = League::count();
        if (!$lastChecked) {
            $lastChecked = 0;
            
        }
        if ($lastChecked <= $leagues) {
            Cache::put('calculateScores', $lastChecked+25,6000);
        } else {
            Cache::put('calculateScores', 0,6000);
        }
        
        $leagues = League::where('draft_status',2)
            ->skip($lastChecked)
            ->take(25)
            ->get();

        foreach($leagues as $league) {
            print($league->id."<br />");
            $sport = Sport::where('id',8)->first();
            $total_points_for = array();
            $teams = LeagueUser::where('league_id',$league->id)->get();

            foreach ($teams as $team) {
                $update = LeagueUser::where('id',$team->id)
                                    ->update([
                                        "pf"=>0,
                                        "pa"=>0,
                                        "wins"=>0,
                                        "losses"=>0,
                                        "ties"=>0
                                    ]);
            }
            for ($week = 1; $week <= $league->week; $week++) {
                $matchups = Matchup::where('league_id',$league->id)
                    ->where('week',$week)
                    ->get();
                
                foreach($matchups as $matchup) {
                    $home_team = LeagueUser::where('id',$matchup->home_id)
                        ->where('league_id',$league->id)
                        ->first();
                    if ($home_team) {
                        $players = Lineup::where('league_id',$league->id)
                            ->where('week',$week)
                            ->where('team_id',$home_team->id)
                            ->where('position', '<>', "BENCH")
                            ->get();
                        $score = 0;
                        foreach($players as $player) {
                            $stats = PlayerStat::where('player_id',$player->player_id)
                                ->where('week',$week)
                                ->first();

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
                                $score = round($score,2);
                            }
                        }
                        $update = Matchup::where('id',$matchup->id)
                            ->update([
                                'home_score'=>$score
                            ]);
                        if ($week < $league->week) {
                            if (array_key_exists($matchup->home_id,$total_points_for)) {
                                $total_points_for[$matchup->home_id] = $total_points_for[$matchup->home_id] + $score;
                            } else {
                                $total_points_for[$matchup->home_id] = $score;
                            }
                        }
                            
                    }

                    if ($matchup->away_id) {

                        $away_team = LeagueUser::where('id',$matchup->away_id)
                            ->where('league_id',$league->id)
                            ->first();
                        if ($away_team) {
                            $players = Lineup::where('league_id',$league->id)
                                ->where('week',$week)
                                ->where('team_id',$away_team->id)
                                ->where('position', '<>', "BENCH")
                                ->get();
                            $score = 0;
                            foreach($players as $player) {
                                $stats = PlayerStat::where('player_id',$player->player_id)
                                    ->where('week',$week)
                                    ->first();

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
                                    $score = round($score,2);
                                }
                            }

                            $update = Matchup::where('id',$matchup->id)
                                ->update([
                                    'away_score'=>$score
                                ]);
                            if ($week < $league->week) {
                                if (array_key_exists($matchup->away_id,$total_points_for)) {
                                    $total_points_for[$matchup->away_id] = $total_points_for[$matchup->away_id] + $score;
                                } else {
                                    $total_points_for[$matchup->away_id] = $score;
                                }
                            }
                        }
                    }
                }
                

                if ($week < $league->week) {
                    foreach($matchups as $matchup) {
                        if ($matchup->home_id) {
                            $home_team = LeagueUser::where('league_id',$league->id)
                                ->where('id',$matchup->home_id)
                                ->first();
                            if ($home_team) {
                                $update = LeagueUser::where('id',$home_team->id)
                                    ->update([
                                        "pf"=>$home_team->pf + $matchup->home_score,
                                        "pa"=>$home_team->pa + $matchup->away_score
                                    ]);
                            }
                        }

                        if ($matchup->away_id) {
                            $away_team = LeagueUser::where('league_id',$league->id)
                                ->where('id',$matchup->away_id)
                                ->first();
                                if ($away_team) {
                                    $update = LeagueUser::where('id',$away_team->id)
                                        ->update([
                                            "pf"=>$away_team->pf + $matchup->away_score,
                                            "pa"=>$away_team->pa + $matchup->home_score
                                        ]);
                                }
                        }

                        if ($matchup->home_id && $matchup->away_id) {
                            if ($home_team && $away_team) {
                                if ($matchup->home_score > $matchup->away_score) {
                                    $update = LeagueUser::where('id',$home_team->id)
                                        ->update([
                                            "wins"=>$home_team->wins + 1
                                        ]);
                                    $update = LeagueUser::where('id',$away_team->id)
                                        ->update([
                                            "losses"=>$away_team->losses + 1
                                        ]);
                                } else if ($matchup->away_score > $matchup->home_score) {
                                    $update = LeagueUser::where('id',$away_team->id)
                                        ->update([
                                            "wins"=>$away_team->wins + 1
                                        ]);
                                    $update = LeagueUser::where('id',$home_team->id)
                                        ->update([
                                            "losses"=>$home_team->losses + 1
                                        ]);
                                } else {
                                    $update = LeagueUser::where('id',$home_team->id)
                                        ->update([
                                            "ties"=>$home_team->ties + 1
                                        ]);
                                    $update = LeagueUser::where('id',$away_team->id)
                                        ->update([
                                            "ties"=>$away_team->ties + 1
                                        ]);
                                }
                            }
                        }
                    }
                }
            }
            
            foreach($teams as $team) {
                    
                if (array_key_exists($team->id,$total_points_for)) {
                    $update = LeagueUser::where('id',$team->id)
                        ->update([
                            'pf'=>$total_points_for[$team->id]
                        ]);
                }
            }

            // Advance week
            if ($league->week < $sport->current_week) {
                
                
                

                // Advance leauge week
                $update = League::where('id',$league->id)
                    ->update([
                        'week'=>$sport->current_week
                    ]);
            }
        }
    }
    public function cleanUp() {
        $lastChecked = Cache::get('cleanup');
        $leagues = League::count();
        if (!$lastChecked) {
            $lastChecked = 0;
            
        }
        if ($lastChecked <= $leagues) {
            Cache::put('cleanup', $lastChecked+100,6000);
        } else {
            Cache::put('cleanup', 0,6000);
        }

        $leagues = League::
            skip($lastChecked)
            ->take(100)
            ->get();
        
        foreach($leagues as $league) {
            print($league->id."<br />");
            $teams = LeagueUser::where('league_id',$league->id)->get();
            foreach ($teams as $team) {
            
                $rosterPlayerIds = [];
                $roster = RosterItem::where('league_id',$league->id)
                    ->where('team_id',$team->id)
                    ->get();

                foreach($roster as $player) {
                    if (in_array($player->player_id, $rosterPlayerIds)) {
                        // duplicate player
                        $delete = RosterItem::where('league_id',$league->id)
                            ->where('id',$player->id)
                            ->delete();
                    } else {
                        $rosterPlayerIds[] = $player->player_id;
                    }
                }
                $sport = Sport::where('id',8)->first();
                $lineup = Lineup::where('league_id',$league->id)
                    ->where('team_id',$team->id)
                    ->where('week',$sport->current_week)
                    ->get();

                foreach( $lineup as $item) {
                    if (!in_array($item->player_id, $rosterPlayerIds)) {
                        $delete = Lineup::where('id',$item->id)
                            ->where('league_id',$league->id)
                            ->where('week',$sport->current_week)
                            ->delete();
                    }
                }


            }



                
        }

    }
    public function processWaivers($day) {
        // get leagues that process this day
        $leagues = League::where('waiver_day',$day)
            ->where('waiver_status',0)
            ->get();

        foreach($leagues as $league) {
            // get teams in reverse standing order
            $teams = LeagueUser::where('league_id',$league->id)->get();
            //Waiver request for Badet, Jeff (WR) (drop Nelson, Philip (QB
            // for each team:
            foreach ($teams as $team) {
                // get their waivers
                $waivers = Waiver::where('team_id',$team->id)->get();
                
                foreach($waivers as $waiver) {
                    // add the player to pick up
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
            $update = League::where('id',$league->id)->update([
                'waiver_status'=>1
            ]);
            $lastUpdate = uniqid();
            Cache::put('leagueUpdate'.$league->id, $lastUpdate,600);
        }
    }
    public function testEmail() {
        $data = ['message' => 'This is a test!'];

        \Mail::to('brian@web3devs.com')->send(new TestEmail($data));
    }
    public function calculatePercent() {
        // get number of leagues
        $numOfLeagues = League::where('draft_status',2)
            ->count();

        // get all players
        $players = Player::get();
        foreach ($players as $player) {
            // count how many draft picks that guy has
            $allPicks = DraftPick::where('player_id',$player->id)->get();

            $update = Player::where('id',$player->id)
                ->update([
                    'percent'=>($allPicks->count() / $numOfLeagues)*100
                ]);
            $tally = 0;
            foreach ($allPicks as $pick) {
                $tally = $tally + $pick->pick_order;
            }
            if ($tally > 0 && $allPicks->count() > 0) {
                $update = Player::where('id',$player->id)
                    ->update([
                        'adp'=>$tally / $allPicks->count()
                    ]);
            } else {
                $update = Player::where('id',$player->id)
                    ->update([
                        'adp'=>100
                    ]);
            }
        }
    }
    public function xflPlayers() {
        $urls = [
            "Dallas" => "https://www.xfl.com/en-US/teams/dallas/renegades-articles/dallas-renegades-roster",
            "DC" => "https://www.xfl.com/en-US/teams/washington-dc/defenders-articles/dc-defenders-roster",
            "Houston" => "https://www.xfl.com/en-US/teams/houston/roughnecks-articles/houston-roughnecks-roster",
            "LA" => "https://www.xfl.com/en-US/teams/los-angeles/wildcats-articles/la-wildcats-roster",
            "New York" => "https://www.xfl.com/en-US/teams/new-york/guardians-articles/new-york-guardians-roster",
            "St Louis" => "https://www.xfl.com/en-US/teams/st-louis/battlehawks-articles/st-louis-battlehawks-roster",
            "Seattle" => "https://www.xfl.com/en-US/teams/seattle/dragons-articles/seattle-dragons-roster",
            "Tampa Bay" => "https://www.xfl.com/en-US/teams/tampa-bay/vipers-articles/tampa-bay-vipers-roster"
        ];

        foreach ($urls as $team=>$endpoint) {
            $html = file_get_html($endpoint);
            if ($team == "LA" || "Tampa Bay") {
                $selector = 'table tr';
            } else {
                $selector = 'table table tr';
            }
            foreach($html->find($selector) as $key=>$element) {
                if ($key > 0) {
                    $player_name = '';
                    $player_position = '';
                    $player_college = '';
                    
                    $college_cell_key = 5;
                    if ($team == "DC") $college_cell_key = 6;
                    if ($team == "Dallas") $college_cell_key = 6;
                    if ($team == "LA") $college_cell_key = 6;
                    if ($team == "Seattle") $college_cell_key = 5;
                    foreach($element->find('td') as $cellkey=>$cell) {
                        if ($cellkey == 1) {
                            if ($team == "Dallas" || $team == "DC" || $team == "LA") {
                                $player_first_name = str_replace("&nbsp;","",trim(strip_tags($cell->innertext)));
                            } else {
                                $player_name = str_replace("&nbsp;","",trim(strip_tags($cell->innertext)));
                            }
                        } else if ($cellkey == 2) {
                            if ($team == "Dallas" || $team == "DC" || $team == "LA") {
                                $player_last_name = str_replace("&nbsp;","",trim(strip_tags($cell->innertext)));
                                $player_name = $player_last_name.", ".$player_first_name;
                            
                            } else {
                                $player_position = trim(strip_tags($cell->innertext));
                            }
                        } else if (($team == "Dallas" || $team == "DC" || $team == "LA") && $cellkey == 3) {
                            $player_position = trim(strip_tags($cell->innertext));
                        } else if ($cellkey == $college_cell_key) {
                            $player_college = trim(strip_tags($cell->innertext));
                        }
                    }
                    if ($player_name && $player_position) {
                        $search = Player::where('name',$player_name)->first();
                        if (empty($search) && strpos($player_name, ",")) {
                            $name_parts = explode(",", $player_name);
                            $player_new_name = trim(strip_tags($name_parts[1]." ".$name_parts[0]));
                            $search = Player::where('name',$player_new_name)->first();
                        }
                        if (empty($search)) {
                            $new_player = new Player;
                            $new_player->name = $player_name;
                            $new_player->position = $player_position;
                            $new_player->extrainfo = $player_college;
                            $new_player->sport_id = 8;
                            $new_player->team = $team;
                            $new_player->save();
                        } else {
                            $updatePlayer = Player::where('name',$player_name)
                                ->update([
                                    "team"=>$team,
                                    "position"=>$player_position
                                ]);
                        }
                    }
                }
            }
        }
        // Deal with comma dupes
        $search = Player::get();
        foreach($search as $player) {
            if (!strpos($player->name, ",") && $player->position != "DEF") {
                $name_parts = explode(" ",$player->name);
                if (isset($name_parts[1])) {
                    $site_name = $name_parts[1].", ".$name_parts[0];
                    $delete = Player::where('name',$site_name)->delete();
                    $update = Player::where('name',$player->name)->update([
                        'name'=>$site_name
                    ]);
                }
            }
        }


        // Add defenses
        foreach ($urls as $team=>$endpoint) {
            $player_name = $team;
            $search = Player::where('name',$player_name)
                ->where('position','DEF')
                ->first();
            if (empty($search)) {
                $new_player = new Player;
                $new_player->name = $player_name;
                $new_player->position = 'DEF';
                $new_player->extrainfo = '';
                $new_player->sport_id = 8;
                $new_player->team = $team;
                $new_player->save();
            }
        }

        // Add team Qbs
        foreach ($urls as $team=>$endpoint) {
            $player_name = $team;
            $search = Player::where('name',$player_name)
                ->where('position','QB')
                ->first();
            if (empty($search)) {
                $new_player = new Player;
                $new_player->name = $player_name;
                $new_player->position = 'QB';
                $new_player->extrainfo = '';
                $new_player->sport_id = 8;
                $new_player->team = $team;
                $new_player->save();
            }
        }

        // Add team kickers
        foreach ($urls as $team=>$endpoint) {
            $player_name = $team;
            $search = Player::where('name',$player_name)
                ->where('position','K')    
                ->first();
            if (empty($search)) {
                $new_player = new Player;
                $new_player->name = $player_name;
                $new_player->position = 'K';
                $new_player->extrainfo = '';
                $new_player->sport_id = 8;
                $new_player->team = $team;
                $new_player->save();
            }
        }


    die();  
    }
}
