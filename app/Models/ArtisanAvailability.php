<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArtisanAvailability extends Model
{
    use HasFactory;

    protected $fillable = [
        'artisan_id',
        'available_date',
        'is_available',
    ];

    protected $casts = [
        'available_date' => 'date:Y-m-d',
        'is_available' => 'boolean',
    ];

    public function artisan()
    {
        return $this->belongsTo(Artisan::class);
    }
}
