<?php

namespace App\Http\Controllers;

use App\Support\Permissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ApiTokenController extends Controller
{
    public function index()
    {
        $this->requirePermission(Permissions::MANAGE_SYSTEM);

        $user = Auth::user();

        return Inertia::render('Settings/ApiTokens', [
            'tokens' => $user->tokens()
                ->orderByDesc('created_at')
                ->get(['id', 'name', 'abilities', 'last_used_at', 'created_at'])
                ->map(fn ($token) => [
                    'id' => $token->id,
                    'name' => $token->name,
                    'abilities' => $token->abilities,
                    'last_used_at' => $token->last_used_at?->toIso8601String(),
                    'created_at' => $token->created_at?->toIso8601String(),
                ]),
        ]);
    }

    public function store(Request $request)
    {
        $this->requirePermission(Permissions::MANAGE_SYSTEM);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $abilities = $user->getAllPermissions()->pluck('name')->values()->all();

        $token = $user->createToken($validated['name'], $abilities);

        return back()->with([
            'success' => 'API token created. Copy it now — it will not be shown again.',
            'new_api_token' => $token->plainTextToken,
        ]);
    }

    public function destroy($id)
    {
        $this->requirePermission(Permissions::MANAGE_SYSTEM);

        Auth::user()->tokens()->where('id', $id)->delete();

        return back()->with('success', 'API token revoked.');
    }
}
