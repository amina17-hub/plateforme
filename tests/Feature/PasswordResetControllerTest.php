<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\PasswordResetConfirmationNotification;
use App\Notifications\ResetPasswordLinkNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_request_a_password_reset_link(): void
    {
        Notification::fake();

        $user = User::create([
            'name' => 'Client Test',
            'email' => 'client@example.com',
            'password' => Hash::make('ancien123'),
            'role' => 'client',
        ]);

        $response = $this->postJson(route('password.email'), [
            'email' => $user->email,
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        Notification::assertSentTo($user, ResetPasswordLinkNotification::class);
    }

    public function test_user_can_reset_password_and_receive_confirmation_notification(): void
    {
        Notification::fake();

        $user = User::create([
            'name' => 'Artisan Test',
            'email' => 'artisan@example.com',
            'password' => Hash::make('ancien123'),
            'role' => 'artisan',
        ]);

        $token = Password::broker()->createToken($user);

        $response = $this->post(route('password.store'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'nouveau456',
            'password_confirmation' => 'nouveau456',
        ]);

        $response->assertRedirect(route('password.reset.success'));

        $user->refresh();

        $this->assertTrue(Hash::check('nouveau456', $user->password));
        Notification::assertSentTo($user, PasswordResetConfirmationNotification::class);
    }
}
