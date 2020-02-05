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
use Illuminate\Support\Facades\Cache;

use App\Mail\TestEmail;

include(app_path() . '/../vendor/simple-html-dom/simple_html_dom.php');

class ScrapeController extends Controller
{
    public function processWaivers($day) {
        // get leagues that process this day
        $leagues = League::where('waiver_day',$day)->get();

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
