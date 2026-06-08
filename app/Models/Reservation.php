<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_user_id',
        'artisan_id',
        'artisan_user_id',
        'client_name',
        'artisan_name',
        'service_type',
        'city',
        'quoted_price',
        'reservation_date',
        'reservation_time',
        'notes',
        'status',
    ];

    protected $casts = [
        'reservation_date' => 'date:Y-m-d',
        'quoted_price' => 'decimal:2',
    ];

    public function artisan()
    {
        return $this->belongsTo(Artisan::class);
    }

    public function clientUser()
    {
        return $this->belongsTo(User::class, 'client_user_id');
    }

    public function artisanUser()
    {
        return $this->belongsTo(User::class, 'artisan_user_id');
    }
}
