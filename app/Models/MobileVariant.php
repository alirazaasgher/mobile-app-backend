<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MobileVariant extends Model
{
    use HasFactory;

    protected $fillable = ['mobile_id', 'ram', 'storage'];

    public function mobile()
    {
        return $this->belongsTo(Mobile::class);
    }

    public function colors()
    {
        return $this->hasMany(MobileVariantColor::class, 'variant_id');
    }
}
