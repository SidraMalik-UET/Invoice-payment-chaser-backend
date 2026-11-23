<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'workspace_name' => 'required|string|max:255',
        ]);

        $result = DB::transaction(function () use ($validated) {

            // Create user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
            ]);

            // Create first workspace
            $workspace = Workspace::create([
                'name' => $validated['workspace_name'],
                'slug' => Str::slug($validated['workspace_name'])
                    . '-' . Str::random(6),
                'owner_id' => $user->id,
                'currency' => 'USD',
                'timezone' => 'UTC',
                'reminder_hour' => 9,
            ]);

            // Attach user as workspace owner
            $user->workspaces()->attach($workspace->id, [
                'role' => 'owner',
            ]);

            // Generate Sanctum token
            $token = $user->createToken('auth-token')->plainTextToken;

            return compact('user', 'workspace', 'token');
        });

        return response()->json([
            'success' => true,
            'message' => 'Registration successful.',
            'data' => [
                'user' => $result['user'],
                'workspace' => $result['workspace'],
                'token' => $result['token'],
            ],
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.',
            ], 401);
        }

        // Generate Sanctum token
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout successful.',
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        $workspaces = $user->workspaces()
            ->get()
            ->map(function ($workspace) {
                return [
                    'id' => $workspace->id,
                    'name' => $workspace->name,
                    'slug' => $workspace->slug,
                    'role' => $workspace->pivot->role,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'workspaces' => $workspaces,
            ],
        ]);
    }
}
