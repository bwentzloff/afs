<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\DraftQueue;
use Faker\Generator as Faker;

$factory->defineAs(DraftQueue::class, 'draft_queue', function ($faker) {
    return [
      'leagueuser_id' => 1,
      'player_id' => 1,
      'queue_order' => 1
    ];
});
