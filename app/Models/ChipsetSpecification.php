<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChipsetSpecification extends Model
{
    protected $fillable = [
        'chipset_id',
        'category',
        'specifications',
    ];

    protected $casts = [
        'specifications' => 'array',
    ];

    public function chipset(): BelongsTo
    {
        return $this->belongsTo(Chipset::class);
    }

    // Get specific spec value
    public function getSpec(string $key, $default = null)
    {
        return data_get($this->specifications, $key, $default);
    }

    // Category scopes
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
