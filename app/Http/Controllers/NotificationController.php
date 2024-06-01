<?php

namespace App\Http\Controllers;

use App\Models\Bearing;
use App\Models\Driver;
use App\Models\DriverActivity;
use App\Models\Owner;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

    public function sendCustomMessage(Request $request)
    {
        try {

            $driverFCM_tokens = Driver::where('version', 58)->whereHas('driverActivities', function ($q) {
                $q->where('created_at', '<=', Carbon::now()->subDays(1)->toDateTimeString());
            })->pluck('FCM_token');
            foreach ($driverFCM_tokens as $driverFCM_token) {
                $data = [
                    'title' => $request->title,
                    'body' => $request->body,
                    'notificationType' => 'newLoad',
                ];
                $this->sendNotification($driverFCM_token, $data, API_ACCESS_KEY_DRIVER);
            }

            return response()->json('OK', 200);
        } catch (\Exception $e) {
            //throw $th;
        }
    }


    private function sendNotification($FCM_token, $data, $API_ACCESS_KEY)
    {
        $url = 'https://fcm.googleapis.com/fcm/send';
        $notification = [
            'body' => $data['body'],
            'sound' => true,
        ];
        $fields = array(
            'registration_ids' => $FCM_token,
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
        if ($result === FALSE) {
            Log::emergency("------------------------------------------------------------");
            Log::emergency("notificationErrors : " . curl_error($ch));
            Log::emergency("------------------------------------------------------------");
        }
        curl_close($ch);
    }


    // تغییر عملکر نوتیفیکیشن
    public function changeNotificationFunction(Request $request)
    {
        $userType = $request->userType;
        $notification = $request->notification;

        if ($userType == 'driver') {
            Driver::where('id', $request->driver_id)
                ->update(['notification' => $notification]);
            return [
                'result' => SUCCESS
            ];
        } else if ($userType == 'owner') {
            Owner::where('id', $request->owner_id)
                ->update(['notification' => $notification]);
            return [
                'result' => SUCCESS
            ];
        }

        return [
            'result' => UN_SUCCESS,
            'message' => 'شما مجاز به انجام این عملیات نیستید'
        ];
    }
}
