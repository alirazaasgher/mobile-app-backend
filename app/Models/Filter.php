<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Filter extends Model
{
    protected $fillable = ['name','slug','type'];

    public function values()
    {
        return $this->hasMany(FilterValue::class);
    }
}
