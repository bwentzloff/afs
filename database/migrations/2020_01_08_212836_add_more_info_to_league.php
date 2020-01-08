<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMoreInfoToLeague extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leagues', function (Blueprint $table) {
            $table->integer('qbs')->default(1);
            $table->integer('rbs')->default(2);
            $table->integer('wrs')->default(2);
            $table->integer('tes')->default(1);
            $table->integer('flex')->default(1);
            $table->integer('ks')->default(1);
            $table->integer('def')->default(1);
            $table->integer('rule1')->default(6);
            $table->integer('rule2')->default(6);
            $table->integer('rule3')->default(6);
            $table->integer('rule4')->default(6);
            $table->integer('rule5')->default(4);
            $table->integer('rule6')->default(1);
            $table->integer('rule7')->default(2);
            $table->integer('rule8')->default(3);
            $table->integer('rule9')->default(1);
            $table->integer('rule10')->default(2);
            $table->integer('rule11')->default(3);
            $table->integer('rule12')->default(1);
            $table->integer('rule13')->default(1);
            $table->integer('rule14')->default(1);
            $table->integer('rule15')->default(-2);
            $table->integer('rule16')->default(-2);
            $table->integer('rule17')->default(5);
            $table->integer('rule18')->default(4);
            $table->integer('rule19')->default(3);
            $table->integer('rule20')->default(3);
            $table->integer('rule21')->default(2);
            $table->integer('rule22')->default(2);
            $table->integer('rule23')->default(2);
            $table->integer('rule24')->default(2);
            $table->integer('rule25')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leagues', function (Blueprint $table) {
            //
        });
    }
}
