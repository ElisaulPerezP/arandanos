<?php

use App\Http\Controllers\PortsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EstadoController;
use App\Http\Controllers\RiegoController;
use App\Http\Controllers\EstadisticasController;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CultivoLoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/registro', [CultivoLoginController::class, 'showRegistrationForm'])->name('registro');
    Route::post('/cultivo/login', [CultivoLoginController::class, 'login'])->name('cultivo.login');

    Route::get('/riego', [RiegoController::class, 'adminRiegos'])->name('riego');
    Route::get('/estadisticas', [EstadisticasController::class, 'verEstadisticas'])->name('estadisticas');
});

Route::middleware('auth')->group(function () {
    // Rutas existentes...

    Route::get('/update/registro', [CultivoLoginController::class, 'showUpdateForm'])->name('update.registro');
    Route::post('/update/cultivo', [CultivoLoginController::class, 'updateCultivo'])->name('cultivo.update');
});

Route::resource('estados', EstadoController::class);

Route::post('/cambiar-estado', [EstadoController::class, 'cambiarEstado']);

require __DIR__.'/auth.php';
