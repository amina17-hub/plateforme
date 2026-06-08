<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Artisan extends Model
{
    use HasFactory;

    // Colonnes de la table que l'on peut remplir via create()
    protected $fillable = [
        'user_id',
        'name',
        'service_type',
        'description',
        'city',
        'commune',
        'latitude',
        'longitude',
    ];

    // Relation inverse vers l'utilisateur
    public function user()
{
    return $this->belongsTo(\App\Models\User::class);
}   
    public function works()
{
    return $this->hasMany(\App\Models\Work::class);
}

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function availabilities()
    {
        return $this->hasMany(ArtisanAvailability::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }


}
