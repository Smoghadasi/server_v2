<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\GroupNotification;
use App\Models\ManualNotificationRecipient;
use App\Models\Owner;
use Illuminate\Http\Request;

class ManualNotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // try {
        //     $manualNotifications = ManualNotificationRecipient::with(['userable' => function ($query) {
        //         $query->select(['id', 'name', 'lastName', 'mobileNumber']);
        //     }])
        //         ->where('userable_type', 'LIKE', "%$request->type%")
        //         ->paginate(20);
        //     return view('admin.manualNotification.index', compact('manualNotifications'));
        // } catch (\Exception $e) {
        //     return view('errors.404');
        // }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // مقدار خام از فرم (string JSON)
        $mobilesJson = $request->input('mobiles');

        // تبدیل به آرایه
        $mobilesArray = json_decode($mobilesJson, true);

        // گرفتن فقط شماره‌ها
        $numbers = array_column($mobilesArray ?? [], 'value');

        // اضافه کردن صفر اول اگر نداشت
        $numbers = array_map(function ($number) {
            $number = trim($number);
            if (!str_starts_with($number, '0')) {
                return '0' . $number;
            }
            return $number;
        }, $numbers);


        $group = GroupNotification::find($request->group_id);
        $model = $group->groupType === 'owner' ? Owner::class : Driver::class;

        // اگر گروه راننده باشه و هیچ شماره دستی نیاد
        if ($group->groupType === 'driver' && empty($numbers)) {

            $IdsDriver = Driver::query()
                ->when($request->fleets !== null, fn($query) => $query->whereIn('fleet_id', $request->fleets))
                ->when($request->provinces !== null, fn($query) => $query->whereIn('province_id', $request->provinces))
                ->take($request->count)
                ->pluck('id');

            foreach ($IdsDriver as $IdDriver) {
                if (ManualNotificationRecipient::where('userable_id', $IdDriver)->where('group_id', $request->group_id)->doesntExist()) {
                    ManualNotificationRecipient::create([
                        'userable_id' => $IdDriver,
                        'userable_type' => $model,
                        'group_id' => $request->group_id,
                    ]);
                }
            }

            return back()->with('success', 'کاربران مورد نظر ثبت شدند');
        }

        // 📌 اینجا برای شماره‌های دستی (آرایه‌ای)
        foreach ($numbers as $mobile) {
            $user = $model::where('mobileNumber', $mobile)->first();

            if (!$user) {
                // یکی از شماره‌ها پیدا نشد → می‌تونی ادامه بدی یا break کنی
                // اینجا تصمیم گرفتم ادامه بده ولی پیام danger هم بده
                continue;
            }

            if (ManualNotificationRecipient::where('userable_type', $model)
                ->where('userable_id', $user->id)
                ->where('group_id', $request->group_id)
                ->exists()
            ) {
                // کاربر تکراری → رد میشه
                continue;
            }

            ManualNotificationRecipient::create([
                'userable_id' => $user->id,
                'userable_type' => $model,
                'group_id' => $request->group_id,
            ]);
        }

        return back()->with('success', 'شماره‌های مورد نظر ثبت شدند');
    }



    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(ManualNotificationRecipient $manualNotification)
    {
        $manualNotification->delete();
        return back()->with('danger', 'با موفقیت حذف شد');
    }

    public function sendManualNotification(Request $request)
    {
        $group = GroupNotification::find($request->group_id);

        $userIds = ManualNotificationRecipient::where('group_id', $request->group_id)->pluck('userable_id');

        $fcm_tokens = ($group->groupType === 'driver' ? Driver::class : Owner::class)::whereIn('id', $userIds)->pluck('FCM_token');

        foreach ($fcm_tokens as $fcm_token) {
            $this->sendNotificationWeb($fcm_token, $request->title, $request->body);
        }
        return back()->with('success', 'با موفقیت ارسال شد');
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
