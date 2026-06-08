<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ArtisanRequestNotification extends Notification
{
    use Queueable;

    public function __construct(protected array $requestData)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'artisan_request',
            'title' => 'Nouvelle demande client',
            'message' => $this->requestData['client_name'] . ' vous a envoye un message pour ' . $this->requestData['service_type'] . '.',
            'client_user_id' => $this->requestData['client_user_id'],
            'client_name' => $this->requestData['client_name'],
            'artisan_id' => $this->requestData['artisan_id'],
            'artisan_name' => $this->requestData['artisan_name'],
            'service_type' => $this->requestData['service_type'],
            'request_message' => $this->requestData['message'],
            'status' => 'nouvelle',
            'url' => route('artisan.dashboard') . '#requests',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Nouvelle demande client')
            ->greeting('Bonjour ' . ($notifiable->name ?: ''))
            ->line($this->requestData['client_name'] . ' vous a envoye une demande pour ' . $this->requestData['service_type'] . '.')
            ->line($this->requestData['message'])
            ->action('Voir la demande', route('artisan.dashboard') . '#requests');
    }
}
