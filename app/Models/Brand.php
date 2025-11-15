<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    //
    public function mobiles()
    {
        return $this->hasMany(Mobile::class);
    }
}
