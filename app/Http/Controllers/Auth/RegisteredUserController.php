<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $input = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:'.User::class.',username,'.($request->username_id ?? 'NULL').',user_id',
            'email' => 'required|string|lowercase|email|max:255',
            'phone' => 'required|string|max:20',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Check for existing provisional user
        $user = User::where('email', $input['email'])->first();

        if ($user) {
            if ($user->status !== 'provisional') {
                return redirect()->back()->withErrors(['email' => 'The email has already been taken.']);
            }
            
            // Update provisional user
            $user->update([
                'first_name' => $input['first_name'],
                'last_name' => $input['last_name'],
                'username' => $input['username'],
                'phone' => $input['phone'],
                'password' => Hash::make($input['password']),
                'status' => 'active',
                'is_active' => true,
            ]);
        } else {
            // Check unique username for new users
            if (User::where('username', $input['username'])->exists()) {
                return redirect()->back()->withErrors(['username' => 'The username has already been taken.']);
            }

            $user = User::create([
                'first_name' => $input['first_name'],
                'last_name' => $input['last_name'],
                'username' => $input['username'],
                'email' => $input['email'],
                'phone' => $input['phone'],
                'password' => Hash::make($input['password']),
                'role_id' => 1, // Default to patient
                'status' => 'active',
                'is_active' => true,
            ]);
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
