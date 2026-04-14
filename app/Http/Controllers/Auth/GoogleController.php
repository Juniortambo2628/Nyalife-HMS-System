<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Http\Request;

class GoogleController extends Controller
{
    public function redirectToGoogle(Request $request)
    {
        try {
            if ($request->role) {
                session(['auth_role' => $request->role]);
            }
            
            // Re-calculate the callback URL.
            // If the server's APP_URL is null, route() might fail to produce the correct domain/protocol.
            $redirectUrl = route('auth.google.callback');
            
            // Safety: If we are on production but the generated URL is not https, force it.
            if (app()->environment('production') && !str_starts_with($redirectUrl, 'https')) {
                $redirectUrl = str_replace('http://', 'https://', $redirectUrl);
            }

            Log::info('Google Auth Initializing', [
                'redirect_url' => $redirectUrl,
                'role' => $request->role,
                'app_env' => app()->environment()
            ]);
            
            return Socialite::driver('google')
                ->stateless()
                ->redirectUrl($redirectUrl)
                ->redirect();
        } catch (\Exception $e) {
            Log::error('Google Auth Redirect Error: ' . $e->getMessage(), [
                'exception' => $e,
                'role' => $request->role,
                'trace' => $e->getTraceAsString()
            ]);

            $route = $request->role === 'staff' ? 'login.staff' : 'login.patient';
            return redirect()->route($route)->with('error', 'Could not initialize Google authentication. Error: ' . $e->getMessage());
        }
    }

    public function handleGoogleCallback()
    {
        $authRole = session('auth_role', 'patient');
        try {
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->redirectUrl(route('auth.google.callback'))
                ->user();
            
            session()->forget('auth_role');
            
            $user = User::where('google_id', $googleUser->id)
                        ->orWhere('email', $googleUser->email)
                        ->first();
 
            if ($user) {
                // If logging in through staff portal, ensure they ARE staff
                if ($authRole === 'staff' && $user->role === 'patient') {
                    return redirect()->route('login.staff')->with('error', 'This account is not authorized for staff access.');
                }

                // Update google id and token if not set
                $user->update([
                    'google_id' => $googleUser->id,
                    'google_token' => $googleUser->token,
                    'google_refresh_token' => $googleUser->refreshToken,
                ]);

                Auth::login($user);

                return redirect()->intended(route('dashboard', [], false));
            } else {
                // Staff accounts MUST be pre-created by admin
                if ($authRole === 'staff') {
                    return redirect()->route('login.staff')->with([
                        'error' => 'Staff accounts must be created by an administrator. Please contact IT support if you believe this is an error.'
                    ]);
                }

                // New patient user - store google data in session and redirect to complete profile
                session(['google_user' => [
                    'id' => $googleUser->id,
                    'email' => $googleUser->email,
                    'first_name' => $googleUser->user['given_name'] ?? '',
                    'last_name' => $googleUser->user['family_name'] ?? '',
                    'token' => $googleUser->token,
                    'refresh_token' => $googleUser->refreshToken,
                ]]);

                return redirect()->route('auth.google.complete-profile');
            }
        } catch (\Exception $e) {
            Log::error('Google Auth Callback Error: ' . $e->getMessage(), [
                'exception' => $e,
                'role' => $authRole
            ]);
            $route = $authRole === 'staff' ? 'login.staff' : 'login.patient';
            return redirect()->route($route)->with('error', 'Google authentication failed. Error: ' . $e->getMessage());
        }
    }

    public function completeProfileView()
    {
        if (!session('google_user')) {
            return redirect()->route('login');
        }

        return Inertia::render('Auth/CompleteProfile', [
            'google_user' => session('google_user')
        ]);
    }

    public function storeProfile(Request $request)
    {
        $googleData = session('google_user');
        if (!$googleData) {
            return redirect()->route('login');
        }

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'required|string|in:male,female,other',
            'date_of_birth' => 'required|date',
            'phone' => 'required|string|max:20',
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $googleData['email'],
            'google_id' => $googleData['id'],
            'google_token' => $googleData['token'],
            'google_refresh_token' => $googleData['refresh_token'],
            'password' => bcrypt(Str::random(16)),
            'role_id' => 7, // Default to Patient
            'gender' => $request->gender,
            'date_of_birth' => $request->date_of_birth,
            'phone' => $request->phone,
            'is_active' => true,
            'status' => 'active',
            'username' => strtolower($request->first_name . '.' . $request->last_name . Str::random(4)),
        ]);

        Auth::login($user);
        session()->forget('google_user');

        return redirect()->route('dashboard');
    }
}
