<?php

namespace App\Models;

use App\Notifications\PasswordResetConfirmationNotification;
use App\Notifications\ResetPasswordLinkNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Les colonnes qui peuvent être remplies via create()
       protected $fillable = [
    'name',    // <- important
    'email',
    'password',
    'role',
];

    // Ne pas exposer le mot de passe dans les tableaux/JSON
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Les relations avec client et artisan
    public function client()
    {
        return $this->hasOne(Client::class);
    }

    public function artisan()
{
    return $this->hasOne(\App\Models\Artisan::class);
}

public function art()
{
    return $this->hasOne(\App\Models\Art::class, 'user_id');
}

public function clientReservations()
{
    return $this->hasMany(\App\Models\Reservation::class, 'client_user_id');
}

public function artisanReservations()
{
    return $this->hasMany(\App\Models\Reservation::class, 'artisan_user_id');
}

public function sendPasswordResetNotification($token): void
{
    $this->notify(new ResetPasswordLinkNotification($token));
}

public function sendPasswordResetConfirmationNotification(): void
{
    $this->notify(new PasswordResetConfirmationNotification());
}
}
