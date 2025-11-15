<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FilterValue extends Model
{
    protected $fillable = ['filter_id','value'];

    public function filter()
    {
        return $this->belongsTo(Filter::class);
    }

    public function mobiles()
    {
        return $this->belongsToMany(Mobile::class, 'mobile_filter_values');
    }
}
