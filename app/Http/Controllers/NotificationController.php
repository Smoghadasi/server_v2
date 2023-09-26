<?php

namespace App\Http\Controllers;

use App\Models\Bearing;
use App\Models\Driver;
use Illuminate\Http\Request;

class NotificationController extends Controller
{


    private static function getApiAccessKey($userType)
    {
        if ($userType == 'bearing')
            return API_ACCESS_KEY_TRANSPORTATION_COMPANY;
        else if ($userType == 'driver')
            return API_ACCESS_KEY_DRIVER;
        else if ($userType == 'customer')
            return API_ACCESS_KEY_USER;
        return null;
    }

    public static function sendNotification($fcm_token, $data, $userType)
    {
        $API_ACCESS_KEY = self::getApiAccessKey($userType);

        $url = 'https://fcm.googleapis.com/fcm/send';
        $notification = [
            'body' => $data['body'],
            'sound' => true,
        ];
        $fields = array(
            'to' => $fcm_token,
            'notification' => $notification,
            'data' => $data
        );
        $headers = array(
            'Authorization: key=' . $API_ACCESS_KEY,
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        curl_close($ch);
    }


    // تغییر عملکر نوتیفیکیشن
    public function changeNotificationFunction(Request $request)
    {
        $userType = $request->userType;
        $function = $request->function;

        if ($function == 'enable' || $function == 'disable') {
            if ($userType == 'driver') {
                Driver::where('id', $request->driver_id)
                    ->update(['notification' => $function]);
                return [
                    'result' => SUCCESS
                ];
            }
            else if ($userType == 'bearing') {
                Bearing::where('id', $request->bearing_id)
                    ->update(['notification' => $function]);
                return [
                    'result' => SUCCESS
                ];
            }
        }

        return [
            'result' => UN_SUCCESS,
            'message' => 'شما مجاز به انجام این عملیات نیستید'
        ];
    }
}
