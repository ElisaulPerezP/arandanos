<?php

use Illuminate\Support\Facades\Route;

return array(
    'scriptsDisponibles' => 'riegoStandard.py, stopManual.py, flujo.py, impulsores.py, inyectores.py, tanques.py, selector.py',
    'scriptsEjecutandose' => '',
    'scritpsDeBase' => implode(', ', [
        'stopManual.py ' . base_path('pythonScripts/input_pins_file_stop.txt') . ' ' . route('api.stop'),
        'flujo.py ' . base_path('pythonScripts/input_pins_file_flujo.txt') . ' ' . route('api.flujo.conteo') . ' ' . route('api.flujo.apagado'),
        'impulsores.py ' . base_path('pythonScripts/output_pins_file_impulsores.txt') . ' ' . base_path('pythonScripts/output_neg_pins_file_impulsores.txt') . ' ' . route('api.impulsores') . ' ' . route('api.impulsores.estado') . ' ' . route('api.impulsores.apagado'),
        'inyectores.py ' . base_path('pythonScripts/output_pins_file_inyectores.txt') . ' ' . base_path('pythonScripts/output_neg_pins_file_inyectores.txt') . ' ' . route('api.inyectores') . ' ' . route('api.inyectores.estado') . ' ' . route('api.inyectores.apagado'),
        'tanques.py ' . base_path('pythonScripts/input_pins_file_tanques.txt') . ' ' . base_path('pythonScripts/output_pins_file_tanques.txt') . ' ' . base_path('pythonScripts/output_neg_pins_file_tanques.txt') . ' ' . route('api.tanques') . ' ' . route('api.tanques.estado') . ' ' . route('api.tanques.apagado'),
        'selector.py ' . base_path('pythonScripts/output_pins_file_selector.txt') . ' ' . base_path('pythonScripts/output_neg_pins_file_selector.txt') . ' ' . route('api.selector') . ' ' . route('api.selector.estado') . ' ' . route('api.selector.apagado')
    ]),
);
