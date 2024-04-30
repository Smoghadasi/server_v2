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

    public static function sendSMSWithPatternSmsir($to, $code)
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
                '"' . $to . '",
                "templateId": 841108,
                "parameters": [
                  {
                    "name": "Code",
                    "value":' . ' " ' . $code . '"' . '
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
