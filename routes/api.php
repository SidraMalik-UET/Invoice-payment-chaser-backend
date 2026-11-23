
<?php

use Illuminate\Support\Facades\Route;
use App\Support\CurrentWorkspace;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\WorkspaceController;
use App\Http\Controllers\Api\InvitationController;

// All API routes use v1 prefix
Route::prefix('v1')->group(function () {

    // Authentication routes
    Route::prefix('auth')->group(function () {

        Route::post('/register', [AuthController::class, 'register']);

        Route::post('/login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {

            Route::post('/logout', [AuthController::class, 'logout']);

            Route::get('/me', [AuthController::class, 'me']);

        });
    });


    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {

        // Create workspace
        Route::post('/workspaces', [WorkspaceController::class, 'store']);


        // Create invitation (requires selected workspace)
        Route::middleware('workspace')->post(
            '/invitations',
            [InvitationController::class, 'store']
        );


        // Accept invitation (does NOT require workspace middleware)
        Route::post(
            '/invitations/{token}/accept',
            [InvitationController::class, 'accept']
        );


        // Workspace test route
        Route::middleware('workspace')->get('/workspace-test', function () {

            return response()->json([
                'success' => true,
                'workspace_id' => app(CurrentWorkspace::class)->id(),
            ]);

        });

    });

});
