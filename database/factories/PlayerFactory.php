<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Player;
use Faker\Generator as Faker;

$factory->defineAs(Player::class, 'player', function ($faker) {
    return [
      'sport_id' => 8, # XFL
      'name' => $faker->name,
      'position' => $faker->state,
      'extrainfo' => 1,
      'team' => $faker->word,
      'percent' => 100,
      'adp' => 1
    ];
});
