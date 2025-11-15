<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MobileVariantColor extends Model
{
    use HasFactory;

    protected $fillable = ['variant_id', 'color', 'price'];

    public function variant()
    {
        return $this->belongsTo(MobileVariant::class, 'variant_id');
    }
}
