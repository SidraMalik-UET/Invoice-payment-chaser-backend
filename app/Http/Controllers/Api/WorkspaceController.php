<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WorkspaceController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'currency' => 'sometimes|string|max:10',
            'timezone' => 'sometimes|string|max:100',
        ]);

        $user = $request->user();

        $workspace = DB::transaction(function () use ($validated, $user) {

            $workspace = Workspace::create([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name'])
                    . '-' . Str::random(6),
                'owner_id' => $user->id,
                'currency' => $validated['currency'] ?? 'USD',
                'timezone' => $validated['timezone'] ?? 'UTC',
                'reminder_hour' => 9,
            ]);

            $user->workspaces()->attach($workspace->id, [
                'role' => 'owner',
            ]);

            return $workspace;
        });

        return response()->json([
            'success' => true,
            'message' => 'Workspace created successfully.',
            'data' => [
                'workspace' => $workspace,
                'role' => 'owner',
            ],
        ], 201);
    }
}
