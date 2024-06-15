<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Load extends Model
{

    use SoftDeletes;
    private static $transportationCompany_id;
    protected $appends = [
        'numOfRequestedDrivers',
        'numOfSelectedDrivers',
        'numOfDriverCalls',
        'numOfInquiryDrivers',
        'originCity',
        'destinationCity',
        'distanceCity',
        'ownerAuthenticated',
        'numOfRateDriver',
        'avarageRateOwner',
    ];

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


    public function getNumOfRateDriverAttribute()
    {
        $driverLoad = Score::where('type', 'Driver')
            ->where('owner_id', $this->user_id)
            ->first();
        if ($driverLoad === null) {
            return null;
        } else {
            return $driverLoad->value;
        }
    }
    public function getNumOfDriverCallsAttribute()
    {
        return DriverCall::where('load_id', $this->id)->count();
    }

    public function getNumOfSelectedDriversAttribute()
    {
        // return 0;
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

    public function getOwnerAuthenticatedAttribute()
    {
        if (Owner::where('mobileNumber', $this->mobileNumberForCoordination)->where('isAccepted', 1)->count() > 0) {
            return true;
        } else {
            return false;
        }
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

    public function getDistanceCityAttribute()
    {
        try {
            $cityDistance = CityDistanceCalculate::where('fromCity_id', $this->origin_city_id)->where('toCity_id', $this->destination_city_id)->first();
            $cityDistanceReverse = CityDistanceCalculate::where('toCity_id', $this->origin_city_id)->where('fromCity_id', $this->destination_city_id)->first();
            if (isset($cityDistance->id))
                return $cityDistance->value;

            if (isset($cityDistanceReverse->id))
                return $cityDistanceReverse->value;

            return 0;
        } catch (Exception $exception) {
            //throw $th;
        }
    }

    public function getAvarageRateOwnerAttribute()
    {
        return Score::where('type', 'Driver')->where('owner_id', $this->user_id)->avg('value');
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
