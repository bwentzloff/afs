<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTransactionLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('league_transactions', function (Blueprint $table) {
            $table->integer('type')->default(0);
            $table->text('team1_selected')->nullable();
            $table->text('team2_selected')->nullable();
            $table->integer('team1_id')->nullable();
            $table->integer('team2_id')->nullable();
            $table->integer('player_id')->nullable();
            $table->integer('drop_player_id')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('league_transactions', function (Blueprint $table) {
            //
        });
    }
}
