<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Variant extends Model
{
    protected $fillable = ['phone_id', 'storage', 'ram', 'ram_type_id', 'storage_type_id', 'pkr_price', 'usd_price'];

    public function colors(): HasMany
    {
        return $this->hasMany(VariantColor::class);
    }

    public function ram_type(): BelongsTo
    {
        return $this->belongsTo(RamType::class, 'ram_type_id');
    }

    public function storage_type(): BelongsTo
    {
        return $this->belongsTo(StorageType::class, 'storage_type_id');
    }
}
