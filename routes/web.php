<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EncuestasController;
use App\Http\Controllers\SuscriptoresController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;


Route::get('/', function () {
    return Auth::check()
        ? to_route('dashboard')
        : to_route('login');
});


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::resource('users', UserController::class);
});

Route::middleware(['auth'])->group(function () {
    Route::get('/encuestas', [EncuestasController::class, 'index'])->name('encuestas.index');
    Route::post('/encuestas/procesar', [EncuestasController::class, 'procesar'])->name('encuestas.procesar');
});

Route::get('/suscriptores', [SuscriptoresController::class, 'index'])
    ->middleware('auth')
    ->name('suscriptores.index');

// Endpoint AJAX para DataTables
Route::get('/suscriptores/data', [SuscriptoresController::class, 'data'])->name('suscriptores.data');

require __DIR__.'/auth.php';
