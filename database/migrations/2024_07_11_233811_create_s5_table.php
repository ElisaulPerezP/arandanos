<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateS5Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('s5', function (Blueprint $table) {
            $table->id();
            $table->boolean('estado');
            $table->foreignId('comando_id')->constrained('comando_hardware');
            $table->integer('flux1');
            $table->integer('flux2');
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
        Schema::dropIfExists('s5');
    }
}
