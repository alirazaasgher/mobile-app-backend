<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class PhoneColor extends Model {

    protected $fillable = ['phone_id','slug','name','hex_code'];

     public function images(): HasMany
    {
        return $this->hasMany(PhoneImage::class);
    }

  
}