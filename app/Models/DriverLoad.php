<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverLoad extends Model
{
    protected $appends = [
        'numOfRateDriver',
    ];

    public function getNumOfRateDriverAttribute()
    {
        $driverLoad = DriverLoad::where('type', 'Driver')
            ->where('owner_id', $this->owner_id)
            ->where('driver_id', $this->driver_id)
            ->first();
        return $driverLoad->value;
    }
}
