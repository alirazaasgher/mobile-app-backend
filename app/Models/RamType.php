<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RamType extends Model
{
    protected $fillable = ['name'];

    public function variants()
    {
        return $this->hasMany(Variant::class, 'ram_type_id');
    }
}

