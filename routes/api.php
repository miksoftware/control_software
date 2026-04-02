<?php

declare(strict_types=1);

use App\Http\Controllers\ClientController;
use App\Http\Controllers\CustomProjectController;
use App\Http\Controllers\MikposFeatureController;
use App\Http\Controllers\MikposLicenseController;
use App\Http\Controllers\PaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — MikSoftware Control System
|--------------------------------------------------------------------------
|
| Rutas RESTful versionadas para la gestión de clientes, licencias MikPoS,
| mejoras, proyectos a la medida y pagos/abonos.
|
*/

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ─── API v1 ─────────────────────────────────────────────────────
Route::prefix('v1')->as('api.v1.')->group(function () {

    // Clientes
    Route::get('clients/{client}/report', [ClientController::class, 'report'])->name('clients.report');
    Route::apiResource('clients', ClientController::class);

    // Licencias MikPoS
    Route::apiResource('licenses', MikposLicenseController::class);

    // Mejoras / Cambios MikPoS
    Route::apiResource('features', MikposFeatureController::class);

    // Proyectos a la Medida
    Route::apiResource('projects', CustomProjectController::class);

    // Pagos / Abonos
    Route::apiResource('payments', PaymentController::class)->except(['update']);

    // Resumen financiero de una entidad pagable
    Route::get('payments/financial-summary', [PaymentController::class, 'financialSummary'])
        ->name('payments.financial-summary');
});
