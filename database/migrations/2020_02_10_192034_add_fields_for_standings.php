<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsForStandings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('league_users', function (Blueprint $table) {
            $table->integer('wins')->default(0);
            $table->integer('losses')->default(0);
            $table->integer('ties')->default(0);
            $table->integer('pf')->default(0);
            $table->integer('pa')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('league_users', function (Blueprint $table) {
            //
        });
    }
}
