<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CityDistanceCalculate extends Model
{
    use HasFactory;

    public function fromCity()
    {
        return $this->belongsTo(CityOwner::class, 'fromCity_id');
    }

    public function toCity()
    {
        return $this->belongsTo(CityOwner::class, 'toCity_id');
    }
}
