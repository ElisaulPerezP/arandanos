<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCultivosAndRelatedTables extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cultivos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('coordenadas')->nullable(); // Se establece como nullable desde el inicio
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('estado_id')->nullable()->constrained('estados')->onDelete('cascade'); // Se establece como nullable desde el inicio
            $table->foreignId('comando_id')->nullable()->constrained('comandos')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('cultivo_comando', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cultivo_id')->constrained('cultivos')->onDelete('cascade');
            $table->foreignId('comando_id')->constrained('comandos')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('estado_cultivo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cultivo_id')->constrained('cultivos')->onDelete('cascade');
            $table->foreignId('estado_id')->constrained('estados')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estado_cultivo');
        Schema::dropIfExists('cultivo_comando');
        Schema::table('cultivos', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['comando_id']);
            $table->dropForeign(['estado_id']);
        });
        Schema::dropIfExists('cultivos');
    }
}
