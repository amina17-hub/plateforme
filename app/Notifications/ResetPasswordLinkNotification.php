<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordLinkNotification extends Notification
{
    use Queueable;

    public function __construct(protected string $token)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $resetUrl = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage())
            ->subject('Réinitialisation de votre mot de passe')
            ->greeting('Bonjour ' . ($notifiable->name ?: ''))
            ->line('Nous avons reçu une demande de changement de mot de passe pour votre compte artisanskikda.')
            ->action('Choisir un nouveau mot de passe', $resetUrl)
            ->line('Ce lien reste valable pendant 60 minutes.')
            ->line('Si vous n’êtes pas à l’origine de cette demande, ignorez simplement cet email.');
    }
}
