<?php

namespace Tests\Feature\Http\Controllers;

use App\User;
use App\Models\Player;
use App\Models\League;
use App\Models\LeagueUser;
use App\Models\DraftQueue;
use App\Models\DraftPick;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class LeagueControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testUpdateDraftStatus()
    {
        for ($i=0; $i<10; $i++) {
            factory(Player::class, 'player')->create();
        }

        $users = array(
            factory(User::class, 'user')->create(),
            factory(User::class, 'user')->create(),
            factory(User::class, 'user')->create(),
            factory(User::class, 'user')->create(),
        );

        $league = factory(League::class, 'league')->create();

        foreach ($users as $index => $user) {
            factory(LeagueUser::class, 'league_user')
                ->create(['draft_order' => $index]);
        }
        $this->assertDatabaseHas('leagues',
            ['draft_status' => 0]);

        // Assumes LeagueController#createDraftPicks has run
        //    called from LeagueController#setDraftOrder
        //    at then end of LeagueController#create

        // this endpoint calls "createDraftPicks" as well
        // and is currently NOT authenticated
        $this->json('POST', '/api/v1/league/updateRoster',
            [
                'leagueId' => $league->id,
                'qbs' => 1,
                'rbs' => 1,
                'wrs' => 1,
                'tes' => 1,
                'flex' => 1,
                'superflex' => 0,
                'ks' => 1,
                'def' => 1,
                'bench' => 2,
                'teamQbs' => 0,
                'teamKs' => 0
            ]
        );
        $this->get('/api/v1/league/updateDraftStatus');
        $this->assertDatabaseHas('leagues',
            ['draft_status' => 1]);

        // seems like the auto-draft mechanism does not work
        // unless there exists a DraftQueue record in the DB
        $count = DB::select('
          select COUNT(1) as count from draft_picks
          where player_id IS NOT NULL')[0]->count;
        $this->assertEquals(0, $count);

        // create a draft queue record and try again
        factory(DraftQueue::class, 'draft_queue')->create(
            [
              'leagueuser_id' => LeagueUser::first()->id,
              'player_id' => Player::first()->id
            ]
        );
        $this->get('/api/v1/league/updateDraftStatus');

        // now the auto-pick works
        $count = DB::select('
          select COUNT(1) as count from draft_picks
          where player_id IS NOT NULL')[0]->count;
        $this->assertEquals(1, $count);

        // endpoint must be re-requested following each pick
        // for an auto-draft to occur on the next pick
    }
}
