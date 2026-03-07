<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BalataController;
use App\Http\Controllers\TarimaController;
use App\Http\Controllers\TallerController;
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('balatas.index');
    }

    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/balatas/catalogo', [BalataController::class, 'catalogo'])->name('balatas.catalogo');
    Route::get('/balatas/exportar-pdf', [BalataController::class, 'exportPdf'])->name('balatas.export.pdf');
    Route::resource('balatas', BalataController::class)->except('show');
    Route::resource('ventas', VentaController::class)->except('show');
    Route::resource('tarimas', TarimaController::class)->except('show');
    Route::resource('talleres', TallerController::class)->except('show');
});
