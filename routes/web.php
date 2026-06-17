<?php

use App\Http\Controllers\Configuracion\ConfiguracionController;
use App\Http\Controllers\Clientes\ClientesController;
use App\Http\Controllers\Cupos\CuposController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Pagos\PagosController;
use App\Http\Controllers\Parqueadero\ParqueaderoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Reportes\ReportesController;
use App\Http\Controllers\Roles\RolesController;
use App\Http\Controllers\Tarifas\TarifasController;
use App\Http\Controllers\Usuarios\UsuariosController;
use App\Http\Controllers\PublicationController;
use App\Http\Controllers\Vehiculos\VehiculosController;
use App\Http\Controllers\Ventas\VentasController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('vehiculos/{vehiculo}/imagen', [VehiculosController::class, 'imagen'])->name('vehiculos.imagen');

    Route::prefix('panel')->group(function () {
        Route::get('clientes/exportar/excel', [ClientesController::class, 'exportarExcel'])->name('clientes.exportar.excel');
        Route::resource('clientes', ClientesController::class);
        Route::get('vehiculos/exportar', [VehiculosController::class, 'exportar'])->name('vehiculos.exportar');
        Route::resource('vehiculos', VehiculosController::class);
        Route::get('ventas/exportar', [VentasController::class, 'exportar'])->name('ventas.exportar');
        Route::resource('ventas', VentasController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update']);
        Route::get('/parqueadero', [ParqueaderoController::class, 'index'])->name('parqueadero.index');
        Route::get('/parqueadero/entrada', [ParqueaderoController::class, 'create'])->name('parqueadero.create');
        Route::post('/parqueadero/entrada', [ParqueaderoController::class, 'store'])->name('parqueadero.store');
        Route::get('/parqueadero/{movimiento}', [ParqueaderoController::class, 'show'])->name('parqueadero.show');
        Route::post('/parqueadero/{movimiento}/salida', [ParqueaderoController::class, 'salida'])->name('parqueadero.salida');
        Route::resource('cupos', CuposController::class);
        Route::resource('tarifas', TarifasController::class);
        Route::resource('pagos', PagosController::class)->only(['index', 'create', 'store', 'show']);
        Route::get('/reportes', [ReportesController::class, 'index'])->name('reportes.index');
        Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('configuracion.index');
        Route::get('/usuarios', [UsuariosController::class, 'index'])->name('usuarios.index');
        Route::get('/roles', [RolesController::class, 'index'])->name('roles.index');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile/avatar/{user}', [ProfileController::class, 'avatar'])->name('profile.avatar');

    Route::get('/vehicle-publications/index', [PublicationController::class, 'index'])->name('vehicle-publications.index');
    Route::get('/vehicle-publications/create', [PublicationController::class, 'create'])->name('vehicle-publications.create');
    Route::post('/vehicle-publications/store', [PublicationController::class, 'store'])->name('vehicle-publications.store');
    Route::delete('/vehicle-publications/{id}', [PublicationController::class, 'destroy'])->name('vehicle-publications.delete');
    Route::get('/vehicle-publications/{id}/edit', [PublicationController::class, 'edit'])->name('vehicle-publications.edit');
    Route::put('/vehicle-publications/{id}', [PublicationController::class, 'update'])->name('vehicle-publications.update');
});

require __DIR__.'/auth.php';
