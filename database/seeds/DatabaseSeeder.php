<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Eloquent::unguard();
        $memory_limit = ini_get('memory_limit');
        ini_set('memory_limit', '-1');
        DB::unprepared(file_get_contents(
          'database/seeds/dev-snapshot.sql'));
        ini_set('memory_limit', $memory_limit);
        $this->command->info('Example database loaded!');
    }
}
