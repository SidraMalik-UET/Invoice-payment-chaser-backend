<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\Workspace;
use App\Models\User;
use App\Mail\WorkspaceInvitationMail;
use App\Support\CurrentWorkspace;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class InvitationController extends Controller
{
    // Create a new invitation
    public function store(Request $request, CurrentWorkspace $currentWorkspace)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'role' => [
                'required',
                'string',
                Rule::in(['admin', 'member']),
            ],
        ]);

        $workspaceId = $currentWorkspace->id();

        $workspace = Workspace::findOrFail($workspaceId);

        // Only workspace owner or admin can invite members
        $membership = $workspace->users()
            ->where('users.id', $request->user()->id)
            ->first();

        $isOwner = (int) $workspace->owner_id === (int) $request->user()->id;
        $isAdmin = $membership && $membership->pivot->role === 'admin';

        if (!$isOwner && !$isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to send invitations.',
            ], 403);
        }

        // Check for an existing active invitation
        $existingInvitation = Invitation::where('workspace_id', $workspaceId)
            ->where('email', $validated['email'])
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->exists();

        if ($existingInvitation) {
            return response()->json([
                'success' => false,
                'message' => 'An active invitation already exists for this email.',
            ], 409);
        }

        $invitation = Invitation::create([
            'workspace_id' => $workspaceId,
            'email' => $validated['email'],
            'role' => $validated['role'],
            'token' => Str::random(64),
            'invited_by' => $request->user()->id,
            'expires_at' => now()->addDays(7),
        ]);

        // Queue the invitation email
        Mail::to($invitation->email)
            ->queue(new WorkspaceInvitationMail($invitation));

        return response()->json([
            'success' => true,
            'message' => 'Invitation created successfully.',
            'data' => [
                'id' => $invitation->id,
                'email' => $invitation->email,
                'role' => $invitation->role,
                'expires_at' => $invitation->expires_at,
            ],
        ], 201);
    }

    // Accept an invitation
    public function accept(Request $request, string $token)
    {
        $user = $request->user();

        $result = DB::transaction(function () use ($token, $user) {

            $invitation = Invitation::where('token', $token)
                ->lockForUpdate()
                ->first();

            if (!$invitation) {
                return [
                    'status' => 404,
                    'message' => 'Invitation not found.',
                ];
            }

            if ($invitation->accepted_at !== null) {
                return [
                    'status' => 422,
                    'message' => 'This invitation has already been used.',
                ];
            }

            if ($invitation->expires_at->isPast()) {
                return [
                    'status' => 422,
                    'message' => 'This invitation has expired.',
                ];
            }

            if (strcasecmp($invitation->email, $user->email) !== 0) {
                return [
                    'status' => 403,
                    'message' => 'This invitation belongs to another email address.',
                ];
            }

            $workspace = Workspace::findOrFail(
                $invitation->workspace_id
            );

            // Attach user to workspace with invited role
            $workspace->users()->syncWithoutDetaching([
                $user->id => [
                    'role' => $invitation->role,
                ],
            ]);

            $invitation->update([
                'accepted_at' => now(),
            ]);

            return [
                'status' => 200,
                'message' => 'Invitation accepted successfully.',
            ];
        });

        return response()->json([
            'success' => $result['status'] === 200,
            'message' => $result['message'],
        ], $result['status']);
    }
}
