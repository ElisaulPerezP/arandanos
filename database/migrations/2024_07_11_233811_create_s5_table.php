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
            $table->uuid('id')->primary();
            $table->boolean('estado');
            $table->uuid('comando_id')->nullable();
            $table->integer('flux1');
            $table->integer('flux2');
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
        Schema::dropIfExists('s5');
        Schema::enableForeignKeyConstraints();
    }
}
