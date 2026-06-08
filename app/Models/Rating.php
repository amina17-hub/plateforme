<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'artisan_id',
        'rating',
        'comment',
    ];

    protected $casts = [
        'rating' => 'decimal:1',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function artisan()
    {
        return $this->belongsTo(Artisan::class);
    }
}
