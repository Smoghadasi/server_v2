<?php

namespace App\Http\Controllers;

use App\Models\AppVersion;
use App\Models\CargoConvertList;
use App\Models\City;
use App\Models\CityOwner;
use App\Models\ComplaintDriver;
use App\Models\Driver;
use App\Models\DriverActivity;
use App\Models\DriverCall;
use App\Models\DriverCallCount;
use App\Models\DriverCallReport;
use App\Models\DriverDefaultPath;
use App\Models\Fleet;
use App\Models\FreeSubscription;
use App\Models\Load;
use App\Models\OperatorDriverAuthMessage;
use App\Models\Owner;
use App\Models\ProvinceCity;
use App\Models\ReportDriver;
use App\Models\ResultOfContactingWithDriver;
use App\Models\Setting;
use App\Models\State;
use App\Models\Transaction;
use Exception;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SoapClient;

class DriverController extends Controller
{

    // لیست رانندگان
    public function drivers($drivers = [], $showSearchResult = false)
    {
        // $fleets = Fleet::all();
        if (!$showSearchResult)
            $drivers = Driver::orderBy('id', 'desc')->paginate(50);

        return view('admin.drivers', compact('drivers', 'showSearchResult'));
    }

    // لیست رانندگان برای ادمین
    public function adminDrivers($drivers = [], $showSearchResult = false)
    {
        if (Auth::user()->role == 'admin') {
            $fleets = Fleet::all();
            if (!$showSearchResult)
                $drivers = Driver::orderBy('id', 'desc')->paginate(50);
            return view('admin.driver.adminDrivers', compact('drivers', 'showSearchResult', 'fleets'));
        } else {
            return response()->view("errors.404");
        }
    }


    // فرم افزودن راننده جدید
    public function addNewDriverForm($message = '', $alert = '')
    {
        $fleets = Fleet::where('parent_id', '>', 0)->get();
        $cities = City::all();
        return view('admin.addNewDriverForm', compact('fleets', 'cities', 'message', 'alert'));
    }

    // افزودن راننده جدید
    public function addNewDriver(Request $request)
    {

        $message = 'حطا در ثبت راننده جدید';
        $alert = 'alert-danger';

        $smartCode = $request->smartCode;
        $nationalCode = $request->nationalCode;
        $name = $request->name;
        $lastName = $request->lastName;
        $fatherName = $request->fatherName;
        $birthDate = $request->birthDate;
        $cardNumber = $request->cardNumber;
        $cardPublishDate = $request->cardPublishDate;
        $applicator_city_id = $request->applicator_city_id;
        $drivingLicence = $request->drivingLicence;
        $receipt_card_city_id = $request->receipt_card_city_id;
        $counter = $request->counter;
        $docNumber = $request->docNumber;
        $inquiryDate = $request->inquiryDate;
        $mobileNumber = $request->mobileNumber;
        $degreeOfEdu = $request->degreeOfEdu;
        $driverType = $request->driverType;
        $insuranceCode = $request->insuranceCode;
        $city_id = $request->city_id;
        $validityDate = $request->validityDate;
        $distances = $request->distances;
        $fleet_id = $request->fleet_id;
        $pic = $request->file('pic');

        $driver = new Driver();
        $driver->smartCode = $smartCode;
        $driver->nationalCode = $nationalCode;
        $driver->name = $name;
        $driver->lastName = $lastName;
        $driver->fatherName = $fatherName;
        if ($birthDate)
            $driver->birthDate = $birthDate;
        if ($cardNumber)
            $driver->cardNumber = $cardNumber;
        if ($cardPublishDate)
            $driver->cardPublishDate = $cardPublishDate;
        if ($applicator_city_id)
            $driver->applicator_city_id = $applicator_city_id;
        if ($drivingLicence)
            $driver->drivingLicence = $drivingLicence;
        if ($receipt_card_city_id)
            $driver->receipt_card_city_id = $receipt_card_city_id;
        if ($counter)
            $driver->counter = $counter;
        if ($docNumber)
            $driver->docNumber = $docNumber;
        if ($inquiryDate)
            $driver->inquiryDate = $inquiryDate;
        $driver->mobileNumber = $mobileNumber;
        if ($degreeOfEdu)
            $driver->degreeOfEdu = $degreeOfEdu;
        if ($driverType)
            $driver->driverType = $driverType;
        if ($insuranceCode)
            $driver->insuranceCode = $insuranceCode;
        if ($city_id)
            $driver->city_id = $city_id;
        if ($validityDate)
            $driver->validityDate = $validityDate;
        if ($distances)
            $driver->distances = $distances;
        if ($fleet_id)
            $driver->fleet_id = $fleet_id;
        $driver->pic = $this->savePicOfUsers($pic);

        if (isDriverAutoActive())
            $driver->status = ACTIVE;
        // خاور و نیسان
        $driver->freeCalls = DRIVER_FREE_CALLS;

        $driver->freeAcceptLoads = DRIVER_FREE_ACCEPT_LOAD;

        $driver->activationDate = (time() - (23 * 24 * 3600));
        $driver->save();

        if ($driver) {
            $message = 'راننده مورد نظر ثبت شد';
            $alert = 'alert-success';
        }
        return $this->addNewDriverForm($message, $alert);
    }

    // فرم ویرایش اطلاعات راننده
    public function editDriverForm(Driver $driver)
    {
        try {

            $fleets = Fleet::where('parent_id', '>', 0)->get();
            $cities = City::all();

            $operatorDriverAuthMessages = OperatorDriverAuthMessage::where('driver_id', $driver->id)
                ->orderBy('id', 'desc')
                ->get();

            return view('admin.editDriverForm', compact('driver', 'fleets', 'cities', 'operatorDriverAuthMessages'));
        } catch (Exception $exception) {
            return $exception->getMessage();
        }
    }

    public function ipAddress(string $ip)
    {
        $ipDriver = Driver::findOrFail($ip);
        return [
            'result' => $ipDriver->ip
        ];
    }

    // ویرایش اطلاعات راننده
    public function editDriver(Driver $driver, Request $request)
    {
        try {
            $driver->smartCode = $request->smartCode;
            $driver->nationalCode = $request->nationalCode;
            $driver->name = $request->name;
            $driver->lastName = $request->lastName;
            $driver->fatherName = $request->fatherName;
            $driver->birthDate = $request->birthDate;
            $driver->cardNumber = $request->cardNumber;
            $driver->cardPublishDate = $request->cardPublishDate;
            $driver->applicator_city_id = $request->applicator_city_id;
            $driver->drivingLicence = $request->drivingLicence;
            $driver->receipt_card_city_id = $request->receipt_card_city_id;
            $driver->counter = $request->counter;
            $driver->docNumber = $request->docNumber;
            $driver->inquiryDate = $request->inquiryDate;
            $driver->mobileNumber = $request->mobileNumber;
            $driver->degreeOfEdu = $request->degreeOfEdu;
            $driver->driverType = $request->driverType;
            $driver->insuranceCode = $request->insuranceCode;
            $driver->city_id = $request->city_id;
            $driver->validityDate = $request->validityDate;
            $driver->distances = $request->distances;
            $driver->fleet_id = $request->fleet_id;
            $driver->address = $request->address;
            $driver->vehicleLicensePlatePartA = $request->vehicleLicensePlatePartA;
            $driver->vehicleLicensePlatePartB = $request->vehicleLicensePlatePartB;
            $driver->vehicleLicensePlatePartC = $request->vehicleLicensePlatePartC;
            $driver->vehicleLicensePlatePartD = $request->vehicleLicensePlatePartD;
            $driver->pic = $this->savePicOfUsers($request->file('pic'));

            if ($request->file('driverImage'))
                $driver->driverImage = $this->storePicOfDriver($request->file('driverImage'), "driverImage", $driver);
            if ($request->file('nationalCardImage'))
                $driver->nationalCardImage = $this->storePicOfDriver($request->file('nationalCardImage'), "nationalCardImage", $driver);
            if ($request->file('carSmartCardImage'))
                $driver->carSmartCardImage = $this->storePicOfDriver($request->file('carSmartCardImage'), "carSmartCardImage", $driver);
            if ($request->file('driverSmartCardImage'))
                $driver->driverSmartCardImage = $this->storePicOfDriver($request->file('driverSmartCardImage'), "driverSmartCardImage", $driver);
            if ($request->file('authImage'))
                $driver->authImage = $this->storePicOfDriver($request->file('authImage'), "authImage", $driver);
            if ($request->file('imageAddressDoc'))
                $driver->imageAddressDoc = $this->storePicOfDriver($request->file('imageAddressDoc'), "imageAddressDoc", $driver);
            if ($request->file('imageRegisterSana'))
                $driver->imageRegisterSana = $this->storePicOfDriver($request->file('imageRegisterSana'), "imageRegisterSana", $driver);

            $driver->updateDateTime = null;
            if (
                $driver->name !== null &&
                $driver->lastName !== null &&
                $driver->nationalCode !== null &&
                $driver->mobileNumber !== null &&
                $driver->vehicleLicensePlatePartA !== null &&
                $driver->vehicleLicensePlatePartB !== null &&
                $driver->vehicleLicensePlatePartC !== null &&
                $driver->vehicleLicensePlatePartD !== null &&
                $driver->address !== null &&
                $driver->nationalCardImage !== null &&
                $driver->authImage !== null
            ) {
                $driver->authLevel = DRIVER_AUTH_SILVER_PENDING;
            }
            if (
                $driver->name !== null &&
                $driver->lastName !== null &&
                $driver->nationalCode !== null &&
                $driver->mobileNumber !== null &&
                $driver->vehicleLicensePlatePartA !== null &&
                $driver->vehicleLicensePlatePartB !== null &&
                $driver->vehicleLicensePlatePartC !== null &&
                $driver->vehicleLicensePlatePartD !== null &&
                $driver->address !== null &&
                $driver->nationalCardImage !== null &&
                $driver->authImage !== null &&
                $driver->imageAddressDoc !== null &&
                $driver->imageRegisterSana !== null
            ) {
                $driver->authLevel = DRIVER_AUTH_GOLD_PENDING;
            }
            $driver->save();

            try {

                OperatorDriverAuthMessage::where('driver_id', $driver->id)
                    ->update(['close' => true]);

                if (strlen($request->operatorMessage) > 0) {
                    $operatorDriverAuthMessage = new OperatorDriverAuthMessage();
                    $operatorDriverAuthMessage->driver_id = $driver->id;
                    $operatorDriverAuthMessage->user_id = \auth()->id();
                    $operatorDriverAuthMessage->message = $request->operatorMessage;
                    $operatorDriverAuthMessage->save();
                }
            } catch (Exception $exception) {
            }

            return back()->with('success', 'اطلاعات راننده با موفقیت بروز رسانی شد');
        } catch (Exception $e) {
            Log::emergency($e->getMessage());
            return $e->getMessage();
        }
        return back()->with('danger', 'خطادر ذخیره اطلاعات راننده!');
    }

    // درخواست اطلاعات پروفایل
    public function requestProfileInfo($id)
    {
        $driver = Driver::where('id', $id)->first();

        if ($driver) {
            return [
                'result' => SUCCESS,
                'driver' => $driver
            ];
        }
        return [
            'result' => UN_SUCCESS,
            'message' => 'چنین راننده ای وجود ندارد'
        ];
    }

    // درخواست تغییر ناوگان
    public function requestChangeFleet(Request $request)
    {
        Driver::where('id', $request->driver_id)
            ->update(['fleet_id' => $request->fleet_id]);

        $driver = Driver::where('id', $request->driver_id)
            ->select('id', 'fleet_id')
            ->first();

        return [
            'data' => [
                'fleet_id' => $driver->fleet_id,
                'fleetTitle' => $driver->fleetTitle,
            ]
        ];
    }

    // تایید حمل بار توسط راننده
    public function confirmLoad(Request $request)
    {
        $driver_id = $request->driver_id;
        $load_id = $request->load_id;

        Load::where('id', $load_id)
            ->update(['driver_id' => $driver_id]);

        return [
            'result' => SUCCESS
        ];
    }

    // درخواست لیست بار جدید در نوتیفیکیشن
    public function requestNewLoadsNotification($driver_id)
    {
    }

    // افزودن مسیر
    public function addPath(Request $request)
    {

        if ($request->origin_city_id == $request->destination_city_id) {
            return [
                'result' => UN_SUCCESS,
                'message' => 'مبدا و مقصد نباید مساوی باشند'
            ];
        }

        $check = DriverDefaultPath::where([
            ['driver_id', $request->driver_id],
            ['origin_city_id', $request->origin_city_id],
            ['destination_city_id', $request->destination_city_id]
        ])->count();

        // قبلا این مسیر ثبت شده است
        if ($check > 0) {

            return [
                'result' => UN_SUCCESS,
                'message' => 'قبلا این مسیر را ثبت کرده اید'
            ];
        }

        $driverPath = new DriverDefaultPath();
        $driverPath->driver_id = $request->driver_id;
        $driverPath->origin_city_id = $request->origin_city_id;
        $driverPath->destination_city_id = $request->destination_city_id;
        $driverPath->save();

        return [
            'result' => SUCCESS
        ];
    }

    // درخواست اطلاعات راننده انتخاب شده
    public function requestDriverInfo(Request $request)
    {
        $driver_id = $request->driver_id;
        $load_id = $request->load_id;

        $driver = Driver::where('id', '=', $driver_id)
            ->select('name', 'lastName', 'mobileNumber', 'pic')
            ->first();

        if ($driver) {
            return [
                'result' => SUCCESS,
                'driver' => $driver,
                'report' => ReportDriver::where([['load_id', $load_id], ['driver_id', $driver_id]])->count()
            ];
        }
        return [
            'result' => UN_SUCCESS,
            'message' => 'چنین راننده ای وجود ندارد'
        ];
    }

    public function requestDriverProfileInfo($id)
    {
        $driver = Driver::where('id', '=', $id)->first();


        if ($driver) {

            $driver->fleet = FleetController::getFleetName($driver->fleet_id);

            return [
                'result' => SUCCESS,
                'driver' => $driver,
            ];
        }
        return [
            'result' => UN_SUCCESS,
            'message' => 'چنین راننده ای وجود ندارد'
        ];
    }

    // ذخیره عکس کابر
    private function savePicOfUsers($picture)
    {
        $picName = 'user.png';
        if (strlen($picture)) {
            $fileType = $picture->guessClientExtension();
            if ($picture->isValid() && ($fileType == 'jpg' || $fileType == 'jpeg' || $fileType == 'gif' || $fileType == 'png' || $fileType == 'bmp')) {
                $picName = sha1(time()) . "." . $fileType;
                $picture->move('pictures/drivers', $picName);
            }
        }
        return 'pictures/drivers/' . $picName;
    }

    // ذخیره عکس کابر
    private function storePicOfDriver($picture, $type, $driver)
    {
        $picName = $type . '_' . time() . $driver->id . ".jpg";
        if (strlen($picture)) {
            $fileType = $picture->guessClientExtension();
            if ($picture->isValid() && ($fileType == 'jpg' || $fileType == 'jpeg' || $fileType == 'gif' || $fileType == 'png' || $fileType == 'bmp')) {
                $picName = $type . '_' . time() . $driver->id . "." . $fileType;
                $picture->move('images/drivers/', $picName);
            }
        }
        return 'images/drivers/' . $picName;
    }

    // ثبت نام راننده
    public function registerDriver(Request $request)
    {

        $name = $request->name;
        $lastName = $request->lastName;
        $nationalCode = $request->nationalCode;
        $mobileNumber = $request->mobileNumber;
        $smartCode = $request->smartCode;
        $fleet_id = $request->fleet_id;
        $marketerCode = $request->marketerCode;

        $countOfNationalCode = Driver::where('nationalCode', $nationalCode)->count();
        //        $countOfSmartCode = Driver::where('smartCode', $smartCode)->count();
        $countOfMobileNumber = Driver::where('mobileNumber', $mobileNumber)->count();

        $message = '';
        if ($countOfMobileNumber > 0) {
            $message = 'شماره همراه تکراری می باشد';
        }
        if ($countOfNationalCode > 0) {
            $message .= ' کدملی تکراری می باشد';
        }
        //        if ($countOfSmartCode > 0) {
        //            $message .= ' کدهوشمند تکراری می باشد';
        //        }

        if ($fleet_id == 0 || strlen($fleet_id) == 0) {
            $message .= 'انتخاب ناوگان الزامی می باشد';
        }

        //        if ($countOfMobileNumber > 0 || $countOfNationalCode > 0 || $countOfSmartCode > 0) {
        if ($countOfMobileNumber > 0) {
            return [
                'result' => UN_SUCCESS,
                'message' => $message
            ];
        }

        // If driver send marketer code, check the code is exist
        if ($marketerCode != null && $marketerCode != 0 && MarketerController::checkMarketerCodeIsExist($marketerCode) == 0) {
            return [
                'result' => UN_SUCCESS,
                'message' => 'کد بازاریاب معتبر نمی باشد'
            ];
        }


        $driver = new Driver();
        $driver->name = $name;
        $driver->lastName = $lastName;
        $driver->nationalCode = $nationalCode;
        $driver->smartCode = $smartCode;
        $driver->mobileNumber = $mobileNumber;
        $driver->marketerCode = $marketerCode;
        $driver->fleet_id = $fleet_id;
        $driver->activationDate = (time() - (23 * 24 * 3600));

        if (isDriverAutoActive())
            $driver->status = ACTIVE;

        //        $activeDate = date("Y-m-d H:i:s", time() + 5 * 24 * 60 * 60);
        //
        //        $driver->activeDate = $activeDate;
        $driver->freeAcceptLoads = DRIVER_FREE_ACCEPT_LOAD;
        // خاور و نیسان
        if ($fleet_id == '82' || $fleet_id == '83' || $fleet_id == '84' || $fleet_id == '85' || $fleet_id == '86' || $fleet_id == '87') {
            $driver->freeCalls = 7;
        } elseif ($fleet_id == '42' || $fleet_id == '43' || $fleet_id == '45' || $fleet_id == '46' || $fleet_id == '47' || $fleet_id == '48') {
            $driver->freeCalls = 7;
        } elseif ($fleet_id == '55' || $fleet_id == '56' || $fleet_id == '57' || $fleet_id == '58' || $fleet_id == '49' || $fleet_id == '50' || $fleet_id == '51' || $fleet_id == '52' || $fleet_id == '53') {
            $driver->freeCalls = 10;
        } elseif ($fleet_id == '54' || $fleet_id == '66') {
            $driver->freeCalls = 15;
        } else {
            $driver->freeCalls = DRIVER_FREE_CALLS;
        }





        $driver->save();

        if ($driver) {
            return [
                'result' => SUCCESS,
                'id' => $driver->id
            ];
        }

        return [
            'result' => UN_SUCCESS,
            'message' => 'خطا در ثبت نام، لطفا دوباره تلاش کنید'
        ];
    }

    // لیست مسیرهای راننده و لیست تمام شهرها
    public function requestPathsListAndAllCitiesList($driver_id)
    {
        $pathList = DriverDefaultPath::where('driver_id', $driver_id)
            ->select('id', 'origin_city_id', 'destination_city_id')
            ->get();

        return [
            'pathList' => $pathList,
            'cities' => City::select('id', 'name', 'state')->get(),
            'province' => State::select('name', 'id')->get()
        ];
    }

    // حذف مسیر پیشفرض راننده
    public function removePath(Request $request)
    {
        $path_id = $request->path_id;
        $driver_id = $request->driver_id;

        $affectedRows = DriverDefaultPath::where([
            ['id', $path_id],
            ['driver_id', $driver_id]
        ])->delete();

        if ($affectedRows > 0)
            return [
                'result' => SUCCESS,
                'pathsListAndAllCitiesList' => $this->requestPathsListAndAllCitiesList($driver_id)
            ];

        return ['result' => UN_SUCCESS];
    }

    // ذخیره توکن FCM
    public function saveMyFireBaseToken(Driver $driver, Request $request)
    {
        $driver->FCM_token = $request->token;
        $driver->version = 67;
        $driver->save();
        return ['result' => SUCCESS];
    }

    // نمایش اطلاعات راننده
    public function driverInfo(Driver $driver)
    {
        return view('admin/driverInfo', compact('driver'));
    }

    // ریپورت کردن راننده توسط باربری
    public function reportDriver(Request $request)
    {
        $load_id = $request->load_id;
        $bearing_id = $request->bearing_id;
        $driver_id = $request->driver_id;

        $report = new ReportDriver();
        $report->load_id = $load_id;
        $report->bearing_id = $bearing_id;
        $report->driver_id = $driver_id;
        $report->save();

        if ($report) {
            return [
                'result' => SUCCESS
            ];
        }

        return [
            'result' => UN_SUCCESS,
            'message' => 'درخواست شما ثبت نشد دوباره تلاش کنید '
        ];
    }

    // انخاب راننده برای بار
    public function addDriverForThisLoad($load_id)
    {

        if (Auth::check()) {

            $load = Load::join('outer_city_loads', 'outer_city_loads.load_id', '=', 'loads.id')
                ->where('loads.id', $load_id)
                ->select('outer_city_loads.origin_city_id', 'outer_city_loads.destination_city_id')
                ->first();


            $driver = Driver::join('driver_default_paths', 'drivers.id', 'driver_default_paths.driver_id')
                ->where([
                    ['driver_default_paths.origin_city_id', $load->origin_city_id],
                    ['driver_default_paths.origin_city_id', $load->destination_city_id],
                ])
                ->select('drivers.*')
                ->get();

            return $driver;
        }
        $message = 'خطا';
        $alert = 'alert-danger';
        return view('admin.alert', compact('message', 'alert'));
    }

    // تغییر راننده به فعال یا غیر فعال
    public function changeDriverStatus($driver_id)
    {
        $driver = Driver::where('id', $driver_id)
            ->select('status', 'FCM_token')
            ->first();

        $message = '';

        if ($driver->status == 0) {
            Driver::where('id', $driver_id)
                ->update(['status' => 1]);
            $message = 'وضعیت به فعال تغییر یافت';
        } else {
            Driver::where('id', $driver_id)
                ->update(['status' => 0]);
            $message = 'وضعیت به غیر فعال تغییر یافت';
        }

        $data = [
            'title' => 'وضعیت',
            'body' => $message,
            'notificationType' => 'authorize',
        ];


        $this->sendNotification($driver->FCM_token, $data, API_ACCESS_KEY_DRIVER);

        $buttonUrl = 'admin/drivers';

        return back()->with('success', $message);
    }

    // ارسال نوتیفیکیشن
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
                ],
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

    // جستجوی راننده
    public function searchDrivers(Request $request)
    {
        /*
        name
        lastName
        mobileNumber
         * */
        $condition = [];
        if (isset($request->name) && strlen($request->name))
            $condition[] = ['name', 'like', '%' . $request->name . '%'];
        if (isset($request->lastName) && strlen($request->lastName))
            $condition[] = ['lastName', 'like', '%' . $request->lastName . '%'];
        if (isset($request->mobileNumber) && strlen($request->mobileNumber))
            $condition[] = ['mobileNumber', 'like', '%' . $request->mobileNumber . '%'];

        if (isset($request->fleet_id) && strlen($request->fleet_id))
            $condition[] = ['fleet_id', $request->fleet_id];

        if (isset($request->city_id) && strlen($request->city_id))
            $condition[] = ['city_id', $request->city_id];

        if (isset($request->province_id) && strlen($request->province_id))
            $condition[] = ['province_id', $request->province_id];

        if (isset($request->version) && strlen($request->version))
            $condition[] = ['version', 'like', '%' . $request->version . '%'];

        if (count($condition)) {
            $drivers = Driver::where($condition)->orderBy('id', 'desc')->paginate(500);
            if (count($drivers))
                return view('admin.driver.searchDriver', compact('drivers'));
        }

        return back()->with('danger', 'راننده ای پیدا نشد!');
    }

    // تماس بار رانندگان
    public function contactingWithDrivers($drivers = [], $showSearchResult = false)
    {
        $resultOfContactingWithDriver = ResultOfContactingWithDriver::where([
            ['operator_id', auth()->id()],
            ['created_at', '>', date('Y-m-d', time()) . ' 00:00:00']
        ])->count();
        if (!$showSearchResult)
            $drivers = Driver::select('id', 'name', 'lastName', 'mobileNumber', 'fleet_id', 'version', 'created_at')
                ->orderBy('id', 'desc')
                ->paginate(20);
        return view('admin.contact.contactingWithDrivers', compact('drivers', 'resultOfContactingWithDriver'));
    }

    // جستجوی راننده
    public function searchDriverCall(Request $request)
    {
        /*
        mobileNumber
         * */
        $condition = [];
        if (isset($request->mobileNumber) && strlen($request->mobileNumber))
            $condition[] = ['mobileNumber', 'like', '%' . $request->mobileNumber . '%'];

        if (count($condition)) {
            $drivers = Driver::where($condition)->orderBy('id', 'desc')->paginate(500);
            if (count($drivers))
                // return $drivers;
                return $this->contactingWithDrivers($drivers, true);
        }

        return back()->with('danger', 'راننده ای پیدا نشد!');
    }

    // حذف راننده
    public function removeDriver(Driver $driver)
    {
        $driver->delete();
        return redirect()->route('drivers')->with('success', 'راننده مورد نظر حذف شد');
    }

    // حذف راننده
    public function removeActiveDate(Driver $driver)
    {
        $driver->activeDate = null;
        $driver->save();

        return back()->with('success', 'اشتراک با موفقیت پاک شد');
    }

    public function zeroData()
    {
        CargoConvertList::where('status', 0)
            ->where('operator_id', 0)
            ->update(['status' => 1, 'operator_id' => 1]);
        return back()->with('success', 'دیتا ها با موفقیت صفر شد');
    }

    // بررسی وضعیت شارژ راننده برای تماس
    public function checkDriverStatusForCalling(Driver $driver, $phoneNumber = '0', $load_id = 0, $latitude = 0, $longitude = 0)
    {
        try {
            $load = Load::where('id', '=', $load_id)->first();

            // $owner = Owner::where('mobileNumber', $load->mobileNumberForCoordination)->whereNotNull('FCM_token')->first();
            // $cityFrom = ProvinceCity::findOrFail($load->origin_city_id);
            // $cityTo = ProvinceCity::findOrFail($load->destination_city_id);

            // if ($owner) {
            //     try {
            //         $title = 'ایران ترابر صاحبان بار';
            //         $body = $driver->name . ' ' . $driver->lastName . ' راننده ' . '(' . $driver->fleetTitle . ')' . ' جهت حمل بار از ' . $cityFrom->name . ' به ' . $cityTo->name . ' با شما تماس گرفته است.';

            //         $this->sendNotification($owner->FCM_token, $title, $body);
            //     } catch (\Exception $exception) {
            //         Log::emergency("----------------------send notif storeInquiryToLoad-----------------------");
            //         Log::emergency($exception);
            //         Log::emergency("---------------------------------------------------------");
            //     }
            // }

            if ($load === null) {
                return ['result' => 2];
            }

            if ($driver->activeDate > date("Y-m-d H:i:s", time()) || $driver->freeCalls > 0) {

                if (DriverCall::where('load_id', $load_id)->where('driver_id', $driver->id)->count() > 0) {
                    return ['result' => true];
                }

                if ($driver->activeDate < date("Y-m-d H:i:s", time())) {
                    $driver->freeCalls--;
                    $driver->save();
                }

                $persian_date = gregorianDateToPersian(date('Y/m/d', time()), '/');

                // گزارش رانندگان بر اساس تماس
                $driverCallCount = DriverCallCount::where('driver_id', $driver->id)
                    ->where('persian_date', $persian_date)
                    ->first();
                if (isset($driverCallCount->id)) {
                    $driverCallCount->calls += 1;
                    $driverCallCount->created_date = $driver->created_at;
                    $driverCallCount->save();
                } else {
                    $driverCallCount = new DriverCallCount();
                    $driverCallCount->persian_date = $persian_date;
                    $driverCallCount->calls = 1;
                    $driverCallCount->driver_id = $driver->id;
                    $driverCallCount->created_date = $driver->created_at;
                    $driverCallCount->save();
                }

                // فعالیت رانندگان بر اساس تماس
                if (DriverCall::where('created_at', '>', date("Y-m-d", time()) . " 00:00:00")->where('driver_id', $driver->id)->count() == 0) {

                    // گزارش رانندگان بر اساس ناوگان
                    $driverCallReport = DriverCallReport::where('fleet_id', $driver->fleet_id)
                        ->where('persian_date', $persian_date)
                        ->first();
                    if (isset($driverCallReport->id)) {
                        $driverCallReport->calls += 1;
                        $driverCallReport->save();
                    } else {
                        $driverCallReport = new DriverCallReport();
                        $driverCallReport->persian_date = $persian_date;
                        $driverCallReport->calls = 1;
                        $driverCallReport->fleet_id = $driver->fleet_id;
                        $driverCallReport->save();
                    }
                }

                $driverCall = new DriverCall();
                $driverCall->driver_id = $driver->id;
                $driverCall->load_id = $load_id;
                $driverCall->phoneNumber = $phoneNumber;
                $driverCall->callingDate = date("Y-m-d");
                $driverCall->date = gregorianDateToPersian(date('Y/m/d', time()), '/');
                $driverCall->dateTime = now()->format('H:i:s');
                $driverCall->latitude = $latitude == 0 ? 0 : $latitude;
                $driverCall->longitude = $longitude == 0 ? 0 : $longitude;
                $driverCall->save();


                $load = Load::find($load_id);

                if (isset($load->id) && $load->operator_id > 0 || $load->isBot == 1) {

                    $load->driverCallCounter--;
                    $load->save();
                    $fleets = json_decode($load->fleets, true);
                    foreach ($fleets as $fleet) {
                        if ($fleet['fleet_id'] == 86 && $load->driverCallCounter <= 0) {
                            $load->delete();
                        }
                        if ($fleet['fleet_id'] == 82 && $load->driverCallCounter <= 0) {
                            $load->delete();
                        }
                    }
                }

                return ['result' => true];
            }
        } catch (Exception $exception) {
            Log::emergency($exception->getMessage());
        }

        return [
            'result' => false
        ];
    }

    public function getPackagesInfo()
    {
        return getDriverPackagesInfo();
    }

    public function driversActivitiesCallDate($driversActivitiesCallDates = [], $showSearchResult = false)
    {
        if (!$showSearchResult) {
            $driversActivitiesCallDates = DriverCall::with('driver')
                ->where('callingDate', now()->format('Y-m-d'))
                ->orderByDesc('created_at')
                ->paginate(20);
        }
        $driversActivitiesCallDatesCount = DriverCall::with('driver')
            ->where('callingDate', now()->format('Y-m-d'))
            ->orderByDesc('created_at')
            ->count();

        $driverCallDatesAllCount = DriverCall::with('driver')->count();

        return view('admin.driversActivitiesCallDate', compact('driversActivitiesCallDates', 'driversActivitiesCallDatesCount', 'driverCallDatesAllCount'));
    }
    public function driversActivitiesCall(Driver $driver)
    {
        $driversActivitiesCallDates = DriverCall::with('driver')
            ->where('callingDate', now()->format('Y-m-d'))
            ->where('driver_id', $driver->id)
            ->orderByDesc('created_at')
            ->paginate(20);
        $driversActivitiesCallDatesCount = DriverCall::with('driver')
            ->where('callingDate', now()->format('Y-m-d'))
            ->orderByDesc('created_at')
            ->count();
        return view('admin.driversActivitiesCallDate', compact('driversActivitiesCallDates', 'driversActivitiesCallDatesCount'));
    }

    public function searchDriversActivitiesCallDate(Request $request)
    {
        $driversActivitiesCallDates = DriverCall::with('driver')
            ->where('callingDate', now()->format('Y-m-d'))
            ->where('phoneNumber', $request->phoneNumber)
            ->orderByDesc('created_at')
            ->get();
        if (count($driversActivitiesCallDates))
            return view('admin.driverActivityLoad.search', compact('driversActivitiesCallDates'));
    }

    public function driversActivities(Request $request, $date = null)
    {
        switch ($request->method()) {
            case 'POST':
                return redirect('admin/driversActivities/' . str_replace('/', '-', convertFaNumberToEn($request->date)));
            case 'GET':
                if ($date == null)
                    $date = DateController::createPersianDate();
        }

        $todayActivities = DriverActivity::join('driver_calls', 'driver_calls.driver_id', 'driver_activities.driver_id')
            ->where('driver_activities.persianDate', $date)
            ->groupBy('driver_activities.driver_id')
            ->select('driver_activities.driver_id', DB::raw('count(driver_calls.driver_id) as total'))
            ->orderby('total', 'desc')
            ->paginate(20);

        $currentMonthActivities = DriverActivity::where('created_at', '>', date('Y-m-d', strtotime(' -30 day')))
            ->distinct()
            ->count('driver_id');
        return view('admin.driversActivities', compact('todayActivities', 'currentMonthActivities'));
    }

    // بررسی وضعیت شارژ راننده برای قبول بار
    public function checkDriverStatusForAcceptLoad(Driver $driver)
    {
        try {

            if ($driver->activeDate > date("Y-m-d H:i:s", time()) || $driver->freeAcceptLoads > 0) {

                if ($driver->activeDate < date("Y-m-d H:i:s", time())) {
                    $driver->freeAcceptLoads--;
                    $driver->save();
                }

                return ['result' => true];
            }
        } catch (Exception $exception) {
            Log::emergency($exception->getMessage());
        }

        return [
            'result' => false,
            'message' => "جهت ارسال درخواست پذیرش حمل بار، شارژ ماهیانه را پرداخت نمایید."
        ];
    }

    // تمدید اعتبار رانندگان
    public function creditDriverExtending(Request $request, Driver $driver)
    {
        $setting = Setting::first();

        if ($request->month == 0) {
            if (Auth::user()->role == 'admin' || Auth::id() == 42 || Auth::id() == 29) {
                if ($this->updateActivationDateAndFreeCallsAndFreeAcceptLoads($driver, $request->month, $request->freeCalls, $driver->freeAcceptLoads)) {
                    $persian_date = gregorianDateToPersian(date('Y/m/d', time()), '/');
                    $oneMonth = gregorianDateToPersian(date('Y/m/d', strtotime('+30 day', time())), '/');
                    $threeMonth = gregorianDateToPersian(date('Y/m/d', strtotime('+90 day', time())), '/');
                    $sixMonth = gregorianDateToPersian(date('Y/m/d', strtotime('+180 day', time())), '/');

                    if ($request->month > 0) {
                        $free_subscription = new FreeSubscription();
                        $free_subscription->type = AUTH_VALIDITY;
                        $free_subscription->value = $request->month;
                        $free_subscription->driver_id = $driver->id;
                        $free_subscription->operator_id = Auth::id();
                        $free_subscription->save();
                        $sms = new Driver();

                        if ($request->month == 1)
                            if ($setting->sms_panel == 'SMSIR') {
                                $sms->freeSubscriptionSmsIr($driver->mobileNumber, $persian_date, $oneMonth);
                            } else {
                                $sms->freeSubscription($driver->mobileNumber, $persian_date, $oneMonth);
                            }
                        if ($request->month == 3)
                            if ($setting->sms_panel == 'SMSIR') {
                                $sms->freeSubscriptionSmsIr($driver->mobileNumber, $persian_date, $threeMonth);
                            } else {
                                $sms->freeSubscription($driver->mobileNumber, $persian_date, $threeMonth);
                            }
                        if ($request->month == 6)
                            if ($setting->sms_panel == 'SMSIR') {
                                $sms->freeSubscriptionSmsIr($driver->mobileNumber, $persian_date, $sixMonth);
                            } else {
                                $sms->freeSubscription($driver->mobileNumber, $persian_date, $sixMonth);
                            }
                    }
                    if ($request->freeCalls > 0) {
                        $free_subscription = new FreeSubscription();
                        $free_subscription->type = AUTH_CALLS;
                        $free_subscription->value = $request->freeCalls;
                        $free_subscription->driver_id = $driver->id;
                        $free_subscription->operator_id = Auth::id();
                        $free_subscription->save();
                        $driver->freeCallTotal += $request->freeCalls;
                        $driver->save();
                    }
                    if ($request->freeAcceptLoads > 0) {
                        $free_subscription = new FreeSubscription();
                        $free_subscription->type = AUTH_CARGO;
                        $free_subscription->value = $request->freeCalls;
                        $free_subscription->driver_id = $driver->id;
                        $free_subscription->operator_id = Auth::id();
                        $free_subscription->save();
                    }
                    return redirect('admin/drivers')->with('success', 'تمدید اعتبار راننده انجام شد.');
                }
            } elseif ($driver->freeCallTotal > 5 || $driver->freeCallTotal + $request->freeCalls > 5) {
                return back()->with('danger', 'خطا! تماس رایگان داده شده بیشتر از 5 تا است');
            } else {
                if ($this->updateActivationDateAndFreeCallsAndFreeAcceptLoads($driver, $request->month, $request->freeCalls, $driver->freeAcceptLoads)) {
                    $persian_date = gregorianDateToPersian(date('Y/m/d', time()), '/');
                    $oneMonth = gregorianDateToPersian(date('Y/m/d', strtotime('+30 day', time())), '/');
                    $threeMonth = gregorianDateToPersian(date('Y/m/d', strtotime('+90 day', time())), '/');
                    $sixMonth = gregorianDateToPersian(date('Y/m/d', strtotime('+180 day', time())), '/');

                    if ($request->month > 0) {
                        $free_subscription = new FreeSubscription();
                        $free_subscription->type = AUTH_VALIDITY;
                        $free_subscription->value = $request->month;
                        $free_subscription->driver_id = $driver->id;
                        $free_subscription->operator_id = Auth::id();
                        $free_subscription->save();
                        $sms = new Driver();

                        if ($request->month == 1)
                            if ($setting->sms_panel == 'SMSIR') {
                                $sms->freeSubscriptionSmsIr($driver->mobileNumber, $persian_date, $oneMonth);
                            } else {
                                $sms->freeSubscription($driver->mobileNumber, $persian_date, $oneMonth);
                            }
                        if ($request->month == 3)
                            if ($setting->sms_panel == 'SMSIR') {
                                $sms->freeSubscriptionSmsIr($driver->mobileNumber, $persian_date, $threeMonth);
                            } else {
                                $sms->freeSubscription($driver->mobileNumber, $persian_date, $threeMonth);
                            }
                        if ($request->month == 6)
                            if ($setting->sms_panel == 'SMSIR') {
                                $sms->freeSubscriptionSmsIr($driver->mobileNumber, $persian_date, $sixMonth);
                            } else {
                                $sms->freeSubscription($driver->mobileNumber, $persian_date, $sixMonth);
                            }
                    }
                    if ($request->freeCalls > 0) {
                        $free_subscription = new FreeSubscription();
                        $free_subscription->type = AUTH_CALLS;
                        $free_subscription->value = $request->freeCalls;
                        $free_subscription->driver_id = $driver->id;
                        $free_subscription->operator_id = Auth::id();
                        $free_subscription->save();
                        $driver->freeCallTotal += $request->freeCalls;
                        $driver->save();
                    }
                    if ($request->freeAcceptLoads > 0) {
                        $free_subscription = new FreeSubscription();
                        $free_subscription->type = AUTH_CARGO;
                        $free_subscription->value = $request->freeCalls;
                        $free_subscription->driver_id = $driver->id;
                        $free_subscription->operator_id = Auth::id();
                        $free_subscription->save();
                    }
                    return redirect('admin/drivers')->with('success', 'تمدید اعتبار راننده انجام شد.');
                }
            }
        } else {
            if ($this->updateActivationDateAndFreeCallsAndFreeAcceptLoads($driver, $request->month, $request->freeCalls, $driver->freeAcceptLoads)) {
                $persian_date = gregorianDateToPersian(date('Y/m/d', time()), '/');
                $oneMonth = gregorianDateToPersian(date('Y/m/d', strtotime('+30 day', time())), '/');
                $threeMonth = gregorianDateToPersian(date('Y/m/d', strtotime('+90 day', time())), '/');
                $sixMonth = gregorianDateToPersian(date('Y/m/d', strtotime('+180 day', time())), '/');

                if ($request->month > 0) {
                    $free_subscription = new FreeSubscription();
                    $free_subscription->type = AUTH_VALIDITY;
                    $free_subscription->value = $request->month;
                    $free_subscription->driver_id = $driver->id;
                    $free_subscription->operator_id = Auth::id();
                    $free_subscription->save();
                    $sms = new Driver();

                    if ($request->month == 1) {
                        if ($setting->sms_panel == 'SMSIR') {
                            $sms->freeSubscriptionSmsIr($driver->mobileNumber, $persian_date, $oneMonth);
                        } else {
                            $sms->freeSubscription($driver->mobileNumber, $persian_date, $oneMonth);
                        }
                    }
                    if ($request->month == 3) {
                        if ($setting->sms_panel == 'SMSIR') {
                            $sms->freeSubscriptionSmsIr($driver->mobileNumber, $persian_date, $threeMonth);
                        } else {
                            $sms->freeSubscription($driver->mobileNumber, $persian_date, $threeMonth);
                        }
                    }
                    if ($request->month == 6) {
                        if ($setting->sms_panel == 'SMSIR') {
                            $sms->freeSubscriptionSmsIr($driver->mobileNumber, $persian_date, $sixMonth);
                        } else {
                            $sms->freeSubscription($driver->mobileNumber, $persian_date, $sixMonth);
                        }
                    }
                }
                if ($request->freeCalls > 0) {
                    $free_subscription = new FreeSubscription();
                    $free_subscription->type = AUTH_CALLS;
                    $free_subscription->value = $request->freeCalls;
                    $free_subscription->driver_id = $driver->id;
                    $free_subscription->operator_id = Auth::id();
                    $free_subscription->save();
                    $driver->freeCallTotal += $request->freeCalls;
                    $driver->save();
                }
                if ($request->freeAcceptLoads > 0) {
                    $free_subscription = new FreeSubscription();
                    $free_subscription->type = AUTH_CARGO;
                    $free_subscription->value = $request->freeCalls;
                    $free_subscription->driver_id = $driver->id;
                    $free_subscription->operator_id = Auth::id();
                    $free_subscription->save();
                }
                return redirect('admin/drivers')->with('success', 'تمدید اعتبار راننده انجام شد.');
            }
        }
        return redirect('admin/drivers')->with('danger', 'خطا در تمدید اعتبار راننده!');
    }

    /**
     * @param Driver $driver
     * @param Request $request
     * @return void
     * @throws Exception
     */
    public function updateActivationDateAndFreeCallsAndFreeAcceptLoads(Driver $driver, $month, $freeCalls, $freeAcceptLoads): bool
    {
        try {

            $date = new \DateTime($driver->activeDate);
            $time = $date->getTimestamp();
            if ($time < time())
                $driver->activeDate = date('Y-m-d', time() + $month * 30 * 24 * 60 * 60);
            else
                $driver->activeDate = date('Y-m-d', $time + $month * 30 * 24 * 60 * 60);

            $driver->freeCalls = ($driver->freeCalls > 0 ? $driver->freeCalls : 0) + $freeCalls;
            // $driver->freeAcceptLoads += $freeAcceptLoads;
            $driver->save();

            try {
                if ($month > 0) {

                    $driverPackagesInfo = getDriverPackagesInfo();
                    $amount = 0;
                    if ($month == 1)
                        $amount = $driverPackagesInfo['data']['monthly']['price'];
                    else if ($month == 3)
                        $amount = $driverPackagesInfo['data']['trimester']['price'];
                    else if ($month == 6)
                        $amount = $driverPackagesInfo['data']['sixMonths']['price'];

                    $transaction = new Transaction();
                    $transaction->user_id = $driver->id;
                    $transaction->userType = ROLE_DRIVER;
                    $transaction->authority = $driver->id . time();
                    $transaction->amount = $amount;
                    $transaction->status = 100;
                    $transaction->monthsOfThePackage = $month;
                    $transaction->save();
                }
            } catch (Exception $e) {
            }

            return true;
        } catch (Exception $exception) {
        }

        return false;
    }

    /************************************************************************************************/
    // دریافت اطلاعات پروفایل راننده
    public function getDriverProfileInfo(Driver $driver)
    {
        $remainingDaysOfSubscription = 0;
        try {
            $currentDate = new \DateTime(date('Y-m-d'));
            $activeDate = new \DateTime($driver->activeDate);
            $interval = $currentDate->diff($activeDate);
            $remainingDaysOfSubscription = ($currentDate < $activeDate) ? $interval->d : 0;
        } catch (Exception $exception) {
        }

        return response()->json([
            'result' => true,
            'data' => [
                'id' => $driver->id,
                'name' => $driver->name,
                'lastName' => $driver->lastName,
                'vehicleLicensePlatePartA' => strlen($driver->vehicleLicensePlatePartA) == 0 || $driver->vehicleLicensePlatePartA == ' ' ? null : $driver->vehicleLicensePlatePartA,
                'vehicleLicensePlatePartB' => strlen($driver->vehicleLicensePlatePartB) == 0 || $driver->vehicleLicensePlatePartB == ' ' ? null : $driver->vehicleLicensePlatePartB,
                'vehicleLicensePlatePartC' => strlen($driver->vehicleLicensePlatePartC) == 0 || $driver->vehicleLicensePlatePartC == ' ' ? null : $driver->vehicleLicensePlatePartC,
                'vehicleLicensePlatePartD' => strlen($driver->vehicleLicensePlatePartD) == 0 || $driver->vehicleLicensePlatePartD == ' ' ? null : $driver->vehicleLicensePlatePartD,
                'nationalCode' => $driver->nationalCode,
                'mobileNumber' => $driver->mobileNumber,
                'fleet_id' => $driver->fleet_id,
                // 'smartCode' => $driver->smartCode,
                'driverImage' => file_exists($driver->driverImage) ? $driver->driverImage : null,
                'nationalCardImage' => file_exists($driver->nationalCardImage) ? $driver->nationalCardImage : null,
                // 'carSmartCardImage' => file_exists($driver->carSmartCardImage) ? $driver->carSmartCardImage : null,
                'driverSmartCardImage' => file_exists($driver->driverSmartCardImage) ? $driver->driverSmartCardImage : null,
                'imageAddressDoc' => file_exists($driver->imageAddressDoc) ? $driver->imageAddressDoc : null,
                // 'imageRegisterSana' => file_exists($driver->imageRegisterSana) ? $driver->imageRegisterSana : null,
                'authImage' => file_exists($driver->authImage) ? $driver->authImage : null,
                'remainingDaysOfSubscription' => $remainingDaysOfSubscription,
                'operatorMessage' => $driver->operatorMessage,
                // 'freeCalls' => $driver->freeCalls,
                // 'freeAcceptLoads' => $driver->freeAcceptLoads,
                'activeDate' => $driver->activeDate,
                'fleetTitle' => $driver->fleetTitle,
                // 'fleets' => Fleet::select('id', 'title', 'parent_id', 'pic')->get(),
                'authStatus' => $driver->authLevel,
                'driverAuthStatusTitles' => DRIVER_AUTH_STATUS_TITLE,
                'address' => $driver->address,
                'ratingDriver' => $driver->ratingDriver
            ]
        ]);
    }

    // بروز رسانی اطلاعات راننده
    public function updateProfileInfo(Request $request, Driver $driver)
    {
        $rules = [
            'name' => 'required',
            'lastName' => 'required',
            'fleet_id' => 'required|numeric',
        ];
        $this->validate($request, $rules, []);

        try {

            $driver->name = $request->name;
            $driver->lastName = $request->lastName;
            $driver->vehicleLicensePlatePartA = $request->vehicleLicensePlatePartA;
            $driver->vehicleLicensePlatePartB = $request->vehicleLicensePlatePartB;
            $driver->vehicleLicensePlatePartC = $request->vehicleLicensePlatePartC;
            $driver->vehicleLicensePlatePartD = $request->vehicleLicensePlatePartD;
            $driver->address = $request->address;

            if (strlen($driver->nationalCode) != 10)
                $driver->nationalCode = $request->nationalCode;
            $driver->fleet_id = $request->fleet_id;

            if ($driver->smartCode != null)
                $driver->smartCode = $request->smartCode;

            $driverImage = "noImage";
            $nationalCardImage = "noImage";
            $carSmartCardImage = "noImage";
            $driverSmartCardImage = "noImage";
            $authImage = "noImage";
            $imageAddressDoc = "noImage";
            $imageRegisterSana = "noImage";

            if ($request->hasfile('authImage')) {
                $file = $request->file('authImage');
                $extenstion = $file->getClientOriginalExtension();
                $filename = time() . '.' . $extenstion;
                if (file_exists($driver->authImage))
                    unlink($driver->authImage);
                $file->move('images/drivers/authImage', $filename);
                $driver->authImage = 'images/drivers/authImage/' . $filename;
            }

            if ($request->hasfile('driverImage')) {
                $file = $request->file('driverImage');
                $extenstion = $file->getClientOriginalExtension();
                $filename = time() . '.' . $extenstion;
                if (file_exists($driver->driverImage))
                    unlink($driver->driverImage);
                $file->move('images/drivers/driverImage', $filename);
                $driver->driverImage = 'images/drivers/driverImage/' . $filename;
            }
            if ($request->hasfile('nationalCardImage')) {
                $file = $request->file('nationalCardImage');
                $extenstion = $file->getClientOriginalExtension();
                $filename = time() . '.' . $extenstion;
                if (file_exists($driver->nationalCardImage))
                    unlink($driver->nationalCardImage);
                $file->move('images/drivers/nationalCardImage', $filename);
                $driver->nationalCardImage = 'images/drivers/nationalCardImage/' . $filename;
            }
            if ($request->hasfile('carSmartCardImage')) {
                $file = $request->file('carSmartCardImage');
                $extenstion = $file->getClientOriginalExtension();
                $filename = time() . '.' . $extenstion;
                if (file_exists($driver->carSmartCardImage))
                    unlink($driver->carSmartCardImage);
                $file->move('images/drivers/carSmartCardImage', $filename);
                $driver->carSmartCardImage = 'images/drivers/carSmartCardImage/' . $filename;
            }
            if ($request->hasfile('driverSmartCardImage')) {
                $file = $request->file('driverSmartCardImage');
                $extenstion = $file->getClientOriginalExtension();
                $filename = time() . '.' . $extenstion;
                if (file_exists($driver->driverSmartCardImage))
                    unlink($driver->driverSmartCardImage);
                $file->move('images/drivers/driverSmartCardImage', $filename);
                $driver->driverSmartCardImage = 'images/drivers/driverSmartCardImage/' . $filename;
            }
            if ($request->hasfile('imageAddressDoc')) {
                $file = $request->file('imageAddressDoc');
                $extenstion = $file->getClientOriginalExtension();
                $filename = time() . '.' . $extenstion;
                if (file_exists($driver->imageAddressDoc))
                    unlink($driver->imageAddressDoc);
                $file->move('images/drivers/imageAddressDoc', $filename);
                $driver->imageAddressDoc = 'images/drivers/imageAddressDoc/' . $filename;
            }
            if ($request->hasfile('imageRegisterSana')) {
                $file = $request->file('imageRegisterSana');
                $extenstion = $file->getClientOriginalExtension();
                $filename = time() . '.' . $extenstion;
                if (file_exists($driver->imageRegisterSana))
                    unlink($driver->imageRegisterSana);
                $file->move('images/drivers/imageRegisterSana', $filename);
                $driver->imageRegisterSana = 'images/drivers/imageRegisterSana/' . $filename;
            }

            if ($driverImage != "noImage" && file_exists($driverImage))
                $driver->driverImage = $driverImage;
            if ($nationalCardImage != "noImage" && file_exists($nationalCardImage))
                $driver->nationalCardImage = $nationalCardImage;
            if ($carSmartCardImage != "noImage" && file_exists($carSmartCardImage))
                $driver->carSmartCardImage = $carSmartCardImage;
            if ($driverSmartCardImage != "noImage" && file_exists($driverSmartCardImage))
                $driver->driverSmartCardImage = $driverSmartCardImage;
            if ($authImage != "noImage" && file_exists($authImage))
                $driver->authImage = $authImage;

            if ($imageAddressDoc != "noImage" && file_exists($imageAddressDoc))
                $driver->imageAddressDoc = $imageAddressDoc;

            if ($imageRegisterSana != "noImage" && file_exists($imageRegisterSana))
                $driver->imageRegisterSana = $imageRegisterSana;

            $driver->updateDateTime = date('Y-m-d H:i:s', time());

            if (
                $driver->name !== null &&
                $driver->lastName !== null &&
                $driver->nationalCode !== null &&
                $driver->mobileNumber !== null &&
                $driver->vehicleLicensePlatePartA !== null &&
                $driver->vehicleLicensePlatePartB !== null &&
                $driver->vehicleLicensePlatePartC !== null &&
                $driver->vehicleLicensePlatePartD !== null &&
                $driver->address !== null &&
                $driver->nationalCardImage !== null &&
                $driver->authImage !== null
            ) {
                if ($driver->authLevel == DRIVER_AUTH_SILVER) {
                    $driver->authLevel = DRIVER_AUTH_SILVER;
                } else
                    $driver->authLevel = DRIVER_AUTH_SILVER_PENDING;
            }
            if (
                $driver->name !== null &&
                $driver->lastName !== null &&
                $driver->nationalCode !== null &&
                $driver->mobileNumber !== null &&
                $driver->vehicleLicensePlatePartA !== null &&
                $driver->vehicleLicensePlatePartB !== null &&
                $driver->vehicleLicensePlatePartC !== null &&
                $driver->vehicleLicensePlatePartD !== null &&
                $driver->address !== null &&
                $driver->nationalCardImage !== null &&
                $driver->authImage !== null &&
                $driver->imageAddressDoc !== null &&
                $driver->imageRegisterSana !== null
            ) {
                $driver->authLevel = DRIVER_AUTH_GOLD_PENDING;
            }

            // if (FreeSubscription::where('driver_id', $driver->id)->where('type', AUTH_CALLS)->where('value', TEN_AUTH_CALLS)->count() == 0) {
            //     $free_subscription = new FreeSubscription();
            //     $free_subscription->type = AUTH_CALLS;
            //     $free_subscription->value = TEN_AUTH_CALLS;
            //     $free_subscription->driver_id = $driver->id;
            //     $free_subscription->save();
            //     $driver->freeCalls += 25;
            // }

            $driver->save();

            try {

                OperatorDriverAuthMessage::where('driver_id', $driver->id)
                    ->update(['close' => true]);
            } catch (Exception $exception) {
            }
            if ($driver->authLevel !== DRIVER_AUTH_SILVER_PENDING && $driver->authLevel !== DRIVER_AUTH_GOLD_PENDING) {
                return \response()->json([
                    'result' => false,
                    'message' => 'اطلاعات جدید ذخیره شد.'
                ]);
            }
            return \response()->json([
                'result' => true,
                'message' => 'اطلاعات جدید ذخیره شد.'
            ]);
        } catch (Exception $e) {
            Log::emergency("========================= بروز رسانی اطلاعات راننده ================================");
            Log::emergency($e);
            Log::emergency("===================================================================================");
        }

        return \response()->json([
            'result' => false,
            'message' => 'خطا در ذخیره اطلاعات! لطفا دوباره تلاش کنید'
        ]);
    }

    public function updateLocation(Request $request, Driver $driver)
    {

        try {

            $driver->latitude = $request->latitude;
            $driver->longitude = $request->longitude;
            $city = ProvinceCity::where('parent_id', '!=', 0)->where('name', $request->city)->first();
            $driver->province_id = $city->parent_id;
            $driver->city_id = $city->id;
            $driver->save();
            return response()->json('اطلاعات جدید ذخیره شد', 200);
        } catch (Exception $exception) {
            return $exception->getMessage();
        }
    }

    /*******************************************************************************************************/
    public function driverAppVersion(Driver $driver, $version)
    {
        $driver->version = $version;
        $driver->save();
        return response()->json($driver->transactionCount, 200);

        // return [
        //     'version' => AppVersion::orderBy('id', 'desc')->first()->driverVersion,
        //     'link' => 'https://cafebazaar.ir/app/com.iran_tarabar.driver',
        //     'tell' => TELL,
        //     'BANK_NAME' => BANK_NAME,
        //     'CARD_NUMBER' => CARD_NUMBER,
        //     'BANK_CARD_OWNER' => BANK_CARD_OWNER,
        //     'authLevel' => $driver->authLevel,
        //     'unAuthLevelMessage' => 'تعداد تماس بدون احراز هویت : ' . CALL_LIMIT_FOR_UNAUTH_DRIVERS . '  تماس تا تکمیل مدارک و احراز هویت. ',
        //     'acceptAuthLevelAlertMessage' =>
        //     'احزار هویت شما به منظور جلب اعتماد اعلام کنندگان بار و معرفی شما به عنوان راننده تایید شده صورت می گیرد.' .
        //         ' ' .
        //         'احراز هویت سطح نقره ای :   روزانه  ' . NUMBER_FOR_CALLS_PAY_DAY_FOR_SILVER_LEVEL_DRIVER . ' تماس ' .
        //         ' ' . 'احراز هویت سطح طلایی :   روزانه ' . NUMBER_FOR_CALLS_PAY_DAY_FOR_GOLD_LEVEL_DRIVER . ' تماس',
        // ];
    }


    /**********************************************************************************************************/
    // احراز هویت رانندگان توسط اپراتور
    public function driversAuthenticationByOperator()
    {
        $drivers = Driver::whereIn('authLevel', [DRIVER_AUTH_SILVER_PENDING, DRIVER_AUTH_GOLD_PENDING])
            ->orderby('updateDateTime', 'asc')
            ->paginate(20);
        $driverCount = Driver::whereIn('authLevel', [DRIVER_AUTH_SILVER_PENDING, DRIVER_AUTH_GOLD_PENDING])
            ->orderby('updateDateTime', 'asc')
            ->count();

        return view('admin.driversAuthenticationByOperator', compact(['drivers', 'driverCount']));
    }

    public static function getNumOfAuthDriver()
    {
        $driverCount = Driver::whereIn('authLevel', [DRIVER_AUTH_SILVER_PENDING, DRIVER_AUTH_GOLD_PENDING])
            ->orderby('updateDateTime', 'asc')
            ->count();
        return $driverCount;
    }

    public function removeDriverFile($fileType, Driver $driver)
    {
        try {
            unlink($driver->$fileType);
            $driver->$fileType = "";
            $driver->save();
            return back()->with('success', 'فایل مورد نظر حذف شد');
        } catch (Exception $exception) {
        }

        return back()->with('danger', 'خطا در حذف فایل!');
    }

    public function updateAuthLevel(Request $request, Driver $driver)
    {
        if ($request->status == ACCEPT) {
            if ($request->authLevel == DRIVER_AUTH_GOLD_PENDING) {
                $driver->authLevel = DRIVER_AUTH_GOLD;
            }
            if ($request->authLevel == DRIVER_AUTH_SILVER_PENDING) {
                $driver->authLevel = DRIVER_AUTH_SILVER;
            }
            $driver->authLevelOld = $driver->authLevel;
        } else {
            if ($request->authLevel == DRIVER_AUTH_GOLD_PENDING) {
                $driver->authLevel = DRIVER_AUTH_SILVER;
            }
            if ($request->authLevel == DRIVER_AUTH_SILVER_PENDING) {
                $driver->authLevel = DRIVER_AUTH_UN_AUTH;
            }
            $driver->authLevel = $driver->authLevelOld;
        }
        $driver->save();

        try {
            OperatorDriverAuthMessage::where('driver_id', $driver->id)
                ->update(['close' => true]);

            if (strlen($request->operatorMessage) > 0) {
                $operatorDriverAuthMessage = new OperatorDriverAuthMessage();
                $operatorDriverAuthMessage->driver_id = $driver->id;
                $operatorDriverAuthMessage->user_id = \auth()->id();
                $operatorDriverAuthMessage->message = $request->operatorMessage;
                $operatorDriverAuthMessage->save();
            }
        } catch (Exception $exception) {
        }

        return back()->with('success', 'وضعیت با موفقیت ثبت شد');
    }
}
