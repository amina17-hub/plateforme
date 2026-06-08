<?php

namespace Tests\Feature;

use App\Mail\HomeContactMessageMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class HomeContactControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_contact_form_sends_email_to_configured_recipient(): void
    {
        Mail::fake();
        Config::set('services.contact.recipient_email', 'artisanskikda6@gmail.com');

        $payload = [
            'name' => 'Client Test',
            'email' => 'client@example.com',
            'message' => 'Bonjour, je veux plus d informations sur vos services.',
        ];

        $response = $this->postJson(route('home.contact'), $payload);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        Mail::assertSent(HomeContactMessageMail::class, function (HomeContactMessageMail $mail) use ($payload) {
            return $mail->hasTo('artisanskikda6@gmail.com')
                && $mail->hasReplyTo($payload['email'])
                && $mail->senderName === $payload['name']
                && $mail->senderEmail === $payload['email']
                && $mail->messageBody === $payload['message'];
        });
    }
}
