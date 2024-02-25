<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Load extends Model
{

    use SoftDeletes;
    private static $transportationCompany_id;
    protected $appends = ['numOfRequestedDrivers', 'numOfSelectedDrivers', 'numOfDriverCalls', 'numOfInquiryDrivers', 'originCity', 'destinationCity'];

    public function diver()
    {
        return $this->hasOne(Driver::class);
    }

    public function fleet()
    {
        return $this->hasOne(Fleet::class);
    }

    public function load_type()
    {
        return $this->hasOne(LoadType::class);
    }

    public function inner_city_load()
    {
        return $this->belongsTo(InnerCityLoad::class);
    }

    public function outer_city_load()
    {
        return $this->belongsTo(OuterCityLoad::class);
    }

    public function tender()
    {
        return $this->belongsTo(Tender::class);
    }

    public function fleetLoads()
    {
        return $this->hasMany(FleetLoad::class);
    }

    public function getNumOfRequestedDriversAttribute()
    {
        return 0;
        return FleetLoad::where('load_id', $this->id)->sum("numOfFleets");
    }

    public function getNumOfInquiryDriversAttribute()
    {
        return Inquiry::where('load_id', $this->id)->count();
    }
    public function getNumOfDriverCallsAttribute()
    {
        return DriverCall::where('load_id', $this->id)->count();
    }

    public function getNumOfSelectedDriversAttribute()
    {
        return 0;
        return DriverLoad::where('load_id', $this->id)->count();
    }


    public function getOriginCityAttribute()
    {
        return CityOwner::where('id', $this->origin_city_id)->select('name as from', 'state as provinceFrom')->first();
    }

    public function getDestinationCityAttribute()
    {
        return CityOwner::where('id', $this->destination_city_id)->select('name as to', 'state as provinceTo')->first();
    }


    public function dateOfCargoDeclarations()
    {
        return $this->hasMany(DateOfCargoDeclaration::class);
    }

    public function driverCall()
    {
        return $this->hasOne(DriverCall::class);
    }

    public function driverCalls()
    {
        return $this->hasMany(DriverCall::class);
    }

    public function owner()
    {
        return $this->belongsTo(Owner::class, 'user_id');
    }

    //    public function getDriverVisitCountAttribute(): int
    //    {
    //        try {
    //            return DriverVisitLoad::where('load_id', $this->id)->count();
    //        } catch (\Exception $exception) {
    //
    //        }
    //
    //        return 0;
    //    }

}
