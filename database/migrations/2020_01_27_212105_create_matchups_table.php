<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMatchupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('matchups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->integer('home_id');
            $table->integer('away_id');
            $table->decimal('home_score',8,2)->default(0);
            $table->decimal('away_score',8,2)->default(0);
            $table->integer('week');
            $table->integer('league_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('matchups');
    }
}
