<?php

namespace App\Http\Middleware;

use App\Support\CurrentWorkspace;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCurrentWorkspace
{
    public function handle(Request $request, Closure $next): Response
    {
        // Get workspace ID from request header
        $workspaceId = $request->header('X-Workspace-Id');

        // Validate workspace ID
        if (!is_string($workspaceId) || !ctype_digit($workspaceId) || (int) $workspaceId < 1) {
            abort(400, 'Valid workspace ID is required.');
        }

        // Verify that the authenticated user belongs to this workspace
        $workspace = $request->user()
            ->workspaces()
            ->findOrFail((int) $workspaceId);

        // Set the active workspace
        app(CurrentWorkspace::class)->set($workspace->id);

        return $next($request);
    }
}
