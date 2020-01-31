<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMoreRulesAndChangeToDecimal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leagues', function (Blueprint $table) {
            $table->decimal('rule27',8,2)->default(10);
            $table->decimal('rule28',8,2)->default(8);
            $table->decimal('rule29',8,2)->default(6);
            $table->decimal('rule30',8,2)->default(2);
            $table->decimal('rule31',8,2)->default(1);
            $table->decimal('rule32',8,2)->default(0);
            $table->decimal('rule33',8,2)->default(-2);
            $table->decimal('rule34',8,2)->default(-4);

            $table->decimal('rule1',8,2)->change();
            $table->decimal('rule2',8,2)->change();
            $table->decimal('rule3',8,2)->change();
            $table->decimal('rule4',8,2)->change();
            $table->decimal('rule5',8,2)->change();
            $table->decimal('rule6',8,2)->change();
            $table->decimal('rule7',8,2)->change();
            $table->decimal('rule8',8,2)->change();
            $table->decimal('rule9',8,2)->change();
            $table->decimal('rule10',8,2)->change();
            $table->decimal('rule11',8,2)->change();
            $table->decimal('rule12',8,2)->change();
            $table->decimal('rule13',8,2)->change();
            $table->decimal('rule14',8,2)->change();
            $table->decimal('rule15',8,2)->change();
            $table->decimal('rule16',8,2)->change();
            $table->decimal('rule17',8,2)->change();
            $table->decimal('rule18',8,2)->change();
            $table->decimal('rule19',8,2)->change();
            $table->decimal('rule20',8,2)->change();
            $table->decimal('rule21',8,2)->change();
            $table->decimal('rule22',8,2)->change();
            $table->decimal('rule23',8,2)->change();
            $table->decimal('rule24',8,2)->change();
            $table->decimal('rule25',8,2)->change();
            $table->decimal('rule26',8,2)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('players', function (Blueprint $table) {
            //
        });
    }
}
