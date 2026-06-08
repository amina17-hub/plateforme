<?php

namespace Tests\Feature;

use App\Mail\RegistrationWelcomeMail;
use App\Models\Artisan;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegisterControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_registration_creates_client_and_sends_welcome_email(): void
    {
        Mail::fake();

        $payload = [
            'role' => 'client',
            'name' => 'Client Test',
            'email' => 'client@example.com',
            'password' => 'secret123',
            'city' => 'Skikda',
            'commune' => 'Skikda',
            'latitude' => '36.876500',
            'longitude' => '6.909000',
        ];

        $response = $this->postJson(route('register'), $payload);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $user = User::where('email', $payload['email'])->first();

        $this->assertNotNull($user);
        $this->assertSame('client', $user->role);

        $this->assertDatabaseHas('clients', [
            'user_id' => $user->id,
            'name' => $payload['name'],
            'city' => $payload['city'],
            'commune' => $payload['commune'],
        ]);

        Mail::assertSent(RegistrationWelcomeMail::class, function (RegistrationWelcomeMail $mail) use ($payload, $user) {
            return $mail->hasTo($payload['email'])
                && $mail->user->is($user)
                && $mail->user->role === 'client';
        });
    }

    public function test_artisan_registration_creates_artisan_and_sends_welcome_email(): void
    {
        Mail::fake();

        $payload = [
            'role' => 'artisan',
            'name' => 'Artisan Test',
            'email' => 'artisan@example.com',
            'password' => 'secret123',
            'city' => 'Skikda',
            'commune' => 'Azzaba',
            'latitude' => '36.728300',
            'longitude' => '7.105100',
            'service_type' => 'Plomberie',
            'description' => 'Disponible pour tous travaux de plomberie.',
        ];

        $response = $this->postJson(route('register'), $payload);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $user = User::where('email', $payload['email'])->first();

        $this->assertNotNull($user);
        $this->assertSame('artisan', $user->role);

        $this->assertDatabaseHas('artisans', [
            'user_id' => $user->id,
            'name' => $payload['name'],
            'city' => $payload['city'],
            'commune' => $payload['commune'],
            'service_type' => $payload['service_type'],
            'description' => $payload['description'],
        ]);

        Mail::assertSent(RegistrationWelcomeMail::class, function (RegistrationWelcomeMail $mail) use ($payload, $user) {
            return $mail->hasTo($payload['email'])
                && $mail->user->is($user)
                && $mail->user->role === 'artisan';
        });
    }
}
