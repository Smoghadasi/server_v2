<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InnerCityLoad extends Model
{
    public function city()
    {
        return $this->hasOne(ProvinceCity::class);
    }

    public function load()
    {
        return $this->hasOne(Load::class);
    }
}
