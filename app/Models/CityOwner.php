<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CityOwner extends Model
{
    use HasFactory;

    // public function state()
    // {
    //     return $this->belongsTo(State::class);
    // }

    public function drivers()
    {
        return $this->hasMany(Driver::class, 'city_id');
    }

    public function inner_city_load()
    {
        return $this->belongsTo(InnerCityLoad::class);
    }
}
