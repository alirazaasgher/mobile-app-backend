<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChipsetsBrands extends Model
{
    //
    public function chipsets()
    {
        return $this->hasMany(related: Chipset::class);
    }
}
