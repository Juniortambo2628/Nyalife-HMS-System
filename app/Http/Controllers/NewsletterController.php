<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'name' => 'nullable|string|max:255',
        ]);

        NewsletterSubscriber::firstOrCreate(
            ['email' => strtolower($validated['email'])],
            [
                'name' => $validated['name'] ?? null,
                'subscribed_at' => now(),
            ]
        );

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Thank you for subscribing to our newsletter!',
                'status' => 'success',
            ]);
        }

        return back()->with('success', 'Thank you for subscribing!');
    }
}
