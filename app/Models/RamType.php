<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class RamType extends Model
{
    protected $fillable = ['name'];

    public function variants()
    {
        return $this->hasMany(Variant::class, 'ram_type_id');
    }

    protected static function booted()
    {
        static::saved(callback: fn() => Cache::forget('memory_types'));
        static::deleted(fn() => Cache::forget('memory_types'));
    }
}

