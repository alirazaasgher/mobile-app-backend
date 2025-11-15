<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhoneVariant extends Model
{
    protected $fillable = [
        'phone_id', 'color_name', 'color_slug', 'color_hex',
        'storage_size', 'storage_gb', 'price', 'sku',
        'stock_quantity', 'is_available', 'images'
    ];

    protected $casts = [
        'storage_gb' => 'integer',
        'price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'is_available' => 'boolean',
        'images' => 'array',
    ];

    public function phone(): BelongsTo
    {
        return $this->belongsTo(Phone::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true)->where('stock_quantity', '>', 0);
    }

    public function scopeByStorage($query, int $storageGb)
    {
        return $query->where('storage_gb', $storageGb);
    }

    public function scopeByColor($query, string $colorSlug)
    {
        return $query->where('color_slug', $colorSlug);
    }
}