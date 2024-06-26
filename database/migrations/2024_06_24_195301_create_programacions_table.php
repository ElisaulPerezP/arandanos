<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('programacions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hora_unix'); // Hora Unix para la programación
            $table->foreignId('cultivo_id')->constrained()->onDelete('cascade'); // Asociación con Cultivo
            $table->foreignId('comando_id')->constrained()->onDelete('cascade'); // Asociación con Comando
            $table->string('estado')->default('por enviar');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programacions');
    }
};
