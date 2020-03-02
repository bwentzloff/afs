<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\LeagueUser;
use Faker\Generator as Faker;

$factory->defineAs(LeagueUser::class, 'league_user', function ($faker) {
    return [
      'name' => $faker->name,
      'user_id' => 1,
      'league_id' => 1,
      'draft_order' => 0,
    ];
});
