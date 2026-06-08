<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'artisan_id',
        'title',
        'description',
        'price',
        'city',
        'latitude',
        'longitude',
    ];

    public function artisan()
    {
        return $this->belongsTo(Artisan::class);
    }
}