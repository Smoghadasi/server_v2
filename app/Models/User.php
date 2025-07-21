<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use SoapClient;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use SoftDeletes;


    protected $fillable = ['name', 'email', 'password', 'access', 'last_seen'];
    protected $hidden = ['password', 'remember_token', 'storeCargoOperators'];



    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function getNumOfAllLoadsAttribute()
    {
        return Load::where('operator_id', $this->id)->count();
    }

    public function getNumOfTodayLoadsAttribute()
    {
        return Load::where([
            ['operator_id', $this->id],
            ['created_at', '>', date('Y-m-d') . ' 00:00:00']
        ])->count();
    }

    public function getCountOfFleetsInTodayAttribute()
    {
        try {
            $loads = Load::where([
                ['operator_id', $this->id],
                ['created_at', '>', date('Y-m-d') . ' 00:00:00']
            ])->pluck('id');

            return FleetLoad::whereIn('load_id', $loads)
                ->select('numOfFleets', 'fleet_id')
                ->selectRaw("SUM(numOfFleets) as numOfFleets")
                ->groupBy('fleet_id', 'numOfFleets')
                ->get();
        } catch (\Exception $exception) {
        }
        return [];
    }

    public function storeCargoOperators(): HasMany
    {
        return $this->hasMany(StoreCargoOperator::class);
    }

    public function getCountOfFleetsInAllAttribute()
    {
        try {
            return FleetLoad::whereIn('load_id', Load::where('operator_id', $this->id)->pluck('id'))
                ->select('numOfFleets', 'fleet_id')
                ->selectRaw("SUM(numOfFleets) as numOfFleets")
                ->groupBy('fleet_id', 'numOfFleets')
                ->get();
        } catch (\Exception $exception) {
        }
        return [];
    }

    public function getNumOfThisWeekLoadsAttribute()
    {

        return Load::where([
            ['operator_id', $this->id],
            ['created_at', '>', getCurrentWeekSaturdayDate()]
        ])->count();

    }

    public function getCountOfFleetsInThisWeekAttribute()
    {
        try {

            $load_id = Load::where([
                ['operator_id', $this->id],
                ['created_at', '>', getCurrentWeekSaturdayDate()]
            ])->pluck('id');

            return FleetLoad::whereIn('load_id', $load_id)
                ->select('numOfFleets', 'fleet_id')
                ->selectRaw("SUM(numOfFleets) as numOfFleets")
                ->groupBy('fleet_id', 'numOfFleets')
                ->get();
        } catch (\Exception $exception) {
        }
        return [];
    }

    public function getUserAccessAttribute()
    {
        return explode(',', $this->access);
    }

    public function getUserActivityReportAttribute()
    {
        return UserActivityReport::where([
            ['created_at', '>', date('Y-m-d', time()) . ' 00:00:00'],
            ['user_id', $this->id]
        ])->count();
    }

    public function getCargoAccessAttribute()
    {
        try {
             $operatorCargoListAccess= OperatorCargoListAccess::where('user_id', $this->id)->select('fleet_id')->pluck('fleet_id')->toArray();
             if (count($operatorCargoListAccess))
                 return  $operatorCargoListAccess;
        } catch (\Exception $exception) {

        }

        return [];
    }

    // سامانه پیامکی FarazSMS
    public function mobileSms($sms)
    {
        $client = new SoapClient("http://ippanel.com/class/sms/wsdlservice/server.php?wsdl");
        $user = "09184696188";
        $pass = "faraz3300131545";
        $fromNum = "+983000505";
        $toNum = array($sms);
        $pattern_code = "uazjh2qxqy7eb06";
        $rand = rand(10000, 99999);
        $input_data = array("verification_code" => $rand);
        $client->sendPatternSms($fromNum, $toNum, $user, $pass, $pattern_code, $input_data);
        return $rand;
    }

    // سامانه پیامکی SMS.ir
    public function mobileSmsIr($sms)
    {
        $curl = curl_init();
        $rand = rand(10000, 99999);
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
                '"' . $sms . '",
                "templateId": 841108,
                "parameters": [
                  {
                    "name": "Code",
                    "value":' . ' " ' . $rand . '"' . '
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

        return $rand;
    }

    public function loginHistory()
    {
        return $this->hasMany(LoginHistory::class);
    }
}
