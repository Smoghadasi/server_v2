<?php

namespace App\Http\Controllers;

use App\Models\Bearing;
use App\Models\ComplaintDriver;
use App\Models\ComplaintTransportationCompany;
use App\Models\ComplaintCustomer;
use App\Models\ComplaintOwner;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Owner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ComplaintController extends Controller
{
    // انتقاد یا شکایت راننده از صاحب بار یا باربری
    public function storeComplaintDriver(Request $request, Driver $driver)
    {
        try {

            $complaintDriver = new ComplaintDriver();
            $complaintDriver->driver_id = $driver->id;
            $complaintDriver->title = $request->title;
            $complaintDriver->phoneNumber = 0;
            $complaintDriver->complaint = 'owner';
            $complaintDriver->message = $request->message;
            $complaintDriver->trackingCode = rand(10000, 99999);
            $complaintDriver->save();

            if (isset($complaintDriver->id))
                return [
                    'result' => true,
                    'data' => ['trackingCode' => $complaintDriver->trackingCode],
                    'message' => ''
                ];
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }

        return [
            'result' => false,
            'data' => null,
            'message' => 'خطا در ذخیره پیام! دوباره تلاش کنید'
        ];
    }

    // پیگیری انتقاد یا شکایت راننده از صاحب بار یا باربری
    public function getComplaintDriverResult(Request $request, Driver $driver)
    {
        try {

            $complaintDriver = ComplaintDriver::where([
                ['driver_id', $driver->id],
                ['trackingCode', $request->trackingCode]
            ])->first();

            if (isset($complaintDriver->id))
                return [
                    'result' => true,
                    'data' => ['complaintDriver' => $complaintDriver],
                    'message' => null
                ];
        } catch (\Exception $exception) {
            Log::emergency($exception->getMessage());
        }
        return [
            'result' => false,
            'data' => null,
            'message' => 'پیام مورد نظر یافت نشد!'
        ];
    }

    // لیست انقادات و شکایات رانندگان
    public function complaintsDriversList()
    {
        $complaintsDrivers = ComplaintDriver::with('driverApp')->orderByDesc('created_at')->paginate(20);
        return view('admin.complaintsDriversList', compact('complaintsDrivers'));
    }

    // لیست انقادات و شکایات راننده
    public function getComplaintDriver($driver)
    {
        $complaints = ComplaintDriver::where('driver_id', $driver)
            // ->select(['title', 'trackingCode', 'message', 'adminMessage'])
            ->orderByDesc('created_at')
            ->get();
        return response()->json($complaints, 200);
    }

    public function storeComplaintDriverAdminMessage(Request $request, ComplaintDriver $complaintDriver)
    {

        $complaintDriver->adminMessage = $request->adminMessage;
        $complaintDriver->save();

        try {
            $driverFCM_tokens = Driver::where('id', $complaintDriver->driver_id)
                ->whereNotNull('FCM_token')
                ->pluck('FCM_token');
            $title = 'ایران ترابر';
            $body = 'پاسخ تیکت ' . ' ( ' . $complaintDriver->trackingCode . ' ) ' . 'برای شما ارسال شد.';
            foreach ($driverFCM_tokens as $driverFCM_token) {
                $this->sendNotification($driverFCM_token, $title, $body, API_ACCESS_KEY_OWNER);
            }
        } catch (\Exception $exception) {
            Log::emergency("----------------------send notification complaintDriver-----------------------");
            Log::emergency($exception);
            Log::emergency("---------------------------------------------------------");
        }


        return back()->with('success', 'پاسخ مورد نظر ثبت شد');
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

    ///////////////////////////////////////////////////////////////////////////////////////////

    // انتقاد یا شکایت باربری از صاحب بار یا راننده
    public function storeComplaintTransportationCompany(Request $request, Bearing $transportationCompany)
    {
        try {

            $complaintTransportationCompany = new ComplaintTransportationCompany();
            $complaintTransportationCompany->transportationCompany_id = $transportationCompany->id;
            $complaintTransportationCompany->title = $request->title;
            $complaintTransportationCompany->phoneNumber = $request->phoneNumber;
            $complaintTransportationCompany->complaint = $request->complaint;
            $complaintTransportationCompany->message = $request->message;
            $complaintTransportationCompany->trackingCode = rand(10000, 99999);
            $complaintTransportationCompany->save();

            if (isset($complaintTransportationCompany->id))
                return [
                    'result' => true,
                    'data' => ['trackingCode' => $complaintTransportationCompany->trackingCode],
                    'message' => ''
                ];
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }

        return [
            'result' => false,
            'data' => null,
            'message' => 'خطا در ذخیره پیام! دوباره تلاش کنید'
        ];
    }

    // پیگیری انتقاد یا شکایت باربری از صاحب بار راننده
    public function getComplaintTransportationCompanyResult(Request $request, Bearing $transportationCompany)
    {
        try {

            $complaintTransportationCompany = ComplaintTransportationCompany::where([
                ['transportationCompany_id', $transportationCompany->id],
                ['trackingCode', $request->trackingCode]
            ])->first();

            if (isset($complaintTransportationCompany->id))
                return [
                    'result' => true,
                    'data' => ['complaintTransportationCompany' => $complaintTransportationCompany],
                    'message' => null
                ];
        } catch (\Exception $exception) {
            Log::emergency($exception->getMessage());
        }
        return [
            'result' => false,
            'data' => null,
            'message' => 'پیام مورد نظر یافت نشد!'
        ];
    }

    // لیست انقادات و شکایات رانندگان
    public function complaintsTransportationCompanyList()
    {
        $complaintsTransportationCompany = ComplaintTransportationCompany::orderby('id', 'desc')->paginate(20);

        return view('admin.complaintsTransportationCompanyList', compact('complaintsTransportationCompany'));
    }


    public function storeComplaintTransportationCompanyAdminMessage(Request $request, ComplaintTransportationCompany $complaintTransportationCompany)
    {
        $complaintTransportationCompany->adminMessage = $request->adminMessage;
        $complaintTransportationCompany->save();

        return back()->with('success', 'پاسخ مورد نظر ثبت شد');
    }

    ///////////////////////////////////////////////////////////////////////////////////////////

    // انتقاد یا شکایت صاحب بار
    public function storeComplaintCustomer(Request $request, Customer $customer)
    {
        try {

            $complaintCustomer = new ComplaintCustomer();
            $complaintCustomer->customer_id = $customer->id;
            $complaintCustomer->title = $request->title;
            $complaintCustomer->phoneNumber = $request->phoneNumber;
            $complaintCustomer->complaint = $request->complaint;
            $complaintCustomer->message = $request->message;
            $complaintCustomer->trackingCode = rand(10000, 99999);
            $complaintCustomer->save();

            if (isset($complaintCustomer->id))
                return [
                    'result' => true,
                    'data' => ['trackingCode' => $complaintCustomer->trackingCode],
                    'message' => ''
                ];
        } catch (\Exception $exception) {
            Log::emergency($exception->getMessage());
        }

        return [
            'result' => false,
            'data' => null,
            'message' => 'خطا در ذخیره پیام! دوباره تلاش کنید'
        ];
    }

    // پیگیری انتقاد یا شکایت صاحب بار
    public function getComplaintCustomerResult(Request $request, string $id)
    {
        $customer = Customer::findOrFail($id);
        try {

            $complaintCustomer = ComplaintCustomer::where([
                ['customer_id', $customer->id],
                ['trackingCode', $request->trackingCode]
            ])->first();

            if (isset($complaintCustomer->id))
                return [
                    'result' => true,
                    'data' => ['complaintCustomer' => $complaintCustomer],
                    'message' => null
                ];
        } catch (\Exception $exception) {
            Log::emergency($exception->getMessage());
        }
        return [
            'result' => false,
            'data' => null,
            'message' => 'پیام مورد نظر یافت نشد!'
        ];
    }

    // انتقاد یا شکایت صاحبان بار
    public function storeComplaintOwner(Request $request, Owner $owner)
    {
        try {

            $complaintOwner = new ComplaintOwner();
            $complaintOwner->owner_id = $owner->id;
            $complaintOwner->title = $request->title;
            $complaintOwner->phoneNumber = $request->phoneNumber;
            $complaintOwner->message = $request->message;
            $complaintOwner->trackingCode = rand(10000, 99999);
            $complaintOwner->save();

            if (isset($complaintOwner->id))
                return [
                    'result' => true,
                    'data' => ['trackingCode' => $complaintOwner->trackingCode],
                    'message' => ''
                ];
        } catch (\Exception $exception) {
            Log::emergency($exception->getMessage());
        }

        return [
            'result' => false,
            'data' => null,
            'message' => 'خطا در ذخیره پیام! دوباره تلاش کنید'
        ];
    }

    // پیگیری انتقاد یا شکایت صاحبان بار
    public function getComplaintOwnerResult(Request $request, Owner $owner)
    {
        try {
            $complaintOwner = ComplaintOwner::where([
                ['owner_id', $owner->id],
                ['trackingCode', $request->trackingCode]
            ])->first();

            if (isset($complaintOwner->id))
                return [
                    'result' => true,
                    'data' => ['complaintOwner' => $complaintOwner],
                    'message' => null
                ];
        } catch (\Exception $exception) {
            Log::emergency($exception->getMessage());
        }
        return [
            'result' => false,
            'data' => null,
            'message' => 'پیام مورد نظر یافت نشد!'
        ];
    }

    // لیست انقادات و شکایات صاحب بار
    public function complaintsCustomerList()
    {
        $complaintsCustomer = ComplaintCustomer::orderby('id', 'desc')->paginate(20);

        return view('admin.complaintsCustomerList', compact('complaintsCustomer'));
    }

    public function storeComplaintCustomerAdminMessage(Request $request, ComplaintCustomer $complaintCustomer)
    {
        $complaintCustomer->adminMessage = $request->adminMessage;
        $complaintCustomer->save();

        return back()->with('success', 'پاسخ مورد نظر ثبت شد');
    }

    // لیست انقادات و شکایات صاحبان بار
    public function complaintsOwnerList()
    {
        $complaintsOwnerLists = ComplaintOwner::orderbyDesc('created_at')->paginate(20);

        return view('admin.complaint.ownerList', compact('complaintsOwnerLists'));
    }

    public function storeComplaintOwnerAdminMessage(Request $request, ComplaintOwner $complaintOwner)
    {
        $complaintOwner->adminMessage = $request->adminMessage;
        $complaintOwner->save();

        return back()->with('success', 'پاسخ مورد نظر ثبت شد');
    }

    /******************************************************************************************/
    /******************************************************************************************/

    // لیست انتقادات و شکایات کاربر
    public function userCriticismOrComplaints()
    {
        $complaints = '';

        if (\auth('bearing')->check())
            $complaints = ComplaintTransportationCompany::where('transportationCompany_id', auth('bearing')->id())
                ->orderby('id', 'desc')
                ->paginate(20);
        else
            $complaints = ComplaintCustomer::where('customer_id', auth('customer')->id())
                ->orderby('id', 'desc')
                ->paginate(20);

        return view('users.criticismOrComplaints', compact('complaints'));
    }

    public function storeComplaintCustomerInWeb(Request $request)
    {
        $customer = Customer::find(auth('customer')->id());
        $result = $this->storeComplaintCustomer($request, $customer);

        if ($result['result'] == true)
            return back()->with('success', 'شکایت یا انتقاد شما ثبت شد.');

        return back()->with('danger', $result['message']);
    }

    public function storeComplaintTransportationCompanyInWeb(Request $request)
    {
        $transportationCompany = Bearing::find(auth('bearing')->id());
        $result = $this->storeComplaintTransportationCompany($request, $transportationCompany);

        if ($result['result'] == true)
            return back()->with('success', 'شکایت یا انتقاد شما ثبت شد.');

        return back()->with('danger', $result['message']);
    }
}
