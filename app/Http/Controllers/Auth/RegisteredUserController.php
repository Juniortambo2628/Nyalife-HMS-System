<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeEmail;
use App\Models\Patient;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
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
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $input = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:'.User::class.',username,'.($request->username_id ?? 'NULL').',user_id',
            'email' => 'required|string|lowercase|email|max:255',
            'phone' => 'required|string|max:20',
            'gender' => 'nullable|string|in:male,female,other',
            'date_of_birth' => 'nullable|date',
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
                'gender' => $input['gender'] ?? $user->gender,
                'date_of_birth' => $input['date_of_birth'] ?? $user->date_of_birth,
                'password' => $input['password'],
                'status' => 'active',
                'is_active' => true,
            ]);

            // Ensure Spatie patient role is assigned
            if (! $user->hasRole('patient')) {
                $user->assignRole('patient');
            }
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
                'gender' => $input['gender'] ?? null,
                'date_of_birth' => $input['date_of_birth'] ?? null,
                'password' => $input['password'],
                'role_id' => Role::idFromName('patient'),
                'status' => 'active',
                'is_active' => true,
            ]);

            // Assign Spatie patient role
            $user->assignRole('patient');

            // Create Patient record if missing
            Patient::firstOrCreate(
                ['user_id' => $user->user_id],
                ['patient_number' => Patient::generateNumber($user->user_id)]
            );
        }

        event(new Registered($user));

        // Send welcome email
        try {
            Mail::to($user->email)->send(new WelcomeEmail([
                'user_name' => $user->first_name.' '.$user->last_name,
                'clinic_name' => config('app.name', "Nyalife Women's Clinic"),
            ]));
        } catch (\Exception $e) {
            Log::warning('Welcome email failed: '.$e->getMessage());
        }

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
