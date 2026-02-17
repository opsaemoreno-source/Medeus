<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EncuestasController;
use App\Http\Controllers\SuscriptoresController;
use App\Http\Controllers\EstadisticasController;
use App\Http\Controllers\ComprasController;
use App\Http\Controllers\CiudadesNormalizacionController;
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

Route::get('/suscriptores', [SuscriptoresController::class, 'index'])
    ->middleware('auth')
    ->name('suscriptores.index');

// Endpoint AJAX para DataTables
Route::get('/suscriptores/data', [SuscriptoresController::class, 'data'])->name('suscriptores.data');
// Obtener estadística por día (rango de fechas opcional)
Route::get('/suscriptores/estadistica', [SuscriptoresController::class, 'estadisticaPorDia'])
    ->middleware('auth')
    ->name('suscriptores.estadistica');
// Exportar suscriptores con filtros aplicados
Route::get('/suscriptores/exportar', [SuscriptoresController::class, 'exportar'])
    ->middleware('auth')
    ->name('suscriptores.exportar');
Route::get('/suscriptores/catalogos', [SuscriptoresController::class, 'getCatalogos'])
    ->middleware('auth')
    ->name('suscriptores.catalogos');


Route::middleware(['auth'])->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('password.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('users', UserController::class);

    Route::get('/encuestas', [EncuestasController::class, 'index'])->name('encuestas.index');
    Route::post('/encuestas/procesar', [EncuestasController::class, 'procesar'])->name('encuestas.procesar');

    Route::get('/estadisticas', function () {
        return view('estadisticas.index');
    })->name('estadisticas.index');

    // HUB principal
    Route::get('/estadisticas', [EstadisticasController::class, 'index'])
        ->name('estadisticas.index');

    // Cargar parciales vía AJAX
    Route::get('/estadisticas/encuestas', [EstadisticasController::class, 'encuestas']);
    Route::get('/estadisticas/suscriptores', [EstadisticasController::class, 'suscriptores']);
    Route::get('/estadisticas/avanzado', [EstadisticasController::class, 'avanzado']);
    Route::get('/estadisticas/compras', [EstadisticasController::class, 'compras']);

    Route::get('/compras', [ComprasController::class, 'index'])
        ->name('compras.index');

    Route::get('/compras/data', [ComprasController::class, 'data'])
        ->name('compras.data');

    Route::get('/compras/exportar', [ComprasController::class, 'exportarCSV'])
        ->name('compras.exportar');

    Route::prefix('ciudades')->middleware('auth')->group(function () {
        Route::get('/', [CiudadesNormalizacionController::class, 'index']);

        Route::get('/canonicas', [CiudadesNormalizacionController::class, 'canonicas']);
        Route::post('/canonicas', [CiudadesNormalizacionController::class, 'storeCanonica']);
        Route::put('/canonicas', [CiudadesNormalizacionController::class, 'updateCanonica']);

        Route::get('/alias/{ciudadCanonica}', [CiudadesNormalizacionController::class, 'alias']);
        Route::post('/alias', [CiudadesNormalizacionController::class, 'store']);
        Route::put('/alias', [CiudadesNormalizacionController::class, 'update']);
        Route::delete('/alias/{alias}', [CiudadesNormalizacionController::class, 'destroy']);
    });

});

require __DIR__.'/auth.php';
