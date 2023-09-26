<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SoapClient;
use SoapFault;

class SMSController extends Controller
{
    public static function send($to, $text, $type = 1)
    {

        $input_data = array(
            'verification_code' => $text
        );
        ini_set("soap.wsdl_cache_enabled", "0");
        try {
            $client = new \SoapClient("http://ippanel.com/class/sms/wsdlservice/server.php?wsdl");
            $user = "09184696188";
            $pass = "faraz3300131545";
            $fromNum = "3000505";
            $toNum = array($to);

            $response = $client->sendPatternSms($fromNum, $toNum, $user, $pass, "d56ihyv8qu", $input_data);

            return $response;

        } catch (SoapFault $ex) {
            return $ex->faultstring;
        }

    }

    public static function sendSMSWithPattern($to, $pattern, $input_data)
    {


        ini_set("soap.wsdl_cache_enabled", "0");
        try {
            $client = new \SoapClient("http://ippanel.com/class/sms/wsdlservice/server.php?wsdl");
            $user = "09184696188";
            $pass = "faraz3300131545";
            $fromNum = "3000505";
            $toNum = array($to);

            $response = $client->sendPatternSms($fromNum, $toNum, $user, $pass, $pattern, $input_data);

            return $response;

        } catch (SoapFault $ex) {
            return $ex->faultstring;
        }

    }
}
