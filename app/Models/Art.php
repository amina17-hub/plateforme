<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Art extends Model
{
    protected $table = 'art';

    protected $fillable = [
        'user_id',
        'metier',
        'experience',
        'photo',
        'title',
        'description'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
