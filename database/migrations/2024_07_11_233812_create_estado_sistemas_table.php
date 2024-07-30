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
            $table->id();
            $table->foreignId('s0_id')->constrained('s0');
            $table->foreignId('s1_id')->constrained('s1');
            $table->foreignId('s2_id')->constrained('s2');
            $table->foreignId('s3_id')->constrained('s3');
            $table->foreignId('s4_id')->constrained('s4');
            $table->foreignId('s5_id')->constrained('s5');
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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('estado_sistemas');
        Schema::enableForeignKeyConstraints();
    }
}
