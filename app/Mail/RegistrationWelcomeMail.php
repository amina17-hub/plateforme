<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistrationWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bienvenue sur artisanskikda',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.registration-welcome',
            with: [
                'user' => $this->user,
                'roleLabel' => $this->user->role === 'artisan' ? 'artisan' : 'client',
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
