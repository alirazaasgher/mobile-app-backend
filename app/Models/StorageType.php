<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StorageType extends Model
{
    protected $fillable = ['name'];

    /**
     * One storage type can belong to many variants
     */
    public function variants()
    {
        return $this->hasMany(Variant::class, 'storage_type_id');
    }
}
