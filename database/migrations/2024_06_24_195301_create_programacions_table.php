<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProgramacionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('programacions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('hora_unix');
            $table->foreignId('cultivo_id')->constrained()->onDelete('cascade');
            $table->foreignId('comando_id')->constrained()->onDelete('cascade');
            $table->string('estado', 50)->default('por enviar');
            $table->timestamps();

            // Agregar índices
            $table->index('hora_unix');
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programacions');
    }
}
