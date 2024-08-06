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
        DB::statement('CREATE INDEX comando_hardware_comando_index ON comando_hardware (comando(255))');
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
