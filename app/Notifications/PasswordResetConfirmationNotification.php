<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetConfirmationNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Confirmation de changement de mot de passe')
            ->greeting('Bonjour ' . ($notifiable->name ?: ''))
            ->line('Votre mot de passe artisanskikda vient d’être modifié avec succès.')
            ->line('Si ce changement ne vient pas de vous, contactez rapidement le support et sécurisez votre compte.');
    }
}
