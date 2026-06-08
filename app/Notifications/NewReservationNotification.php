<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewReservationNotification extends Notification
{
    use Queueable;

    public function __construct(protected Reservation $reservation)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'new_reservation',
            'title' => 'Nouvelle reservation',
            'message' => $this->reservation->client_name . ' a reserve un rendez-vous le ' . $this->reservation->reservation_date->format('d/m/Y') . ' a ' . substr((string) $this->reservation->reservation_time, 0, 5) . '.',
            'reservation_id' => $this->reservation->id,
            'client_name' => $this->reservation->client_name,
            'service_type' => $this->reservation->service_type,
            'status' => $this->reservation->status,
            'url' => route('artisan.dashboard') . '#reservations',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Nouvelle reservation')
            ->greeting('Bonjour ' . ($notifiable->name ?: ''))
            ->line($this->reservation->client_name . ' a cree une reservation.')
            ->line('Date: ' . $this->reservation->reservation_date->format('d/m/Y') . ' a ' . substr((string) $this->reservation->reservation_time, 0, 5))
            ->line('Service: ' . $this->reservation->service_type)
            ->action('Gerer la reservation', route('artisan.dashboard') . '#reservations');
    }
}
