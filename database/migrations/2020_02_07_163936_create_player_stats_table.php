<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlayerStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('player_stats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->integer('week');
            $table->integer('player_id');
            $table->decimal('rule1',8,2)->default(0);
            $table->decimal('rule2',8,2)->default(0);
            $table->decimal('rule3',8,2)->default(0);
            $table->decimal('rule4',8,2)->default(0);
            $table->decimal('rule5',8,2)->default(0);
            $table->decimal('rule6',8,2)->default(0);
            $table->decimal('rule7',8,2)->default(0);
            $table->decimal('rule8',8,2)->default(0);
            $table->decimal('rule9',8,2)->default(0);
            $table->decimal('rule10',8,2)->default(0);
            $table->decimal('rule11',8,2)->default(0);
            $table->decimal('rule12',8,2)->default(0);
            $table->decimal('rule13',8,2)->default(0);
            $table->decimal('rule14',8,2)->default(0);
            $table->decimal('rule15',8,2)->default(0);
            $table->decimal('rule16',8,2)->default(0);
            $table->decimal('rule17',8,2)->default(0);
            $table->decimal('rule18',8,2)->default(0);
            $table->decimal('rule19',8,2)->default(0);
            $table->decimal('rule20',8,2)->default(0);
            $table->decimal('rule21',8,2)->default(0);
            $table->decimal('rule22',8,2)->default(0);
            $table->decimal('rule23',8,2)->default(0);
            $table->decimal('rule24',8,2)->default(0);
            $table->decimal('rule25',8,2)->default(0);
            $table->decimal('rule26',8,2)->default(0);
            $table->decimal('rule27',8,2)->default(0);
            $table->decimal('rule28',8,2)->default(0);
            $table->decimal('rule29',8,2)->default(0);
            $table->decimal('rule30',8,2)->default(0);
            $table->decimal('rule31',8,2)->default(0);
            $table->decimal('rule32',8,2)->default(0);
            $table->decimal('rule33',8,2)->default(0);
            $table->decimal('rule34',8,2)->default(0);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('player_stats');
    }
}
