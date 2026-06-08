<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    // Colonnes de la table que l'on peut remplir via create()
    protected $fillable = [
        'user_id',
        'name',
        'city',
        'commune',
        'latitude',
        'longitude',
    ];

    // Relation inverse vers l'utilisateur
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }
}
