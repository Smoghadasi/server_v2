<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        return round(Score::where('type', 'Driver')->where('owner_id', $this->id)->avg('value'),1);
    }
}
