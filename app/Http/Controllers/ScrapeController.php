<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\League;
use App\Models\User;
use App\Models\LeagueUser;
use App\Models\Player;

include(app_path() . '/../vendor/simple-html-dom/simple_html_dom.php');

class ScrapeController extends Controller
{
    public function xflPlayers() {
        $urls = [
            "Dallas" => "https://www.xfl.com/en-US/articles/dallas-renegades-roster",
            "DC" => "https://www.xfl.com/en-US/teams/washington-dc/defenders-articles/dc-defenders-roster",
            "Houston" => "https://www.xfl.com/en-US/articles/houston-roughnecks-roster",
            "LA" => "https://www.xfl.com/en-US/articles/la-wildcats-roster",
            "New York" => "https://www.xfl.com/en-US/articles/new-york-guardians-roster",
            "St Louis" => "https://www.xfl.com/en-US/articles/st-louis-battlehawks-roster",
            "Seattle" => "https://www.xfl.com/en-US/articles/seattle-dragons-roster",
            "Tampa Bay" => "https://www.xfl.com/en-US/articles/tampa-bay-vipers-roster"
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
                    
                    foreach($element->find('td') as $cellkey=>$cell) {
                        if ($cellkey == 1) {
                            $player_name = str_replace("&nbsp;","",trim(strip_tags($cell->innertext)));
                        } else if ($cellkey == 2) {
                            $player_position = trim(strip_tags($cell->innertext));
                        } else if ($cellkey == 5) {
                            $player_college = trim(strip_tags($cell->innertext));
                        }
                    }
                    if ($player_name && $player_position) {
                        $search = Player::where('name',$player_name)->first();
                        if (empty($search)) {
                            $new_player = new Player;
                            $new_player->name = $player_name;
                            $new_player->position = $player_position;
                            $new_player->extrainfo = $player_college;
                            $new_player->sport_id = 8;
                            $new_player->team = $team;
                            $new_player->save();
                        }
                    }
                }
            }
        }
        // Add defenses
        foreach ($urls as $team=>$endpoint) {
            $player_name = $team;
            $search = Player::where('name',$player_name)->first();
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


    die();  
    }
}
