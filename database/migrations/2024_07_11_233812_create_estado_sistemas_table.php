<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEstadoSistemasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('estado_sistemas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('s0_id');
            $table->uuid('s1_id');
            $table->uuid('s2_id');
            $table->uuid('s3_id');
            $table->uuid('s4_id');
            $table->uuid('s5_id');
            $table->timestamps();

            $table->foreign('s0_id')->references('id')->on('s0')->onDelete('cascade');
            $table->foreign('s1_id')->references('id')->on('s1')->onDelete('cascade');
            $table->foreign('s2_id')->references('id')->on('s2')->onDelete('cascade');
            $table->foreign('s3_id')->references('id')->on('s3')->onDelete('cascade');
            $table->foreign('s4_id')->references('id')->on('s4')->onDelete('cascade');
            $table->foreign('s5_id')->references('id')->on('s5')->onDelete('cascade');
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
        Schema::dropIfExists('estado_sistemas');
        Schema::enableForeignKeyConstraints();
    }
}
