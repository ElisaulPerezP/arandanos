<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateComandoHardwareTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comando_hardware', function (Blueprint $table) {
            $table->id();
            $table->string('sistema');
            $table->text('comando');
            $table->timestamps();
        });

        // Crear el índice dependiendo del tipo de base de datos
        if (DB::getDriverName() === 'mysql') {
            // Crear el índice en la columna comando con longitud específica en MySQL
            DB::statement('CREATE INDEX comando_hardware_comando_index ON comando_hardware (comando(255))');
        } elseif (DB::getDriverName() === 'sqlite') {
            // Crear el índice en la columna comando sin longitud específica en SQLite
            Schema::table('comando_hardware', function (Blueprint $table) {
                $table->index('comando');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comando_hardware');
    }
}
