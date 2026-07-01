<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'amount',
        'due_date',
        'icon',
        'is_paid'
    ];

    // Relasi balik ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
