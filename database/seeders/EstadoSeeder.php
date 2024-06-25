<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Estado;

class EstadoSeeder extends Seeder
{
    public function run(): void
    {
        Estado::create([
            'nombre' => 'Activo',
            'descripcion' => 'El cultivo se encuentra en estado operativo',
        ]);

        Estado::create([
            'nombre' => 'Inactivo',
            'descripcion' => 'El cultivo se encuentra detenido por orden del productor, de permanecer asi, se corre riezgo de muerte del cultivo',
        ]);

        Estado::create([
            'nombre' => 'Emergencia_Fuga',
            'descripcion' => 'El sistema ha detectado una fuga critica de agua. Revisar el registro de mensajes para mas informacion',
        ]);
        Estado::create([
            'nombre' => 'Emergencia_Bomba',
            'descripcion' => 'La bomba principal de agua ha dejado de funcionar. Revisar el registro de mensajes para mas informacion',
        ]);

        Estado::create([
            'nombre' => 'Emergencia_Valvula',
            'descripcion' => 'Una electrovalvula del sistema esta funcionando anormalmente o no esta funcionando. Revisar el registro de mensajes para mas informacion',
        ]);
        Estado::create([
            'nombre' => 'Emergencia_Agua',
            'descripcion' => 'El nivel de agua en los tanques es insuficiente para suministrar agua a las bombas',
        ]);

        Estado::create([
            'nombre' => 'Ejecutando_Tarea',
            'descripcion' => 'El sistema se encuentra ejecutnado una tarea programada. por favor espere a terminar el proceso, o detenga el sistema manualmente',
        ]);
    }
}
