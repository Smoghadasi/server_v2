<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function inner_city_load()
    {
        return $this->belongsTo(InnerCityLoad::class);
    }
}
