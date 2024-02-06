<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use SoapClient;

class Customer extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = ['mobileNumber', 'name', 'lastName', 'nationalCode', 'status'];
    protected $hidden = ['password',  'remember_token'];
    protected $append = ['blockedIp'];

    public function legalPersonality()
    {
        return $this->hasOne(LegalPersonality::class);
    }

    /**
     * @return bool
     */
    public function getBlockedIpAttribute(): bool
    {
        if (BlockedIp::where('user_id', $this->id)->where('userType', ROLE_CUSTOMER)->count())
            return true;

        return false;
    }

    public function acceptCustomerSms($mobile)
    {
        $client = new SoapClient("http://ippanel.com/class/sms/wsdlservice/server.php?wsdl");
        $user = "09184696188";
        $pass = "faraz3300131545";
        $fromNum = "+983000505";
        $toNum = array($mobile);
        $pattern_code = "vw1a1y5nsom0xye";
        $rand = rand(10000, 99999);
        $input_data = array("tell" => TELL);
        $client->sendPatternSms($fromNum, $toNum, $user, $pass, $pattern_code, $input_data);
        return $rand;
    }
}
