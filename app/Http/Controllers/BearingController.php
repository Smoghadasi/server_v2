<?php

namespace App\Http\Controllers;

use App\Models\Bearing;
use App\Models\City;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Zend\Diactoros\Response;

class BearingController extends Controller
{
    // نمایش لیست باربری ها
    public function bearing()
    {
        $bearings = Bearing::orderby('id', 'desc')->paginate(50);
        $cities = City::all();
        $states = State::all();
        return view('admin.bearing', compact('bearings', 'cities', 'states'));
    }

    // جستجوی باربری
    public function searchBearing(Request $request)
    {
        $word = $request->word;
        $conditions = [];
        $message = 'جستجو بر اساس : ';

        switch ($request->searchMethod) {
            case 'title':
                $message .= ' عنوان باربری - کلمه جستجو شده : ' . $word;
                $conditions[] = ['title', 'LIKE', "%$word%"];
                break;
            case 'operatorName':
                $message .= ' نام متصدی  - کلمه جستجو شده : ' . $word;
                $conditions[] = ['operatorName', 'LIKE', "%$word%"];
                break;
            case 'city':
                $message .= ' شهر - شهر جستجو شده : ' . AddressController::geCityName($request->city_id);
                $conditions[] = ['city_id', $request->city_id];
                break;
            case 'state':
                $message .= ' استان - استان جستجو شده : ' . AddressController::geStateName($request->state_id);
                $conditions[] = ['state_id', $request->state_id];
                break;
            case 'status_active':
                $message .= ' تایید شده ';
                $conditions[] = ['status', 1];
                break;
            case 'status_deactive':
                $message .= ' تایید نشده ';
                $conditions[] = ['status', 0];
                break;
            case 'mobileNumber':
                $message .= ' شماره تلفن همراه  - شماره جستجو شده : ' . $word;
                $conditions[] = ['mobileNumber', 'LIKE', "%$word%"];
                break;
            case 'phoneNumber':
                $message .= ' شماره تلفن ثابت  - شماره جستجو شده : ' . $word;
                $conditions[] = ['phoneNumber', 'LIKE', "%$word%"];
                break;
        }

        $bearings = Bearing::where($conditions)->orderby('id', 'desc')->paginate(500);

        $message .= ' - تعداد یافته ها : ' . count($bearings) . ' مورد ';

        $cities = City::all();
        $states = State::all();
        $searchMethod = $request->searchMethod;
        return view('admin.bearing', compact('bearings', 'cities', 'states', 'message', 'searchMethod'));
    }

    // نمایش لیست بارهای یک باربری
    public function bearingLoads($id)
    {

    }


    public static function getBearingInfo($id)
    {

        return Bearing::join('cities', 'cities.id', '=', 'bearings.city_id')
            ->where('bearings.id', $id)
            ->select('bearings.title', 'bearings.operatorName', 'bearings.registrationNumber', 'bearings.phoneNumber', 'bearings.mobileNumber', 'bearings.grade', 'bearings.score', 'bearings.wallet', 'cities.name as city')
            ->first();

    }

    // دریافت عنوان باربری
    public static function getBearingTitle($bearing_id)
    {
        $bearing = Bearing::where('id', $bearing_id)->first();

        if ($bearing)
            return $bearing->title;

        return 'بدون نام';
    }

    // ذخیره توکن FCM
    public function saveMyFireBaseToken(Request $request)
    {
        $bearing_id = $request->bearing_id;
        $token = $request->token;

        Bearing::where('id', $bearing_id)->update(['FCM_token' => $token]);
        return ['result' => SUCCESS];
    }

    // نمایش فرم افزودن باربری
    public function addNewBearingForm($message = '')
    {
        $cities = City::all();
        return view('admin.addNewBearingForm', compact('cities', 'message'));
    }

    // افزودن باربری
    public function addNewBearing(Request $request)
    {

        $title = $request->title;
        $operatorName = $request->operatorName;
        $registrationNumber = $request->registrationNumber;
        $city_id = $request->city_id;
        $state_id = AddressController::geStateIdFromCityId($city_id);
        $phoneNumber = $request->phoneNumber;
        $mobileNumber = $request->mobileNumber;

        // ذخیره باربری جدید
        $bearing = new Bearing();
        $bearing->title = $title;
        $bearing->operatorName = $operatorName;
        $bearing->mobileNumber = $mobileNumber;
        $bearing->phoneNumber = $phoneNumber;
        $bearing->registrationNumber = $registrationNumber;
        $bearing->city_id = $city_id;
        $bearing->state_id = $state_id;

        if (isTransportationCompanyAutoActive())
            $bearing->status = ACTIVE;

        $bearing->save();

        return $this->addNewBearingForm('باربری مورد نظر ثبت شد');

    }

    public function addNewBearingOnWeb(Request $request)
    {

        $title = $request->title;
        $operatorName = $request->operatorName;
        $registrationNumber = $request->registrationNumber;
        $city_id = $request->city_id;
        $state_id = AddressController::geStateIdFromCityId($city_id);
        $phoneNumber = $request->phoneNumber;
        $mobileNumber = $request->mobileNumber;

        // ذخیره باربری جدید
        $bearing = new Bearing();
        $bearing->title = $title;
        $bearing->operatorName = $operatorName;
        $bearing->mobileNumber = $mobileNumber;
        $bearing->phoneNumber = $phoneNumber;
        $bearing->registrationNumber = $registrationNumber;
        $bearing->city_id = $city_id;
        $bearing->state_id = $state_id;

        if (isTransportationCompanyAutoActive())
            $bearing->status = ACTIVE;

        $bearing->save();

        auth('bearing')->loginUsingId($bearing->id);
        return redirect(url('user'));
    }

    // Decrease from wallet
    public static function decreaseFromWallet($bearing_id, $cost)
    {
        $bearing = Bearing::where('id', $bearing_id)->first();

        if ($bearing->wallet >= $cost) {
            Bearing::where('id', $bearing_id)
                ->update(['wallet' => ($bearing->wallet - $cost)]);
            return true;
        }
        return false;
    }

    // تغییر وضعیت بابری
    public function changeBearingStatus($bearing_id)
    {
        $bearing = Bearing::where('id', $bearing_id)
            ->select('status', 'FCM_token')
            ->first();

        $message = '';
        $alert = 'alert-success';

        if ($bearing->status == 0) {
            Bearing::where('id', $bearing_id)
                ->update(['status' => 1]);
            $message = 'وضعیت به فعال تغییر یافت';

        } else {
            Bearing::where('id', $bearing_id)
                ->update(['status' => 0]);
            $message = 'وضعیت به غیر فعال تغییر یافت';
        }

        $data = [
            'title' => 'وضعیت',
            'body' => $message,
            'notificationType' => 'authorize',
        ];


       // $this->sendNotification($bearing->FCM_token, $data, API_ACCESS_KEY_TRANSPORTATION_COMPANY);

        $buttonUrl = 'admin/bearing';

        return view('admin.alert', compact('message', 'alert', 'buttonUrl'));
    }

    // ارسال نوتیفیکیشن
    public function sendNotification($FCM_token, $data, $API_ACCESS_KEY)
    {
        $url = 'https://fcm.googleapis.com/fcm/send';
        $notification = [
            'body' => $data['body'],
            'sound' => true,
        ];
        $fields = array(
            'to' => $FCM_token,
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

    // فرم ویرایش اطلاعات باربری
    public function editBearingInfoForm($bearing_id, $message = null)
    {
        if (Bearing::where('id', $bearing_id)->count() == 0) {
            $message = 'چنین باربری وجود ندارد';
            $alert = 'alert-warning';
            return view('admin.alert', compact('message', 'alert'));
        }

        $cities = City::all();
        $bearing = Bearing::where('id', $bearing_id)->first();

        return view('admin.editBearingForm', compact('cities', 'bearing', 'message'));
    }

    // ویرایش اطلاعات باربری
    public function editBearingInfo(Request $request)
    {

        $rules = [
            'title' => 'required|',
            'operatorName' => 'required|',
            'mobileNumber' => 'required|',
            'phoneNumber' => 'required|',
            'registrationNumber' => 'required|',
            'city_id' => 'required|',
            'grade' => 'required|',
        ];
        $messages = [
            'title.required' => 'عنوان باربری را وارد نمایید',
            'operatorName.required' => 'نام متصدی باربری را وارد نمایید',
            'mobileNumber.required' => 'شماره تلفن همراه را وارد نمایید',
            'phoneNumber.required' => 'شماره ثابت باربری را وارد نمایید',
            'registrationNumber.required' => 'شماره ثبت باربری را وارد نمایید',
            'city_id.required' => 'شهر باربری را انتخاب کنید',
            'grade.required' => 'گرید باربری را وارد نمایید',
        ];

        $this->validate($request, $rules, $messages);


        Bearing::where('id', $request->bearing_id)
            ->update([
                'title' => $request->title,
                'operatorName' => $request->operatorName,
                'mobileNumber' => $request->mobileNumber,
                'phoneNumber' => $request->phoneNumber,
                'registrationNumber' => $request->registrationNumber,
                'city_id' => $request->city_id,
                'state_id' => AddressController::geStateIdFromCityId($request->city_id),
                'grade' => $request->grade
            ]);

        return $this->editBearingInfoForm($request->bearing_id, 'اطلاعات جدید ثبت شد');
    }

    // بررسی اعتبار حساب کاربری شرکت باربری
    public function checkUserAccountCredit($id)
    {

        try {
            $transportationCompany = Bearing::find($id);
            if (isset($transportationCompany->id)) {

                // بررسی وضعیت تایید یا عدم تایید
                if ($transportationCompany->status == 0)
                    return [
                        'result' => false,
                        'message' => null,
                        'data' => [
                            'authStatus' => false
                        ]
                    ];

                // بررسی زمان و تعداد استفاده از اپلیکیشن
                if ($transportationCompany->validityDate > date('Y-m-d H:i:s') || $transportationCompany->countOfLoadsAfterValidityDate > 0)
                    return [
                        'result' => true,
                        'message' => null,
                        'data' => [
                            'authStatus' => true
                        ]
                    ];

                // اگر زمان و تعداد ثبت بار پس از اعتبار حساب تمام شده بود
                if ($transportationCompany->wallet >= TRANSPORTATION_COMPANY_MONTHLY_SERVICE_AMOUNT)
                    return [
                        'result' => false,
                        'message' => 'اعتبار حساب شما تمام شده است، آیا مایل هستید از طریق اعتبار کیف پول خود '
                            . number_format(TRANSPORTATION_COMPANY_MONTHLY_SERVICE_AMOUNT)
                            . ' تومان پرداخت نمایید؟',
                        'data' => [
                            'authStatus' => true,
                            'payAmountFromWallet' => false,
                            'monthlyServiceAmount' => TRANSPORTATION_COMPANY_MONTHLY_SERVICE_AMOUNT,
                            'numOfFreeLoads' => NUM_OF_FREE_LOADS
                        ]
                    ];

                return [
                    'result' => false,
                    'message' => 'اعتبار حساب شما تمام شده است لطفا جهت کار با اپلیکیشن کیف پول خود را به مبلغ '
                        . number_format(TRANSPORTATION_COMPANY_MONTHLY_SERVICE_AMOUNT)
                        . ' تومان شارژ کنید.',
                    'data' => [
                        'authStatus' => true,
                        'payAmountFromWallet' => true,
                        'monthlyServiceAmount' => TRANSPORTATION_COMPANY_MONTHLY_SERVICE_AMOUNT,
                        'numOfFreeLoads' => NUM_OF_FREE_LOADS
                    ]
                ];
            }

        } catch (\Exception $exception) {

        }

        return [
            'result' => false,
            'message' => 'خطایی رخ داده! لطفا مجددا تلاش کنید',
            'data' => [
                'authStatus' => true
            ]
        ];
    }

    // پرداخت شارژ ماهیانه از کیف پول
    public function payMonthlyChargeFromWallet(Request $request)
    {
        try {
            $transportationCompany = Bearing::find($request->transportationCompany_id);

            if ($transportationCompany->wallet >= TRANSPORTATION_COMPANY_MONTHLY_SERVICE_AMOUNT) {

                $transportationCompany->wallet -= TRANSPORTATION_COMPANY_MONTHLY_SERVICE_AMOUNT;
                $transportationCompany->validityDate = date("Y-m-d H:i:s", time() + 30 * 24 * 60 * 60);
                $transportationCompany->countOfLoadsAfterValidityDate = 60;
                $transportationCompany->save();

                return [
                    'result' => true,
                    'message' => null,
                    'data' => null
                ];
            }

            return [
                'result' => false,
                'message' => 'شارژ کیف پول شما کافی نمی باشد',
                'data' => null
            ];

        } catch (\Exception $exception) {
            Log::emergency("-------------------------------------------------------------------------------");
            Log::emergency("پرداخت شارژ ماهیانه از کیف پول");
            Log::emergency($exception->getMessage());
            Log::emergency("-------------------------------------------------------------------------------");
        }

        return [
            'result' => false,
            'message' => 'شرکت باربری مورد نظر معتبر نمی باشد!',
            'data' => null
        ];
    }

    // حذف باربری
    public function removeTransportationCompany(Bearing $bearing)
    {
        $bearing->delete();
        return back()->with('success', 'باربری مورد نظر حذف شد');
    }
}
