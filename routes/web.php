<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EncuestasController;
use App\Http\Controllers\SuscriptoresController;
use App\Http\Controllers\EstadisticasController;
use App\Http\Controllers\ComprasController;
use App\Http\Controllers\ChatbotExecutionController;
use App\Http\Controllers\CityAliasController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\ChatbotConversationController;
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
    Route::post('/encuestas/actualizar', [EncuestasController::class, 'actualizar'])->name('encuestas.actualizar');
    Route::post('/encuestas/sync-auto-update', [EncuestasController::class, 'syncAutoUpdate']);

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

    Route::prefix('cities')->name('cities.')->middleware('auth')->group(function () {
        Route::get('/', [CityAliasController::class, 'index'])->name('index');
        Route::post('/cities-alias', [CityAliasController::class, 'store']);
        Route::put('/cities-alias/{alias}', [CityAliasController::class, 'update']);
        Route::get('/cities-canonicas', [CityAliasController::class, 'canonicasSearch']);
        Route::get('/cities-alias/check-duplicate', [CityAliasController::class, 'checkDuplicate']);
    });

    Route::prefix('chatbot')
        ->name('chatbot.')
        ->group(function () {
            Route::get('/', [ChatbotController::class, 'index'])
                ->name('index');
            Route::get('/create', [ChatbotController::class, 'create'])
                ->name('create');
            Route::post('/', [ChatbotController::class, 'store'])
                ->name('store');
            Route::get('/{topic}/edit', [ChatbotController::class, 'edit'])
                ->name('edit');
            Route::put('/{topic}', [ChatbotController::class, 'update'])
                ->name('update');
            Route::post('/{topic}/duplicate', [ChatbotController::class, 'duplicate'])
                ->name('duplicate');
            Route::post('/{topic}/activate', [ChatbotController::class, 'activate'])
                ->name('activate');
            Route::post('/{topic}/deactivate', [ChatbotController::class, 'deactivate'])
                ->name('deactivate');
            Route::get('/{topic}/versions', [ChatbotController::class, 'versions'])
                ->name('versions');
            Route::post('/{topic}/versions/{version}/restore',[ChatbotController::class, 'restoreVersion'])
                ->name('restore-version');
            Route::get('/conversations', [ChatbotConversationController::class, 'index'])
                ->name('conversations.index');
            Route::get('/conversations/{conversation}', [ChatbotConversationController::class, 'show'])
                ->name('conversations.show');
            Route::get('/conversations/{conversation}/execution', [ChatbotExecutionController::class, 'show'])
                ->name('conversations.execution');
    });
});

require __DIR__.'/auth.php';
