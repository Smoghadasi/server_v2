<?php

namespace App\Jobs;

use App\Models\Driver;
use App\Models\FleetLoad;
use App\Models\ProvinceCity;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
// use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendNotificationForNearDriver implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $load;
    protected $radius;

    public function __construct($load, $radius)
    {
        $this->load = $load;
        $this->radius = $radius;
    }

    public function handle()
    {
        $load = $this->load;
        $radius = $this->radius;
        $latitude = $load->latitude;
        $longitude = $load->longitude;
        $fleets = FleetLoad::where('load_id', $load->id)->pluck('fleet_id');
        $cityFrom = ProvinceCity::where('id', $load->origin_city_id)->first();
        $cityTo = ProvinceCity::where('id', $load->destination_city_id)->first();

        $haversine = "(6371 * acos(cos(radians({$latitude}))
            * cos(radians(latitude))
            * cos(radians(longitude) - radians({$longitude}))
            + sin(radians({$latitude}))
            * sin(radians(latitude))))";

        try {
            $driverFCM_tokens = Driver::select('FCM_token')
                ->whereNotNull('location_at')
                ->where('location_at', '>=', Carbon::now()->subMinutes(120))
                ->whereIn('fleet_id', $fleets)
                ->where('version', '>', 58)
                ->where('province_id', $cityFrom->parent_id)
                ->havingRaw("{$haversine} < ?", [$radius])
                ->pluck('FCM_token');

            $title = 'ایران ترابر رانندگان';
            $body = ' بار ' . ' از ' . $cityFrom->name . ' به ' . $cityTo->name;
            foreach ($driverFCM_tokens as $driverFCM_token) {
                // dispatch(new SendNotificationJob($driverFCM_token, $title, $body));
                $this->sendNotificationWeb($driverFCM_token, $title, $body, API_ACCESS_KEY_OWNER);
            }
        } catch (\Exception $exception) {
            Log::emergency("----------------------send notification load by driver-----------------------");
            Log::emergency($exception);
            Log::emergency("---------------------------------------------------------");
        }
    }

    private function sendNotificationWeb($FCM_token, $title, $body)
    {
        $serviceAccountPath = base_path('public/assets/zarin-tarabar-firebase-adminsdk-9x6c3-7dbc939cac.json');
        $serviceAccountJson = file_get_contents($serviceAccountPath);
        $serviceAccount = json_decode($serviceAccountJson, true);

        $clientEmail = $serviceAccount['client_email'];
        $privateKey = $serviceAccount['private_key'];
        $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
        $now = time();
        $expiration = $now + 3600;
        $payload = json_encode([
            'iss' => $clientEmail,
            'scope' => 'https://www.googleapis.com/auth/cloud-platform https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $expiration,
            'iat' => $now
        ]);

        // Encode to base64
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        // Create the signature
        $signatureInput = $base64UrlHeader . "." . $base64UrlPayload;
        openssl_sign($signatureInput, $signature, $privateKey, 'sha256');
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        // Create the JWT
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
        // Exchange JWT for an access token
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]));
        $response = curl_exec($ch);
        curl_close($ch);
        $responseData = json_decode($response, true);
        $accessToken = $responseData['access_token'];

        $url = 'https://fcm.googleapis.com/v1/projects/zarin-tarabar/messages:send';
        $notification = [
            "message" => [
                "token" => $FCM_token,
                "notification" => [
                    "title" => $title,
                    "body" => $body
                ]
                // "webpush" => [
                //     "fcm_options" => [
                //         "link" => "https://cargo.iran-tarabar.com/780849"
                //     ]
                // ]
            ],
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($notification));
        curl_exec($ch);
        curl_close($ch);
    }
}
