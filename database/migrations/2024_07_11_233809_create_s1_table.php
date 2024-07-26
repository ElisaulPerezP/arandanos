<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateS1Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('s1', function (Blueprint $table) {
            $table->id();
            $table->boolean('estado');
            $table->foreignId('comando_id')->nullable()->constrained('comando_hardware');
            $table->boolean('sensor1');
            $table->boolean('sensor2');
            $table->boolean('valvula14');
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
        Schema::dropIfExists('s1');
    }
}
