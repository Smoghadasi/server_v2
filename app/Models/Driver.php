<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use SoapClient;

class Driver extends Authenticatable
{

    protected $appends = ['fleetTitle', 'countOfPais', 'countOfCalls', 'operatorMessage','blockedIp'];
    public function city()
    {
        return $this->hasOne(City::class);
    }

    public function cityOwner()
    {
        return $this->belongsTo(CityOwner::class, 'city_id');
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

    public function resultOfContacting()
    {
        return $this->hasMany(ResultOfContactingWithDriver::class)->orderBy('id', 'desc');
    }

    public function getCountOfPaisAttribute()
    {
        return [
            'isPaid' => Transaction::where([['user_id', $this->id], ['status', '>', 0]])->count(),
            'unPaid' => Transaction::where([['user_id', $this->id], ['status', 0]])->count(),
        ];

    }

    public function getCountOfCallsAttribute()
    {
        return DriverCall::where('driver_id', $this->id)->count();
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


    /**
     * @return bool
     */
    public function getBlockedIpAttribute(): bool
    {
        if (BlockedIp::where('user_id', $this->id)->where('userType', ROLE_DRIVER)->count())
            return true;

        return false;
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
}
