<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use SoapClient;

class Owner extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $appends = [
        'numOfLoads',
        'moreDayLoad',
        'ratingOwner'
    ];

    public function operatorMessages()
    {
        return $this->hasMany(OperatorOwnerAuthMessage::class);
    }

    public function getNumOfLoadsAttribute()
    {
        return Load::where('user_id', $this->id)->where('userType', 'owner')->withTrashed()->count();
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
    public function acceptCustomerSmsIr($mobile)
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
                "templateId": 322376,
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

    public function rejectCustomerSms($mobile)
    {
        $client = new SoapClient("http://ippanel.com/class/sms/wsdlservice/server.php?wsdl");
        $user = "09184696188";
        $pass = "faraz3300131545";
        $fromNum = "+983000505";
        $toNum = array($mobile);
        $pattern_code = "rpncsb872e9qih3";
        $rand = rand(10000, 99999);
        $input_data = array("tell" => TELL);
        $client->sendPatternSms($fromNum, $toNum, $user, $pass, $pattern_code, $input_data);
        return $rand;
    }

    public function rejectCustomerSmsIr($mobile)
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
                "templateId": 277063,
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

    // از آخرین بار تا به امروز
    public function getMoreDayLoadAttribute()
    {
        $lastLoad = Load::where('userType', ROLE_OWNER)
            ->where('user_id', $this->id)
            ->withTrashed()
            ->orderByDesc('created_at')->first();
        $now = now();
        if ($lastLoad != null) {
            return $lastLoad->created_at->diff($now)->format("%a") ;
        }
    }

    public function getRatingOwnerAttribute()
    {
        return $score = Score::where('type', 'Driver')->where('owner_id', $this->id)->avg('value');
        if ($score === null) {
            return null;
        }else{
            return round($score,1);
        }
    }


    public function loads(): HasMany
    {
        return $this->hasMany(Load::class, 'user_id');
    }

    public function province()
    {
        return $this->belongsTo(ProvinceCity::class, 'province_id');
    }
}
