<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Variant extends Model {
    protected $fillable = ['phone_id','storage','ram','price_modifier_pkr','price_modifier_usd'];

      public function colors(): HasMany
    {
        return $this->hasMany(VariantColor::class);
    }
}