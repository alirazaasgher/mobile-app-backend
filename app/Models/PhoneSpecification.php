<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhoneSpecification extends Model
{
    protected $fillable = [
        'phone_id', 'category', 'specifications', 'searchable_text'
    ];

    protected $casts = [
        'specifications' => 'array',
    ];

    public function phone(): BelongsTo
    {
        return $this->belongsTo(Phone::class);
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