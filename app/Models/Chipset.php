<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;

class Chipset extends Model
{


    protected $fillable = [
        'name',
        'brand_id',
        'primary_image',
        'announced_year',
        'tier',
        'slug',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(ChipsetsBrands::class, 'brand_id', 'id');
    }

    public function specifications(): HasMany
    {
        return $this->hasMany(ChipsetSpecification::class);
    }

    public function mobiles()
    {
        return $this->hasMany(Phone::class);
    }
}
