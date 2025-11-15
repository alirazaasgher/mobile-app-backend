<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MobileSpec extends Model
{
    use HasFactory;

    protected $fillable = ['mobile_id', 'category', 'value'];

    public function mobile()
    {
        return $this->belongsTo(Mobile::class);
    }
}
