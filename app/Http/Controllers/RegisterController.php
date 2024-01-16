<?php

namespace App\Http\Controllers;

use App\Models\ActivationCode;
use App\Models\Bearing;
use App\Models\Customer;
use App\Models\LegalPersonality;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    // ثبت نام باربری
    public function registerBearing(Request $request)
    {

        try {
            // فیلتر کردن ورود های

            // ذخیره باربری جدید
            $bearing = new Bearing();
            $bearing->title = $request->title;
            $bearing->operatorName = $request->operatorName;
            $bearing->mobileNumber = ParameterController::convertNumbers($request->mobileNumber);
            $bearing->phoneNumber = $request->phoneNumber;
            $bearing->registrationNumber = $request->registrationNumber;
            $bearing->marketerCode = $request->marketerCode;
            $bearing->city_id = $request->city_id;
            $bearing->state_id = AddressController::geStateIdFromCityId($request->city_id);
            $bearing->save();

            if (isset($bearing->id)) {
                return [
                    'result' => SUCCESS,
                    'id' => $bearing->id
                ];
            }
        } catch (\Exception $exception) {

            Log::emergency("---------------------------------- registerBearing ---------------------------------");
            Log::emergency($exception->getMessage());
            Log::emergency("------------------------------------------------------------------------------------");
        }
        return [
            'result' => UN_SUCCESS,
            'message' => 'خطا! در ذخیره اطلاعات لطفا دوباره تلاش کنید'
        ];
    }

    // ثبت نام باربری
    public function registerBearingInWeb(Request $request)
    {

        $request->mobileNumber = ParameterController::convertNumbers($request->mobileNumber);

        $rules = [
            'title' => 'required|min:2|max:100',
            'operatorName' => 'required|min:2|max:100',
            'registrationNumber' => 'required',
            'mobileNumber' => 'required|min:11|max:11',
            'phoneNumber' => 'required|min:11|max:11',
            'city' => 'required|min:1|integer|'
        ];
        $message = [
            'required' => 'این فیلد الزمامی می باشد',

            'title.min' => 'حداقل طول نام باید 2 کاراکتر باشد',
            'title.max' => 'حداکثر طول نام باید 16 کاراکتر باشد',

            'operatorName.min' => 'حداقل طول نام خانوادگی باید 2 کاراکتر باشد',
            'operatorName.max' => 'حداکثر طول نام خانوادگی باید 16 کاراکتر باشد',

            'mobileNumber.min' => 'شماره موبایل باید 11 رقم باشد',
            'mobileNumber.max' => 'شماره موبایل باید 11 رقم باشد',

            'phoneNumber.min' => 'شماره تلفن ثابت باید 11 رقم باشد',
            'phoneNumber.max' => 'شماره تلفن ثابت باید 11 رقم باشد',

            'city.min' => 'لطفا شهر خود را انتخاب نمایید',
        ];

        $this->validate($request, $rules, $message);


        $errors = [];

        $checkRegistrationNumber = Bearing::where('registrationNumber', $request->registrationNumber)->count();
        if ($checkRegistrationNumber > 0) {
            $errors['registrationNumber'] = 'شماره ثبت تکراری می باشد';
        }
        $checkMobileNumber = Bearing::where('mobileNumber', $request->mobileNumber)->count();
        if ($checkMobileNumber > 0) {
            $errors['mobileNumber'] = 'شماره همراه تکراری می باشد';
        }

        $checkPhoneNumber = Bearing::where('phoneNumber', $request->phoneNumber)->count();
        if ($checkPhoneNumber > 0) {
            $errors['phoneNumber'] = 'شماره ثابت تکراری می باشد';
        }

        if ($request->city == 0)
            $errors['city'] = 'لطفا شهر را انتخاب نمایید';


        if (count($errors))
            return redirect()->back()->withErrors($errors);

        // ذخیره باربری جدید
        $bearing = new Bearing();
        $bearing->title = $request->title;
        $bearing->operatorName = $request->operatorName;
        $bearing->mobileNumber = $request->mobileNumber;
        $bearing->phoneNumber = $request->phoneNumber;
        $bearing->registrationNumber = $request->registrationNumber;
        $bearing->marketerCode = $request->marketerCode;
        $bearing->city_id = $request->city;
        $bearing->state_id = AddressController::geStateIdFromCityId($request->city);
        $bearing->save();

        if ($bearing) {
            ActivationCode::where('mobileNumber', '=', $request->mobileNumber)->delete();

            $code = rand(10000, 99999);
            $userType = 1;
            $activationCode = new ActivationCode();
            $activationCode->mobileNumber = $request->mobileNumber;
            $activationCode->code = $code;
            $activationCode->userType = $userType;
            $activationCode->save();

            $input_data = array(
                'verification_code' => $code
            );
            SMSController::sendSMSWithPattern($request->mobileNumber, "uazjh2qxqy7eb06", $input_data);
            $mobileNumber = $request->mobileNumber;

            return view('auth.sendActivationCode', compact('userType', 'mobileNumber'));
        }

        $message[0] = 'خطا! در ذخیره اطلاعات لطفا دوباره تلاش کنید';
        return [
            'result' => UN_SUCCESS,
            'message' => $message
        ];
    }

    // افزودن اپراتور جدید
    public function addNewOperator(Request $request)
    {

        $rules = [
            'name' => 'required|min:2|max:16',
            'lastName' => 'required|min:2|max:16',
            'nationalCode' => 'required|min:10|max:10|unique:users',
            'email' => 'required|unique:users',
            'mobileNumber' => 'required|min:11|max:11|unique:users',
            'role' => 'required',
            'password' => 'required|min:6|max:16|same:password_confirmation',
            'password_confirmation' => 'required|min:6|max:16',
        ];
        $message = [
            'required' => 'این فیلد الزمامی می باشد',
            'name.min' => 'حداقل طول نام باید 2 کاراکتر باشد',
            'name.max' => 'حداکثر طول نام باید 16 کاراکتر باشد',

            'lastName.min' => 'حداقل طول نام خانوادگی باید 2 کاراکتر باشد',
            'lastName.max' => 'حداکثر طول نام خانوادگی باید 16 کاراکتر باشد',

            'nationalCode.min' => 'کد ملی باید 10 رقم باشد',
            'nationalCode.max' => 'کد ملی باید 10 رقم باشد',
            'nationalCode.unique' => 'کد ملی تکراری می باشد',

            'mobileNumber.min' => 'شماره موبایل باید 11 رقم باشد',
            'mobileNumber.max' => 'شماره موبایل باید 11 رقم باشد',
            'mobileNumber.unique' => 'شماره موبایل تکراری می باشد',

            'role.min' => 'نوع کاربر می بایست انتخاب شود',
            'role.max' => 'نوع کاربر می بایست انتخاب شود',

            'password.min' => 'رمز ورود باید حداقل 6 کاراکتر باشد',
            'password.max' => 'رمز ورود باید حداکثر 16 کاراکتر باشد',

            'password_confirmation.min' => 'تکرار رمز ورود باید حداقل 6 کاراکتر باشد',
            'password_confirmation.max' => 'تکرار رمز ورود باید حداکثر 16 کاراکتر باشد',
            'password.same' => 'رمز ورود و تکرار رمز ورود باهم برابر نیستند',

            'email.unique' => 'ایمیل تکراری می باشد',
            'email.required' => 'ایمیل الزامی می باشد'
        ];
        $this->validate($request, $rules, $message);

        try {

            $role = Role::find($request->role);

            $pic = $request->file('pic');

            // ذخیره کاربر
            $user = new User();
            $user->name = $request->name;
            $user->lastName = $request->lastName;
            $user->pic = $this->savePicOfUsers($pic);
            $user->nationalCode = $request->nationalCode;
            $user->mobileNumber = ParameterController::convertNumbers($request->mobileNumber);
            $user->role = $role->role;
            $user->email = $request->email;
            $user->password = bcrypt($request->password);
            $user->save();

            // ذخیره نقش کاربر
            $role_user = new RoleUser();
            $role_user->user_id = $user->id;
            $role_user->role_id = $request->role;
            $role_user->save();

            $message = 'اپراتور جدید ذخیره شد';
            $roles = Role::all();

            return view('admin/addNewOperatorForm', compact('message', 'roles'));
        } catch (\Exception $exception) {
        }

        return back()->with('danger', 'خطایی رخ داده لطفا دوباره تلاش کنید');
    }

    // ثبت نام مشتری
    public function registerCustomer(Request $request, $mode = "app")
    {

        $request->mobileNumber = ParameterController::convertNumbers($request->mobileNumber);

        $customerOld = Customer::where('mobileNumber', $request->mobileNumber)
            ->orWhere('nationalCode', $request->nationalCode)
            ->first();

        if (isset($customerOld)) {
            $message[0] = 'خطا! در ذخیره اطلاعات لطفا دوباره تلاش کنید';
            return [
                'result' => UN_SUCCESS,
                'message' => $message
            ];
        }

        try {

            DB::beginTransaction();

            $customer = new Customer();
            $customer->name = $request->name;
            $customer->lastName = $request->lastName;
            $customer->mobileNumber = $request->mobileNumber;
            $customer->marketerCode = $request->marketerCode;
            $customer->nationalCode = $request->nationalCode;
            $customer->userType = $request->userType;
            $customer->freeCalls = CUSTOMER_FREE_DRIVER_CALLS;
            $customer->freeLoads = CUSTOMER_FREE_LOADS;
            $customer->callsDate = date("Y-m-d H:i:s", time() + 30 * 24 * 60 * 60);
            $customer->activeDate = date("Y-m-d H:i:s", time() + 30 * 24 * 60 * 60);
            $customer->save();

            // آپلود تصویر کارت ملی کاربر حقیقی
            $nationalCardPic = null;
            if (isset($request->nationalCardPic) && $request->nationalCardPic != 'noImage' && $mode == "app") {
                $nationalCardPic = "pictures/nationalCardPic/" . sha1(time() . $customer->id) . ".jpg";
                file_put_contents($nationalCardPic, base64_decode($request->nationalCardPic));
            } else if ($mode == "web") {
                $picture = $request->file('nationalCardPic');
                if (strlen($picture)) {
                    $fileType = $picture->guessClientExtension();
                    $nationalCardPic = sha1(time() . $customer->id) . "." . $fileType;
                    if ($picture->isValid() && ($fileType == 'jpg' || $fileType == 'jpeg' || $fileType == 'gif' || $fileType == 'png' || $fileType == 'bmp')) {
                        $picture->move('pictures/nationalCardPic', $nationalCardPic);
                        $nationalCardPic = "pictures/nationalCardPic/" . $nationalCardPic;
                    }
                }
            }

            $customer->nationalCardPic = $nationalCardPic;
            $customer->save();

            if ($customer) {
                ActivationCode::where('mobileNumber', '=', $request->mobileNumber)->delete();

                // ذخیره اطلاعات شخصیت حقوقی
                if (isset($request->userType) && $request->userType == 'legalPersonality') {
                    $legalPersonality = new LegalPersonality();
                    $legalPersonality->customer_id = $customer->id;
                    $legalPersonality->companyName = $request->companyName;
                    $legalPersonality->email = $request->email;
                    $legalPersonality->nationalID = $request->nationalID;
                    $legalPersonality->registrationNumber = $request->registrationNumber;
                    $legalPersonality->phoneNumber = $request->phoneNumber;
                    $legalPersonality->cityCode = $request->cityCode;
                    $legalPersonality->companyType = $request->companyType;
                    $legalPersonality->address = $request->address;
                    $legalPersonality->save();
                }

                DB::commit();

                return [
                    'result' => SUCCESS,
                    'mobileNumber' => $request->mobileNumber,
                    'id' => $customer->id
                ];
            }
        } catch (\Exception $exception) {
            Log::emergency($exception->getMessage());
            DB::rollBack();
        }
        $message[0] = 'خطا! در ذخیره اطلاعات لطفا دوباره تلاش کنید';
        return [
            'result' => UN_SUCCESS,
            'message' => $message
        ];
    }

    public function registerCustomerInWeb(Request $request)
    {


        $rules = [
            'name' => 'required|min:2|max:20',
            'lastName' => 'required|min:2|max:20',
            'mobileNumber' => 'required|regex:/(09)[0-9]{9}/',
            //            'nationalCode' => 'required|min:10|max:10'
        ];
        $message = [
            'name.required' => 'لطفا نام را وارد نمایید',
            'name.min' => 'نام حداقل باید 2 حرف باشد',
            'name.max' => 'نام حداکثر باید 20 حرف باشد',
            'lastName.required' => 'لطفا نام خانواگی را وارد نمایید',
            'lastName.min' => 'نام خانوادگی حداقل باید 2 حرف باشد',
            'lastName.max' => 'نام خانوادگی حداکثر باید 20 حرف باشد',
            'mobileNumber.required' => 'شماره همراه الزامی می باشد',
            'mobileNumber.min' => 'شماره تلفن صحیح نمی باشد',
            'mobileNumber.max' => 'شماره تلفن صحیح نمی باشد',
            'mobileNumber.regex' => 'شماره تلفن صحیح نمی باشد',
            //            'nationalCode.required' => 'کدملی الزامی می باشد',
            //            'nationalCode.min' => 'کدملی صحیح نیست',
            //            'nationalCode.max' => 'کدملی صحیح نیست'
        ];

        $this->validate($request, $rules, $message);

        $errors = [];

        //        $checkNationalCode = Customer::where('nationalCode', $request->nationalCode)->count();
        //        if ($checkNationalCode > 0) {
        //            $errors['nationalCode'] = 'کدملی تکراری می باشد';
        //        }
        $checkMobileNumber = Customer::where('mobileNumber', $request->mobileNumber)->count();
        if ($checkMobileNumber > 0) {
            $errors['mobileNumber'] = 'شماره همراه تکراری می باشد';
        }

        if (count($errors))
            return redirect()->back()->withErrors($errors);

        $result = $this->registerCustomer($request, "web");

        if ($result['result'] == SUCCESS) {

            ActivationCode::where('mobileNumber', '=', $request->mobileNumber)->delete();

            $code = rand(10000, 99999);
            $userType = 2;
            $activationCode = new ActivationCode();
            $activationCode->mobileNumber = ParameterController::convertNumbers($request->mobileNumber);
            $activationCode->code = $code;
            $activationCode->userType = $userType;
            $activationCode->save();

            $input_data = array(
                'verification_code' => $code
            );
            SMSController::sendSMSWithPattern($request->mobileNumber, "uazjh2qxqy7eb06", $input_data);
            $mobileNumber = $request->mobileNumber;

            return view('auth.sendActivationCode', compact('userType', 'mobileNumber'));
        }

        $message = 'خطا! در ذخیره اطلاعات لطفا دوباره تلاش کنید';
        $alert = 'alert-danger';
        return view('auth.registerCustomer', compact('message', 'alert'));
    }


    private function savePicOfUsers($picture)
    {
        $picName = 'user.png';
        if (strlen($picture)) {
            $fileType = $picture->guessClientExtension();
            if ($picture->isValid() && ($fileType == 'jpg' || $fileType == 'jpeg' || $fileType == 'gif' || $fileType == 'png' || $fileType == 'bmp')) {
                $picName = sha1(time()) . "." . $fileType;
                $picture->move('pictures/users', $picName);
            }
        }

        return $picName;
    }

    // ثبت نام راننده
    public function registerDriverInWeb(Request $request)
    {

        $name = $request->name;
        $lastName = $request->lastName;
        $nationalCode = $request->nationalCode;
        $mobileNumber = ParameterController::convertNumbers($request->mobileNumber);
        $smartCode = $request->smartCode;
        $fleet_id = $request->fleet_id;
        $marketerCode = $request->marketerCode;

        $countOfNationalCode = Driver::where('nationalCode', $nationalCode)->count();
        $countOfSmartCode = Driver::where('smartCode', $smartCode)->count();
        $countOfMobileNumber = Driver::where('mobileNumber', $mobileNumber)->count();

        $message = '';
        if ($countOfMobileNumber > 0) {
            $message = 'شماره همراه تکراری می باشد';
        }
        if ($countOfNationalCode > 0) {
            $message .= ' کدملی تکراری می باشد';
        }
        if ($countOfSmartCode > 0) {
            $message .= ' کدهوشمند تکراری می باشد';
        }

        if ($countOfMobileNumber > 0 || $countOfNationalCode > 0 || $countOfSmartCode > 0) {
            return [
                'result' => UN_SUCCESS,
                'message' => $message
            ];
        }

        // If driver send marketer code, check the code is exist
        if (strlen($marketerCode) > 0 && $marketerCode != null && $marketerCode != 0 && MarketerController::checkMarketerCodeIsExist($marketerCode) == 0) {
            return [
                'result' => UN_SUCCESS,
                'message' => 'کد معرف معتبر نمی باشد'
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
}
