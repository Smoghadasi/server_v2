<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OuterCityLoad extends Model
{

    public function origin_city_id()
    {
        return $this->hasOne(City::class);
    }

    public function destination_city_id()
    {
        return $this->hasOne(City::class);
    }

    public function loads()
    {
        return $this->hasOne(Load::class);
    }
}
