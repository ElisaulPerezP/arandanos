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
            $table->uuid('id')->primary();
            $table->boolean('estado');
            $table->uuid('comando_id')->nullable();
            $table->boolean('pump3');
            $table->boolean('pump4');
            $table->timestamps();

            $table->foreign('comando_id')->references('id')->on('comando_hardware')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('s4');
        Schema::enableForeignKeyConstraints();
    }
}
