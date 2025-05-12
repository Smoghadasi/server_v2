<?php

namespace App\Models;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Load extends Model
{

    use SoftDeletes;
    private static $transportationCompany_id;
    protected $guarded = [];
    protected $appends = [
        'numOfRequestedDrivers',
        'numOfSelectedDrivers',
        'numOfDriverCalls',
        'numOfNearDriver',
        'numOfInquiryDrivers',
        'originCity',
        'originState',
        'destinationCity',
        'distanceCity',
        'ownerAuthenticated',
        'numOfRateDriver',
        'avarageRateOwner',
        'firstLoad',
    ];

    public function driver()
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

    public function getFirstLoadAttribute()
    {
        try {
            if ($this->userType === 'owner' && $owner = Owner::find($this->user_id)) {
                return Load::where('user_id', $owner->id)
                    ->whereDate('created_at', now()->toDateString())
                    ->exists();
            }
        } catch (\Exception $e) {
            Log::warning($e);
        }
        return false;
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


    public function getNumOfNearDriverAttribute()
    {
        if ($this->deleted_at) {
            return 0;
        }

        try {
            $latitude = $this->latitude;
            $longitude = $this->longitude;
            $fleets = FleetLoad::where('load_id', $this->id)->pluck('fleet_id');

            $haversine = "(6371 * acos(
                cos(radians(?)) * cos(radians(latitude)) *
                cos(radians(longitude) - radians(?)) +
                sin(radians(?)) * sin(radians(latitude))
            ))";

            // شعاع‌های جستجو به ترتیب
            $searchRadiuses = [50, 100, 120];

            foreach ($searchRadiuses as $radius) {
                // کلید یکتا برای هر مرحله از جستجو
                $cacheKey = "near_drivers_count_{$this->id}_{$latitude}_{$longitude}_{$radius}";

                // کش به مدت 5 دقیقه
                $count = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($latitude, $longitude, $radius, $fleets, $haversine) {
                    return Driver::whereNotNull('location_at')
                        ->where('location_at', '>=', now()->subMinutes(360))
                        ->whereIn('fleet_id', $fleets)
                        ->whereRaw("{$haversine} < ?", [$latitude, $longitude, $latitude, $radius])
                        ->count();
                });

                if ($count > 0) {
                    return $count;
                }
            }

            // اگر در هیچ شعاعی راننده‌ای پیدا نشد
            return 0;
        } catch (\Exception $e) {
            return 0;
        }
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


    public function getOriginStateAttribute()
    {
        return ProvinceCity::where('id', $this->origin_state_id)->first();
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

    public function firstLoad($mobile)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.sms.ir/v1/send/verify',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>
            '{' . '
                "mobile": ' .
                '"' . $mobile . '",
                "templateId": 538979,
                "parameters": [
                  {
                    "name": "TELL",
                    "value":' . ' " ' . TELL . '"' . '
                  }
                ]
              }',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Accept: text/plain',
                'x-api-key: QlDsnB6uLz3glijWOP02YcXiBAEjf06Hw5WOcRWovUGVESpJIPMkwRdcPRbEPPMj'
            ),
        ));

        curl_exec($curl);
        curl_close($curl);

        return true;
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
