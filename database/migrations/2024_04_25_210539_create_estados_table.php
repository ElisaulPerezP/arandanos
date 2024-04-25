<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEstadosTable extends Migration
{
    public function up()
    {
        Schema::create('estados', function (Blueprint $table) {
            $table->id();
            for ($i = 1; $i <= 12; $i++) {
                $table->boolean('solenoide_' . $i)->default(false);
            }
            $table->boolean('bomba_1')->default(false);
            $table->boolean('bomba_2')->default(false);
            $table->boolean('bomba_fertilizante')->default(false);
            $table->unsignedBigInteger('id_tabla_flujos');
            $table->timestamps();

            $table->foreign('id_tabla_flujos')->references('id')->on('flujos')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('estados');
    }
}
