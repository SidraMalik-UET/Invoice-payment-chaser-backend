<?php

use Illuminate\Support\Facades\Route;
use App\Support\CurrentWorkspace;
use App\Http\Controllers\Api\AuthController;

Route::middleware(['auth:sanctum', 'workspace'])
    ->prefix('v1')
    ->get('/workspace-test', function () {
        return response()->json([
            'success' => true,
            'workspace_id' => app(CurrentWorkspace::class)->id(),
        ]);
    });

Route::prefix('v1/auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
});    