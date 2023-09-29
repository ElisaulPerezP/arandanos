<?php

use App\Http\Controllers\PortsController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

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
});


Route::middleware('auth:sanctum')->get('/on', [PortsController::class, 'onLed'])->name('led.on');
Route::middleware('auth:sanctum')->get('/off', [PortsController::class, 'offLed'])->name('led.off');
Route::middleware('auth:sanctum')->get('/pump/1/on', [PortsController::class, 'offLed'])->name('Button.on_pump_1');
Route::middleware('auth:sanctum')->get('/pump/2/on', [PortsController::class, 'offLed'])->name('Button.on_pump_2');
Route::middleware('auth:sanctum')->get('/pump/1/off', [PortsController::class, 'offLed'])->name('Button.off_pump_1');
Route::middleware('auth:sanctum')->get('/pump/2/off', [PortsController::class, 'offLed'])->name('Button.off_pump_2');
Route::middleware('auth:sanctum')->get('/stop', [PortsController::class, 'offLed'])->name('Button.STOP');

Route::middleware('auth:sanctum')->get('/solenoid/1-2/on', [PortsController::class, 'offLed'])->name('Button.on_1_2');
Route::middleware('auth:sanctum')->get('/solenoid/3-4/on', [PortsController::class, 'offLed'])->name('Button.on_3_4');
Route::middleware('auth:sanctum')->get('/solenoid/5-6/on', [PortsController::class, 'offLed'])->name('Button.on_5_6');
Route::middleware('auth:sanctum')->get('/solenoid/7-8/on', [PortsController::class, 'offLed'])->name('Button.on_7_8');
Route::middleware('auth:sanctum')->get('/solenoid/9-10/on', [PortsController::class, 'offLed'])->name('Button.on_9_10');

Route::middleware('auth:sanctum')->get('/solenoid/1-2/off',  [PortsController::class, 'offLed'])->name('Button.off_1_2');
Route::middleware('auth:sanctum')->get('/solenoid/3-4/off',  [PortsController::class, 'offLed'])->name('Button.off_3_4');
Route::middleware('auth:sanctum')->get('/solenoid/5-6/off',  [PortsController::class, 'offLed'])->name('Button.off_5_6');
Route::middleware('auth:sanctum')->get('/solenoid/7-8/off',  [PortsController::class, 'offLed'])->name('Button.off_7_8');
Route::middleware('auth:sanctum')->get('/solenoid/9-10/off', [PortsController::class, 'offLed'])->name('Button.off_9_10');

require __DIR__.'/auth.php';
