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
            $table->string('estado')->nullable();
            $table->foreignId('comando_id')->nullable()->constrained('comando_hardware');
            $table->string('valvula1')->nullable();
            $table->string('valvula2')->nullable();
            $table->string('valvula3')->nullable();
            $table->string('valvula4')->nullable();
            $table->string('valvula5')->nullable();
            $table->string('valvula6')->nullable();
            $table->string('valvula7')->nullable();
            $table->string('valvula8')->nullable();
            $table->string('valvula9')->nullable();
            $table->string('valvula10')->nullable();
            $table->string('valvula11')->nullable();
            $table->string('valvula12')->nullable();
            $table->string('valvula13')->nullable();
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
