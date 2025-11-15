<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MobileHighlight extends Model
{
    use HasFactory;

    protected $fillable = ['mobile_id', 'text'];

    public function mobile()
    {
        return $this->belongsTo(Mobile::class);
    }
}
