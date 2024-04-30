<?php

namespace App\Http\Controllers;

use App\Models\ActivationCode;
use App\Models\Bearing;
use App\Models\City;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Marketer;
use App\Models\Owner;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{

    // درخوسات کد فعال سازی
    public function requestActivationCode(Request $request)
    {
        $mobileNumber = ParameterController::convertNumbers($request->mobileNumber);

        if (strlen($mobileNumber) == 11) {

            if (SMS_PANEL == 'SMSIR') {
                if ($this->createAndSendActivationCodeSmsir($mobileNumber) != 1) {
                    return [
                        'result' => UN_SUCCESS,
                        'message' => 'خطا در ارسال پیامک'
                    ];
                }
                return response()->json(['result' => SUCCESS]);
            } else {
                if ($this->createAndSendActivationCode($mobileNumber) != 1) {
                    return [
                        'result' => UN_SUCCESS,
                        'message' => 'خطا در ارسال پیامک'
                    ];
                }
                return response()->json(['result' => SUCCESS]);
            }
        }

        return [
            'result' => UN_SUCCESS,
            'message' => 'شماره ارسال شده صحیح نمی باشد'
        ];
    }

    // درخوسات کد فعال سازی
    public function requestActivationCodeOwner(Request $request)
    {
        $mobileNumber = ParameterController::convertNumbers($request->mobileNumber);
        $checkBlock = Owner::where('mobileNumber', $mobileNumber)->first();
        if (strlen($mobileNumber) == 11) {
            if (isset($checkBlock) && $checkBlock->status == 0) {
                return [
                    'result' => UN_SUCCESS,
                    'message' => 'لطفا با پشتیبانی تماس بگیرید'
                ];
            }

            if (SMS_PANEL == 'SMSIR') {
                if ($this->createAndSendActivationCodeSmsir($mobileNumber) != 1) {
                    return [
                        'result' => UN_SUCCESS,
                        'message' => 'خطا در ارسال پیامک'
                    ];
                }
                return response()->json(['result' => SUCCESS]);
            } else {
                if ($this->createAndSendActivationCode($mobileNumber) != 1) {
                    return [
                        'result' => UN_SUCCESS,
                        'message' => 'خطا در ارسال پیامک'
                    ];
                }
                return response()->json(['result' => SUCCESS]);
            }
            return response()->json(['result' => SUCCESS]);
        }

        return [
            'result' => UN_SUCCESS,
            'message' => 'شماره ارسال شده صحیح نمی باشد'
        ];
    }


    // ایجاد و ارسال کد فعال سازی فراز اس ام اس
    private function createAndSendActivationCodeSmsir($mobileNumber)
    {

        ActivationCode::where('mobileNumber', '=', $mobileNumber)->delete();

        $code = rand(10000, 99999);

        $activationCode = new ActivationCode();
        $activationCode->mobileNumber = $mobileNumber;
        $activationCode->code = $code;
        $activationCode->save();


        // $input_data = array(
        //     'verification_code' => $code
        // );
        SMSController::sendSMSWithPatternSmsir($mobileNumber, $code);
        return true;
    }

    // اعتبار سنجی کد فعال سازی
    public function verifyActivationCode($mobileNumber, $code)
    {
        $activationCode = ActivationCode::where([
            ['mobileNumber', '=', $mobileNumber],
            ['code', '=', $code]
        ])->first();

        if ($activationCode)
            return SUCCESS;

        return UN_SUCCESS;
    }

    // اعتبار سنجی کد فعال سازی
    public function verifyActivationCodeForBearing(Request $request)
    {
        $mobileNumber = ParameterController::convertNumbers($request->mobileNumber);
        $code = $request->code;

        if ($this->verifyActivationCode($mobileNumber, $code) == SUCCESS) {

            $bearing = Bearing::where('mobileNumber', '=', $mobileNumber)->first();

            if ($bearing) {
                // قبلا این باربری ذخیره شده است
                return [
                    'result' => IS_MEMBER,
                    'id' => $bearing->id
                ];
            }
            // قبلا این باربری ذخیره نشده است
            return [
                'result' => NOT_MEMBER
            ];
        }
        return [
            'result' => UN_SUCCESS,
            'message' => 'کد فعال سازی معتبر نیست!'
        ];
    }

    // اعتبار سنحی کد برای راننده
    public function verifyActivationCodeForDriver(Request $request)
    {
        $mobileNumber = ParameterController::convertNumbers($request->mobileNumber);
        $code = $request->code;


        if ($this->verifyActivationCode($mobileNumber, $code) == SUCCESS) {

            $driver = Driver::where('mobileNumber', '=', $mobileNumber)->first();

            if ($driver) {
                // قبلا این راننده ذخیره شده است
                return [
                    'result' => IS_MEMBER,
                    'id' => $driver->id

                ];
            }
            // این راننده ذخیره نشده است
            return [
                'result' => NOT_MEMBER
            ];
        }
        return [
            'result' => UN_SUCCESS,
            'message' => 'کد فعال سازی معتبر نیست!'
        ];
    }

    // بررسی کد فعال سازی
    public function verifyActivationCodeForCustomer(Request $request)
    {
        $mobileNumber = ParameterController::convertNumbers($request->mobileNumber);
        $code = $request->code;

        $activationCode = ActivationCode::where([
            ['mobileNumber', '=', $mobileNumber],
            ['code', '=', $code]
        ])->count();

        if ($activationCode > 0) {

            $customer = Customer::where('mobileNumber', '=', $mobileNumber)->first();
            if ($customer) {
                return [
                    'result' => IS_MEMBER,
                    'id' => $customer->id
                ];
            }
            return [
                'result' => NOT_MEMBER
            ];
        }
        return [
            'result' => UN_SUCCESS,
            'message' => 'کد فعال سازی معتبر نیست!'
        ];
    }

    // بررسی کد فعال سازی برای باربری و صاحب بار
    public function verifyActivationCodeForCustomerBearing(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobileNumber' => 'required',
            'code' => 'required',
        ]);
        $mobileNumber = ParameterController::convertNumbers($request->mobileNumber);
        // return $mobileNumber;
        if ($validator->fails()) {
            return response()->json('ورودی اشتباه است', Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $code = $request->code;
        $activationCode = ActivationCode::where([
            ['mobileNumber', '=', $mobileNumber],
            ['code', '=', $code]
        ])->count();
        // return $activationCode;
        if ($activationCode > 0) {
            if ($request->isOwner == 0) {
                $owner = Owner::where('mobileNumber', '=', $mobileNumber)->first();
                if ($owner) {
                    return [
                        'result' => IS_MEMBER,
                        'id' => $owner->id
                    ];
                }
                return [
                    'result' => NOT_MEMBER
                ];
            } else {
                $bearing = Bearing::where('mobileNumber', '=', $mobileNumber)->first();
                if ($bearing) {
                    // قبلا این باربری ذخیره شده است
                    return [
                        'result' => IS_MEMBER,
                        'id' => $bearing->id
                    ];
                }
                // قبلا این باربری ذخیره نشده است
                return [
                    'result' => NOT_MEMBER
                ];
            }
        } else {
            return [
                'result' => UN_SUCCESS,
                'message' => 'کد فعال سازی معتبر نیست!'
            ];
        }
    }

    // بررسی کد فعال سازی
    public function verifyActivationCodeForMarketer(Request $request)
    {
        $mobileNumber = ParameterController::convertNumbers($request->mobileNumber);
        $code = $request->code;

        if ($this->verifyActivationCode($mobileNumber, $code) == SUCCESS) {

            $marketer = Marketer::where('mobileNumber', '=', $mobileNumber)->first();

            if ($marketer) {
                return [
                    'result' => IS_MEMBER,
                    'id' => $marketer->id
                ];
            }
            return [
                'result' => NOT_MEMBER
            ];
        }
        return [
            'result' => UN_SUCCESS,
            'message' => 'کد فعال سازی معتبر نیست!'
        ];
    }



    // ایجاد و ارسال کد فعال سازی فراز اس ام اس
    private function createAndSendActivationCode($mobileNumber)
    {

        ActivationCode::where('mobileNumber', '=', $mobileNumber)->delete();

        $code = rand(10000, 99999);

        $activationCode = new ActivationCode();
        $activationCode->mobileNumber = $mobileNumber;
        $activationCode->code = $code;
        $activationCode->save();


        $input_data = array(
            'verification_code' => $code
        );
        SMSController::sendSMSWithPattern($mobileNumber, "uazjh2qxqy7eb06", $input_data);
        return true;
    }

    // ارسال شماره تلفن برای ورود (باربری، صاحب بار، راننده، بازاریاب)
    public function sendActivationCode(Request $request)
    {
        $request->mobileNumber = ParameterController::convertNumbers($request->mobileNumber);

        $rules = [
            'mobileNumber' => 'required|numeric|regex:/(09)[0-9]{9}/',
            'userType' => 'required|numeric|min:1|max:3'
        ];

        $message = [
            'mobileNumber.required' => 'شماره تلفن همراه را وارد نمایید',
            'mobileNumber.numeric' => 'شماره تلفن همراه صحیح نیست',
            'mobileNumber.regex' => 'شماره تلفن همراه صحیح نیست',
            'userType.required' => 'نوع کاربر را انتخاب نمایید',
            'userType.numeric' => 'نوع کاربر را انتخاب نمایید',
            'userType.min' => 'نوع کاربر را انتخاب نمایید',
            'userType.max' => 'نوع کاربر را انتخاب نمایید',
        ];

        $this->validate($request, $rules, $message);


        $message = '';
        $alert = 'alert-danger';


        switch ($request->userType) {
            case BEARING:
                if (Bearing::where('mobileNumber', $request->mobileNumber)->count() == 0)
                    return redirect('user/registerBearing')->with('mobileNumber', $request->mobileNumber);
                break;
            case CUSTOMER:
                if (Customer::where('mobileNumber', $request->mobileNumber)->count() == 0)
                    return redirect('user/registerCustomer')->with('mobileNumber', $request->mobileNumber);
                break;
            case DRIVER:
                if (Customer::where('mobileNumber', $request->mobileNumber)->count() == 0)
                    return redirect('user/registerDriver')->with('mobileNumber', $request->mobileNumber);
                break;
            case MARKETER:
                if (Marketer::where('mobileNumber', $request->mobileNumber)->count() == 0) {
                    $message = 'چنین بازاریابی وجود ندارد';
                    return view('auth.sendMobileNumberOfUserToLogin', compact('message', 'alert'));
                }
                break;
        }


        $result = $this->requestActivationCode($request);

        $mobileNumber = $request->mobileNumber;
        $userType = $request->userType;

        return view('auth/sendActivationCode', compact('mobileNumber', 'userType'));
    }

    // دریافت کد فعال سازی
    public function validateActivationCode(Request $request)
    {

        $request->mobileNumber = ParameterController::convertNumbers($request->mobileNumber);

        $result = '';

        switch ($request->userType) {
            case BEARING:
                $result = $this->verifyActivationCodeForBearing($request);
                if ($result['result'] == SUCCESS) {
                    $bearing = Bearing::where('mobileNumber', $request->mobileNumber)->first();
                    if ($bearing) {
                        auth('bearing')->loginUsingId($bearing->id, true);
                        return redirect(url('user'));
                    }
                } else if ($result['result'] == NOT_MEMBER) {
                    $cities = City::all();
                    $mobileNumber = $request->mobileNumber;
                    return view('auth.registerBearingForm', compact('cities', 'mobileNumber'));
                }
                break;
            case CUSTOMER:
                $result = $this->verifyActivationCodeForCustomer($request);
                if ($result['result'] == SUCCESS) {
                    $customer = Customer::where('mobileNumber', $request->mobileNumber)->first();
                    if ($customer) {
                        auth('customer')->loginUsingId($customer->id, true);
                        return redirect(url('user'));
                    } else if ($result['result'] == NOT_MEMBER) {
                        return view('users.registerCustomer');
                    }
                }
                break;
            case DRIVER:
                $result = $this->verifyActivationCodeForDriver($request);
                if ($result['result'] == SUCCESS) {
                    $driver = Driver::where('mobileNumber', $request->mobileNumber)->first();
                    if ($driver) {
                        auth('driver')->loginUsingId($driver->id, true);
                        return redirect(url('user'));
                    }
                } else if ($result['result'] == NOT_MEMBER) {
                    return view('auth.registerDriver');
                }
                break;
            case MARKETER:
                $result = $this->verifyActivationCodeForMarketer($request);
                if ($result['result'] == SUCCESS) {
                    $marketer = Marketer::where('mobileNumber', $request->mobileNumber)->first();
                    if ($marketer) {
                        auth('marketer')->loginUsingId($marketer->id);
                    } else {
                        return view('registerBearing');
                    }
                } else if ($result['result'] == NOT_MEMBER) {
                    return view('registerBearing');
                }
                break;
        }

        if ($result['result'] == 0) {
            $mobileNumber = $request->mobileNumber;
            $userType = $request->userType;
            $message = 'کدفعال سازی معتبر نمی باشد';
            return view('auth/sendActivationCode', compact('mobileNumber', 'userType', 'message'));
        }
        return $result;
    }
}
