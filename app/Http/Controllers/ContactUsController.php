<?php

namespace App\Http\Controllers;

use App\Models\ContactUs;
use App\Models\Driver;
use App\Models\Load;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContactUsController extends Controller
{


    // ارسال پیام
    public function sendMessage(Request $request)
    {
        $name = $request->name;
        $lastName = $request->lastName;

        if ($request->role == ROLE_DRIVER) {
            $driver = Driver::where('mobileNumber', $request->mobileNumber)->first();
            if (isset($driver->name)) {
                $name = $driver->name;
                $lastName = $driver->lastName;
            }
        }


        $contactUs = new ContactUs();
        $contactUs->title = $request->title;
        $contactUs->message = $request->message;
        $contactUs->name = $name;
        $contactUs->lastName = $lastName;
        $contactUs->mobileNumber = $request->mobileNumber;
        $contactUs->role = $request->role;
        $contactUs->email = $request->email;
        $contactUs->save();

        if ($contactUs) {
            return ['result' => SUCCESS];
        }

        return ['result' => UN_SUCCESS];
    }

    // ارسال پیام
    public function sendMessageInWeb(Request $request)
    {
        $mobileNumber = $request->mobileNumber;

        $title = isset($request->title) ? $request->title : null;
        $email = isset($request->email) ? $request->email : null;
        $lastName = isset($request->lastName) ? $request->lastName : null;
        $message = $request->message;
        $name = $request->name;


        $contactUs = new ContactUs();
        $contactUs->title = $title;
        $contactUs->message = $message;
        $contactUs->name = $name;
        $contactUs->mobileNumber = $mobileNumber;
        $contactUs->email = $email;
        $contactUs->lastName = $lastName;
        $contactUs->save();

        if ($contactUs) {
            return ['result' => SUCCESS];
        }

        return ['result' => UN_SUCCESS];
    }

    // نمایش پیام ها
    public function messages()
    {
        $messages = ContactUs::whereIn('role', ['owner', 'driver'])
            ->where('parent_id', null)
            ->orderby('id', 'desc')
            ->paginate(20);
        return view('admin.message.index', compact('messages'));
    }

    // نمایش پیام ها رانندگان
    public function driverMessages($mobileNumber)
    {
        $messages = ContactUs::where('role', 'driver')
            ->where('mobileNumber', $mobileNumber)
            ->get();
        if ($messages->isEmpty())
            return response()->json('Empty', 404);
        else
            return response()->json($messages, 200);
    }

    // ارسال امتیاز و نظر از طرف صاحب بار برای باربری
    public function sendScoreAndCommentToLoadFromCustomer(Request $request)
    {
        $load_id = $request->load_id;
        $score = $request->score;
        $comment = $request->comment;

        Load::where('id', $load_id)
            ->update([
                'score' => $score,
                'comment' => $comment
            ]);

        return [
            'result' => 1,
            'message' => 'امتیاز شما ثبت شد'
        ];
    }

    // تغییر وضعیت به خوانده شده
    public function changeMessageStatus(ContactUs $contactUs, Request $request)
    {
        $contact = new ContactUs();
        $contact->status = true;
        $contact->parent_id = $contactUs->id;
        $contact->role = 'operator';
        $contact->result = strlen($request->result) ? $request->result : "نتیجه ای ثبت نشده!";
        $contact->save();

        $contactUs->status = true;
        $contactUs->save();

        if ($request->notification == 'on') {
            try {
                if ($contactUs->role == 'driver') {
                    $driverFCM_tokens = Driver::where('mobileNumber', $contactUs->mobileNumber)
                        ->whereNotNull('FCM_token')
                        ->pluck('FCM_token');
                    $title = 'ایران ترابر';
                    $body = 'پاسخ تیکت ' . 'برای شما ارسال شد.';
                    foreach ($driverFCM_tokens as $driverFCM_token) {
                        $this->sendNotification($driverFCM_token, $title, $body, API_ACCESS_KEY_OWNER);
                    }
                }
            } catch (\Exception $exception) {
                Log::emergency("----------------------send notification changeMessageStatus-----------------------");
                Log::emergency($exception);
                Log::emergency("---------------------------------------------------------");
            }
        }


        return back()->with('success', 'وضعیت پیام مورد نظر به خوانده شده تغییر و نتیجه پیگیری ثبت شد.');
    }

    public function show(ContactUs $contactUs)
    {
        $contactUses = ContactUs::with('childrenRecursive')
            ->where('parent_id', null)
            ->whereId($contactUs->id)
            ->first();
        // return $contactUses;
        return view('admin.message.show', compact('contactUses'));
    }

    private function sendNotification($FCM_token, $title, $body)
    {

        $serviceAccountPath = asset('assets/zarin-tarabar-firebase-adminsdk-9x6c3-7dbc939cac.json');
        $serviceAccount = json_decode(file_get_contents($serviceAccountPath), true);

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


    public function removeMessage(ContactUs $contactUs)
    {
        $contactUs->delete();

        return back()->with('success', ' پیام مورد نظر حذف شد.');
    }
}
