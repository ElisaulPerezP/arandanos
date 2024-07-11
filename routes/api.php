<?php

use App\Http\Controllers\PortsController;
use App\Http\Controllers\EstadoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('estados', [EstadoController::class, 'store']);

//stop_rul
Route::post('/stop', [ApiController::class, 'reportStop'])->name('api.stop');

// Ruta para obtener comandos de activacion o desactivacion llenado de tanques
Route::get('/tanques', [ApiController::class, 'getTanquesCommand'])->name('api.tanques');

// Ruta para reportar el estado de llenado de los tanquez, o errores
Route::post('/tanques/estado', [ApiController::class, 'reportTanquesState'])->name('api.tanques.estado');

// Ruta para reportar el apagado de sistema de llenado de tanques
Route::post('/tanques/apagado', [ApiController::class, 'reportTanquesShutdown'])->name('api.tanques.apagado');

// Ruta para obtener comandos para el grupo principal de electrovalvulas del sistema
Route::get('/selector', [ApiController::class, 'getSelectorCommand'])->name('api.selector');

// Ruta para reportar estado del grupo de electrovalvulas del sistema
Route::post('/selector/estado', [ApiController::class, 'reportState'])->name('api.selector.estado');

// Ruta para reportar apagado del sistema de grupo de electrovalvulas
Route::post('/selector/apagado', [ApiController::class, 'reportShutdown'])->name('api.selector.apagado');

// Ruta para obtener comandos de accinamiento de bombas principales
Route::get('/impulsores', [ApiController::class, 'getImpulsoresCommand'])->name('api.impulsores');

// Ruta para reportar estado del sistema de control de bombas principales
Route::post('/impulsores/estado', [ApiController::class, 'reportImpulsoresState'])->name('api.impulsores.estado');

// Ruta para reportar apagado del sistema de manejo de bombas princiapales
Route::post('/impulsores/apagado', [ApiController::class, 'reportImpulsoresShutdown'])->name('api.impulsores.apagado');

// Ruta para obtener comandos de manejo de sistema de inyeccion de fertilizante
Route::get('/inyectores', [ApiController::class, 'getInyectoresCommand'])->name('api.inyectores');

// Ruta para reportar estado del sistema de inyeccion de fertilizante
Route::post('/inyectores/estado', [ApiController::class, 'reportInyectoresState'])->name('api.inyectores.estado');

// Ruta para reportar apagado del sistema de inyeccion de fertilizantes
Route::post('/inyectores/apagado', [ApiController::class, 'reportInyectoresShutdown'])->name('api.inyectores.apagado');

// Ruta para reportar el conteo de los medidores de flujo del sistema
Route::post('/flujo/conteo', [ApiController::class, 'reportFlujoConteo'])->name('api.flujo.conteo');

// Ruta para reportar apagado del sistema de medida de flujo del sistema
Route::post('/flujo/apagado', [ApiController::class, 'reportFlujoApagado'])->name('api.flujo.apagado');