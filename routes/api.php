<?php

use App\Http\Controllers\Api\MaintenanceLogController;
use App\Http\Controllers\Api\ShipController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| All routes here are prefixed with /api and use the 'api' middleware
| group which includes throttle:api (60 requests per minute per IP).
|
*/

// ─── Ships Endpoints ─────────────────────────────────────────────────────────
Route::apiResource('ships', ShipController::class)->only(['index', 'show']);

// ─── Maintenance Logs Endpoints ───────────────────────────────────────────────
Route::apiResource('maintenance-logs', MaintenanceLogController::class)->only(['index', 'show']);

// ─── Mark Service as Completed (triggers auto-schedule + queue notification) ─
Route::patch('maintenance-logs/{maintenanceLog}/complete', [MaintenanceLogController::class, 'complete'])
    ->name('maintenance-logs.complete');
