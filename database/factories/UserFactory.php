<?php

use App\User;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->defineAs(User::class, 'user', function ($faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'email_verified_at' => now(),
        'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        'remember_token' => Str::random(10),
        // -1 for super admin, 1 for admin, 0 for user
        'role' => 0,
    ];
});

$factory->defineAs(User::class, 'user_admin', function ($faker) use ($factory) {
    return array_merge($factory->raw(User::class), ['role' => 1]);
});

$factory->defineAs(User::class, 'user_super_admin', function ($faker) use ($factory) {
    $user = ;
    return array_merge($factory->raw(User::class), ['role' => -1]);
});
