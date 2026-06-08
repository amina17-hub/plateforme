<?php

namespace App\Http\Controllers;

use App\Mail\RegistrationWelcomeMail;
use App\Models\User;
use App\Models\Client;
use App\Models\Artisan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'role' => 'required|in:client,artisan',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'commune' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'description' => 'nullable|string',
            'service_type' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(function () use ($request) {
                    return $request->input('role') === 'artisan';
                }),
            ],
        ]);

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
            ]);

            if ($validated['role'] === 'client') {
                Client::create([
                    'user_id' => $user->id,
                    'name' => $validated['name'],
                    'city' => $validated['city'],
                    'commune' => $validated['commune'] ?? null,
                    'latitude' => $validated['latitude'] ?? null,
                    'longitude' => $validated['longitude'] ?? null,
                ]);

                return $user;
            }

            Artisan::create([
                'user_id' => $user->id,
                'name' => $validated['name'],
                'service_type' => $validated['service_type'],
                'description' => $validated['description'] ?? null,
                'city' => $validated['city'],
                'commune' => $validated['commune'] ?? null,
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
            ]);

            return $user;
        });

        $mailer = (string) config('mail.default', 'log');
        $mailLoggedOnly = in_array($mailer, ['log', 'array'], true);
        $mailSent = false;

        try {
            Mail::to($user->email)->send(new RegistrationWelcomeMail($user));
            $mailSent = true;
        } catch (\Throwable $exception) {
            Log::warning('Registration welcome email could not be sent.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'mailer' => $mailer,
                'error' => $exception->getMessage(),
            ]);
        }

        $message = 'Inscription réussie !';

        if ($mailSent && $mailLoggedOnly) {
            $message .= ' L\'email de bienvenue a été généré en mode local et enregistré dans les logs.';
        } elseif ($mailSent) {
            $message .= ' Un email de bienvenue a été envoyé à votre adresse.';
        } else {
            $message .= ' Votre compte a été créé, mais l\'email de bienvenue n\'a pas pu être envoyé.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }
}
