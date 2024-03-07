<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoadBackup extends Model
{
    protected $fillable = array('*');

    private static $transportationCompany_id;
    protected $appends = ['numOfRequestedDrivers', 'numOfSelectedDrivers', 'originCity', 'destinationCity'];

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
        return FleetLoad::where('load_id', $this->id)->sum("numOfFleets");
    }

    public function getNumOfSelectedDriversAttribute()
    {
        return DriverLoad::where('load_id', $this->id)->count();
    }


    public function getOriginCityAttribute()
    {
        return ProvinceCity::where('id', $this->origin_city_id)->first();
    }

    public function getDestinationCityAttribute()
    {
        return ProvinceCity::where('id', $this->destination_city_id)->first();
    }


    public function dateOfCargoDeclarations()
    {
        return $this->hasMany(DateOfCargoDeclaration::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'user_id');
    }

    public function bearing()
    {
        return $this->belongsTo(Bearing::class, 'user_id');
    }

    public function owner()
    {
        return $this->belongsTo(Owner::class, 'user_id');
    }


}
