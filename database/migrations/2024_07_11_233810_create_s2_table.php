<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateS2Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('s2', function (Blueprint $table) {
            $table->id();
            $table->boolean('estado');
            $table->foreignId('comando_id')->constrained('comando_hardware');
            $table->boolean('valvula1');
            $table->boolean('valvula2');
            $table->boolean('valvula3');
            $table->boolean('valvula4');
            $table->boolean('valvula5');
            $table->boolean('valvula6');
            $table->boolean('valvula7');
            $table->boolean('valvula8');
            $table->boolean('valvula9');
            $table->boolean('valvula10');
            $table->boolean('valvula11');
            $table->boolean('valvula12');
            $table->boolean('valvula13');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('s2');
    }
}
