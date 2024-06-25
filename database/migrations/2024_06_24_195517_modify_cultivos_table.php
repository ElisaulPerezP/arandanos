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
        Schema::table('cultivos', function (Blueprint $table) {
            $table->string('coordenadas')->nullable()->change();
            $table->foreignId('estado_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cultivos', function (Blueprint $table) {
            $table->string('coordenadas')->nullable(false)->change();
            $table->foreignId('estado_id')->nullable(false)->change();
        });
    }
};
