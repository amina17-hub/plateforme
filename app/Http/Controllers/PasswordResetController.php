<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function sendResetLink(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink([
            'email' => $validated['email'],
        ]);

        if (in_array($status, [Password::RESET_LINK_SENT, Password::INVALID_USER], true)) {
            return response()->json([
                'success' => true,
                'message' => 'Si un compte existe avec cet email, un lien de réinitialisation a été envoyé.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $this->messageForStatus($status),
        ], 422);
    }

    public function showResetForm(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));

                try {
                    $user->sendPasswordResetConfirmationNotification();
                } catch (\Throwable $exception) {
                    Log::warning('Password reset confirmation notification could not be sent.', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'error' => $exception->getMessage(),
                    ]);
                }
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => $this->messageForStatus($status),
                ]);
        }

        return redirect()
            ->route('password.reset.success')
            ->with('status', 'Votre mot de passe a été mis à jour avec succès.');
    }

    public function success(): View
    {
        return view('auth.reset-password-success');
    }

    protected function messageForStatus(string $status): string
    {
        return match ($status) {
            Password::INVALID_TOKEN => 'Le lien de réinitialisation est invalide ou expiré.',
            Password::INVALID_USER => 'Aucun compte ne correspond à cet email.',
            Password::RESET_THROTTLED => 'Une demande a déjà été envoyée récemment. Réessayez dans un instant.',
            default => 'Impossible de traiter la réinitialisation du mot de passe pour le moment.',
        };
    }
}
