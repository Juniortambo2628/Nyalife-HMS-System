<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckGuestDataRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class CheckGuestDataController extends Controller
{
    /**
     * Check if an email belongs to a provisional user (guest appointment).
     * Returns their basic info for auto-filling the registration form.
     */
    public function check(CheckGuestDataRequest $request): JsonResponse
    {
        $user = User::where('email', $request->validated('email'))
            ->where('status', 'provisional')
            ->first();

        if (!$user) {
            return response()->json([
                'found' => false,
            ], 404);
        }

        return response()->json([
            'found' => true,
            'data' => [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'phone' => $user->phone,
            ],
        ]);
    }
}
