<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFlujosTable extends Migration
{
    public function up()
    {
        Schema::create('flujos', function (Blueprint $table) {
            $table->id();
            // Añade otros campos necesarios aquí
            $table->string('nombre'); // Ejemplo de campo adicional
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('flujos');
    }
}
