<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Email ou mot de passe incorrect'
            ], 401);
        }

        $storedPassword = (string) $user->password;
        $plainPassword = $validated['password'];

        $isHashedPassword = str_starts_with($storedPassword, '$2y$')
            || str_starts_with($storedPassword, '$2b$')
            || str_starts_with($storedPassword, '$argon2');

        $passwordMatches = false;

        if ($isHashedPassword) {
            $passwordMatches = Hash::check($plainPassword, $storedPassword);

            if ($passwordMatches && Hash::needsRehash($storedPassword)) {
                $user->password = Hash::make($plainPassword);
                $user->save();
            }
        } else {
            // Migration transparente des anciens comptes stockés en clair.
            $passwordMatches = hash_equals($storedPassword, $plainPassword);

            if ($passwordMatches) {
                $user->password = Hash::make($plainPassword);
                $user->save();
            }
        }

        if (! $passwordMatches) {
            return response()->json([
                'success' => false,
                'message' => 'Email ou mot de passe incorrect'
            ], 401);
        }

        Auth::login($user);
        $request->session()->regenerate();

        if ($user->role === 'admin') {
            $redirect = route('admin.dashboard');
        } elseif ($user->role === 'artisan') {
            $redirect = route('artisan.dashboard');
        } else {
            $redirect = route('client.dashboard');
        }

        return response()->json([
            'success' => true,
            'redirect' => $redirect
        ]);
    }
}
