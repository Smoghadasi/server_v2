<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Fleet extends Model
{

    protected $appends = ['numOfDrivers'];

    //    public function load()
    //    {
    //        return $this->belongsTo(Load::class);
    //    }
    //
    //    public function truck()
    //    {
    //        return $this->belongsTo(Truck::class);
    //    }

    public function parent()
    {
        return $this->belongsTo(Fleet::class);
    }

    public function drivers()
    {
        return $this->hasMany(Driver::class);
    }

    public function getNumOfDriversAttribute()
    {
        try {
            return Driver::where('fleet_id', $this->id)->count();
        } catch (\Exception $exception) {
        }
        return 0;
    }

    public function cargoReport()
    {
        return $this->hasOne(CargoReportByFleet::class);
    }
}
