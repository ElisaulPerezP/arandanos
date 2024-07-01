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
            $table->string('coordenadas')->nullable();
            $table->foreignId('estado_id')->nullable()->constrained('estados')->nullOnDelete();
            $table->foreignId('comando_id')->nullable()->constrained('comandos')->nullOnDelete();
            $table->string('api_token', 80)->unique()->nullable()->default(null);
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
        Schema::dropIfExists('cultivos');
    }
}
