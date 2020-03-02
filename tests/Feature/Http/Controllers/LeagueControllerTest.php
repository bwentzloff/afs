<?php

namespace Tests\Feature\Http\Controllers;

use App\User;
use App\Models\League;
use App\Models\LeagueUser;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LeagueControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testUpdateDraftStatus()
    {
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

        // this endpoint calls "createDraftPicks"
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
        $response = $this->get('/api/v1/league/updateDraftStatus');
        $this->assertDatabaseHas('leagues',
            ['draft_status' => 1]);
    }
}
