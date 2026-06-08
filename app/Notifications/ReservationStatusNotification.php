<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservationStatusNotification extends Notification
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
            'kind' => 'reservation_status',
            'title' => $this->statusLabel(),
            'message' => 'Votre reservation avec ' . $this->reservation->artisan_name . ' est maintenant ' . strtolower($this->statusLabel()) . '.',
            'reservation_id' => $this->reservation->id,
            'artisan_name' => $this->reservation->artisan_name,
            'service_type' => $this->reservation->service_type,
            'status' => $this->reservation->status,
            'url' => route('client.dashboard') . '#reservations',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject($this->statusLabel())
            ->greeting('Bonjour ' . ($notifiable->name ?: ''))
            ->line('Votre reservation avec ' . $this->reservation->artisan_name . ' est maintenant ' . strtolower($this->statusLabel()) . '.')
            ->line('Date: ' . $this->reservation->reservation_date->format('d/m/Y') . ' a ' . substr((string) $this->reservation->reservation_time, 0, 5))
            ->action('Voir la reservation', route('client.dashboard') . '#reservations');
    }

    protected function statusLabel(): string
    {
        return match ($this->reservation->status) {
            'confirmee' => 'Reservation confirmee',
            'annulee' => 'Reservation refusee',
            default => 'Reservation en attente',
        };
    }
}
