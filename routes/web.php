<?php

declare(strict_types=1);

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\ClientWebController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\FeatureWebController;
use App\Http\Controllers\Web\LicenseWebController;
use App\Http\Controllers\Web\PaymentWebController;
use App\Http\Controllers\Web\ProjectWebController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Clientes
    Route::get('clients/{client}/report', [ClientWebController::class, 'report'])->name('clients.report');
    Route::get('clients/{client}/statement', [ClientWebController::class, 'statement'])->name('clients.statement');
    Route::resource('clients', ClientWebController::class);

    // Licencias MikPoS
    Route::resource('licenses', LicenseWebController::class);

    // Mejoras / Cambios MikPoS
    Route::resource('features', FeatureWebController::class);

    // Proyectos a la Medida
    Route::resource('projects', ProjectWebController::class);

    // Pagos / Abonos
    Route::resource('payments', PaymentWebController::class)->except(['edit', 'update']);

    // Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
