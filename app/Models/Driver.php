<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use SoapClient;

class Driver extends Authenticatable
{
    protected $guarded = [];

    protected $appends = [
        'fleetTitle',
        'countOfPais',
        'countOfCalls',
        'operatorMessage',
        'blockedIp',
        'transactionCount',
        'ratingDriver'
    ];
    public function city()
    {
        return $this->hasOne(ProvinceCity::class, 'city_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'user_id')->where('userType', 'driver');
    }

    public function cityOwner()
    {
        return $this->belongsTo(ProvinceCity::class, 'city_id');
    }

    public function provinceOwner()
    {
        return $this->belongsTo(ProvinceCity::class, 'province_id');
    }

    //    public function loads()
    //    {
    //        return $this->belongsTo(Load::class);
    //    }

    public function sos()
    {
        return $this->hasMany(SOS::class);
    }

    public function getFleetTitleAttribute()
    {
        try {
            return Fleet::find($this->fleet_id)->title;
        } catch (\Exception $exception) {
        }

        return '';
    }

    public function getTransactionCountAttribute()
    {
        try {
            return Transaction::where('user_id', $this->id)->where('status', '>', 2)->count();
        } catch (\Exception $exception) {
        }

        return '';
    }

    public function freeCallDrivers()
    {
        return $this->hasMany(Driver::class);
    }

    public function resultOfContacting()
    {
        return $this->hasMany(ResultOfContactingWithDriver::class)->orderBy('id', 'desc');
    }

    public function getCountOfPaisAttribute()
    {
        return [
            'isPaid' => Transaction::where([['user_id', $this->id], ['status', '>', 2]])->count(),
            'unPaid' => Transaction::where([['user_id', $this->id], ['status', 0]])->count(),
        ];
    }

    public function getCountOfCallsAttribute()
    {
        return DriverCall::where('driver_id', $this->id)->count();
    }

    public function driverCalls(): HasMany
    {
        return $this->hasMany(DriverCall::class);
    }

    public function getOperatorMessageAttribute()
    {
        try {


            $operatorMessage = OperatorDriverAuthMessage::where([
                ['driver_id', $this->id],
                ['close', false]
            ])
                ->orderBy('id', 'desc')
                ->first();

            if (isset($operatorMessage->message))
                return $operatorMessage->message;
        } catch (\Exception $e) {
        }

        return '';
    }

    public function driverActivities()
    {
        return $this->hasMany(DriverActivity::class);
    }


    /**
     * @return bool
     */
    public function getBlockedIpAttribute(): bool
    {
        if (BlockedIp::where('user_id', $this->id)->where('userType', ROLE_DRIVER)->count())
            return true;

        return false;
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class);
    }

    public function freeSubscription($sms, $persian_date, $free_date)
    {
        $client = new SoapClient("http://ippanel.com/class/sms/wsdlservice/server.php?wsdl");
        $user = "09184696188";
        $pass = "faraz3300131545";
        $fromNum = "+983000505";
        $toNum = array($sms);
        $pattern_code = "xcj5cot5me6x7w5";
        $date = array('date' => $persian_date, 'expireDate' => $free_date);
        return $client->sendPatternSms($fromNum, $toNum, $user, $pass, $pattern_code, $date);
    }

    public function freeSubscriptionSmsIr($sms, $persian_date, $free_date)
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
                '"' . $sms . '",
                "templateId": 392467,
                "parameters": [
                  {
                    "name": "DATE",
                    "value":' . ' " ' . $persian_date . '"' . '
                  },
                  {
                    "name": "EXPIREDATE",
                    "value":' . ' " ' . $free_date . '"' . '
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
    public function freeSubscriptionGiftSmsIr($sms, $persian_date, $free_date)
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
                '"' . $sms . '",
                "templateId": 603567,
                "parameters": [
                  {
                    "name": "DATE",
                    "value":' . ' " ' . $persian_date . '"' . '
                  },
                  {
                    "name": "EXPIREDATE",
                    "value":' . ' " ' . $free_date . '"' . '
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

    public function bookmark()
    {
        return $this->morphOne(Bookmark::class, 'userable');
    }

    public function unSuccessPayment($mobile)
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
                "templateId": 580573,
                "parameters": [
                  {
                    "name": "TEL",
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

    // ارسال پیامک برای رانندگانی که سه روز از اشتراک باقی نمانده است
    public function sendDriverThreeDays($mobile)
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
                "templateId": 580573,
                "parameters": [
                  {
                    "name": "TEL",
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

    public function getRatingDriverAttribute()
    {
        return $score = Score::where('type', 'Owner')->where('driver_id', $this->id)->avg('value');
        if ($score === null) {
            return null;
        } else {
            return round($score, 1);
        }
    }

    public function driverVisitLoad(): HasOne
    {
        return $this->hasOne(DriverVisitLoad::class);
    }

    public function activities()
    {
        return $this->hasMany(DriverActivity::class, 'driver_id');
    }

    public function driverVisitLoads(): HasMany
    {
        return $this->hasMany(DriverVisitLoad::class);
    }
    //
    public function subscriptionLoadSmsIr($mobile, $driver, $from, $to)
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
                "templateId": 753360,
                "parameters": [
                  {
                    "name": "DRIVER",
                    "value":' . ' " ' . $driver . '"' . '
                  },
                  {
                    "name": "FROM",
                    "value":' . ' " ' . $from . '"' . '
                  },
                  {
                    "name": "TO",
                    "value":' . ' " ' . $to . '"' . '
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
}
