<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileScore extends Model
{
    protected $casts = [
        'score' => 'decimal:2',
        'breakdown' => 'array',
    ];
    protected $fillable = [
        'phone_id',
        'category',
        'score',
        'breakdown',
        'profile'
    ];
}
