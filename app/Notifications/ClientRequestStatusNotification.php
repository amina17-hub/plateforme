<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClientRequestStatusNotification extends Notification
{
    use Queueable;

    public function __construct(protected array $requestData, protected string $status)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        $accepted = $this->status === 'acceptee';

        return [
            'kind' => 'client_request_status',
            'title' => $accepted ? 'Demande acceptee' : 'Demande refusee',
            'message' => $this->requestData['artisan_name'] . ($accepted ? ' a accepte votre demande.' : ' a refuse votre demande.'),
            'artisan_name' => $this->requestData['artisan_name'],
            'service_type' => $this->requestData['service_type'] ?? 'Service artisan',
            'status' => $this->status,
            'url' => route('client.dashboard') . ($accepted ? '#chat' : '#reservations'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $accepted = $this->status === 'acceptee';

        return (new MailMessage())
            ->subject($accepted ? 'Votre demande a ete acceptee' : 'Votre demande a ete refusee')
            ->greeting('Bonjour ' . ($notifiable->name ?: ''))
            ->line($this->requestData['artisan_name'] . ($accepted ? ' a accepte votre demande.' : ' a refuse votre demande.'))
            ->line('Service: ' . ($this->requestData['service_type'] ?? 'Service artisan'))
            ->action('Voir votre espace client', route('client.dashboard'));
    }
}
