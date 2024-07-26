<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateS4Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('s4', function (Blueprint $table) {
            $table->id();
            $table->boolean('estado');
            $table->foreignId('comando_id')->nullable()->constrained('comando_hardware');
            $table->boolean('pump3');
            $table->boolean('pump4');
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
        Schema::dropIfExists('s4');
    }
}
