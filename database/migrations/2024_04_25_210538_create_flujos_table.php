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
            $table->enum('estado', ['acentado', 'reportado']); // Campo estado con valores específicos
            $table->integer('valor'); // Campo valor como entero
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('flujos');
    }
}
