<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateS3Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('s3', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->boolean('estado');
            $table->uuid('comando_id')->nullable();
            $table->boolean('pump1');
            $table->boolean('pump2');
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
        Schema::dropIfExists('s3');
        Schema::enableForeignKeyConstraints();
    }
}
