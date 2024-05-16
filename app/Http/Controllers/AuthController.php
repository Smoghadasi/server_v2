<?php

namespace App\Http\Controllers;

use App\Models\ActivationCode;
use App\Models\LoginHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function checkUser(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
            'captcha' => 'required|captcha'
        ]);
        $user = User::where('email', $request->email)->first();
        if ($user && Hash::check($request->password, $user->password)) {
            if ($user->lockDate < now()) {
                $mobileNumber = ParameterController::convertNumbers($user->mobileNumber);
                $sms = new User();
                if (SMS_PANEL == 'SMSIR') {
                    $rnd = $sms->mobileSmsIr($mobileNumber);
                } else {
                    $rnd = $sms->mobileSms($mobileNumber);
                }
                ActivationCode::where('mobileNumber', $mobileNumber)->delete();
                $activationCode = new ActivationCode();
                $activationCode->mobileNumber = $mobileNumber;
                $activationCode->code = $rnd;
                $activationCode->save();
                $user->numOfRequest = null;
                $user->lockDate = null;
                $user->save();
                return response()->json([
                    'status' => 200,
                    'response' => $mobileNumber
                ]);
            } else {
                $user->numOfRequest += 1;
                if ($user->numOfRequest == 1) {
                    $user->lockDate = Carbon::parse($user->lockDate)->addMinutes(5);
                } elseif ($user->numOfRequest == 2) {
                    $user->lockDate = Carbon::parse($user->lockDate)->addMinutes(20);
                } elseif ($user->numOfRequest >= 3) {
                    $user->lockDate = Carbon::parse($user->lockDate)->addMinutes(60);
                }
                $user->save();
                return response()->json([
                    'status' => 403,
                    'response' => 'شما تا تاریخ ' . $user->lockDate . ' مسدود شده اید. '
                ]);
            }
        } else {
            if ($user) {
                $user->numOfRequest += 1;
                if ($user->numOfRequest == 1) {
                    $user->lockDate = Carbon::parse($user->lockDate)->addMinutes(5);
                } elseif ($user->numOfRequest == 2) {
                    $user->lockDate = Carbon::parse($user->lockDate)->addMinutes(20);
                } elseif ($user->numOfRequest >= 3) {
                    $user->lockDate = Carbon::parse($user->lockDate)->addMinutes(60);
                }
                $user->save();
                return response()->json([
                    'status' => 403,
                    'response' => 'شما تا تاریخ' . $user->lockDate . ' مسدود شده اید. '
                ]);
            }
            LoginHistory::create(
                [
                    'ip_address' => request()->ip(),
                    'status' => 0,
                    'action' => 1,
                    'unsuccess' => $request->email,
                ]
            );
            return response()->json([
                'status' => 422,
                'response' => 'نام کاربری و رمز عبور اشتباه است'
            ]);
        }
    }

    public function checkMobile(Request $request)
    {
        $request->validate([
            'mobile' => 'required'
        ]);
        $user = User::where('mobileNumber', $request->mobile)->first();
        if ($user) {
            if ($user->mobileNumber == null || $user->mobileNumber == "") {
                return response()->json(['code' => 422, 'success' => 'شماره موبایل ثبت نشده است لطفا با پشتیبانی تماس بگیرید.']);
            }
            $sms = new User;
            $rnd = $sms->mobileSms($user->mobileNumber);
            return response()->json([
                'code' => 200,
                'sms' => $rnd,
                'response' => 'با موفقیت ارسال شد'
            ]);
        } else {
            return response()->json(['code' => 400, 'success' => 'کاربر مورد نظر یافت نشد.']);
        }
    }
    // درخواست کد فعال سازی برای احراز هویت
    public function checkActivationCode(Request $request)
    {
        $mobileNumber = ParameterController::convertNumbers($request->mobileNumber);

        if (strlen($mobileNumber) == 11) {
            if (ActivationCode::where('mobileNumber', '=', $mobileNumber)->where('code', $request->code)->count() > 0) {
                $credentials = $request->only('mobileNumber','password');

                Auth::attempt($credentials);
                LoginHistory::create(
                    [
                        'user_id' => auth()->user()->id,
                        'ip_address' => request()->ip(),
                        'status' => 1,
                        'action' => 1
                    ]
                );
                return response()->json([
                    'status' => 200,
                    'response' => 'با موفقیت ورود به سیستم انجام شد'
                ]);
            } else {
                return response()->json([
                    'status' => 400,
                    'response' => 'کد ارسال شده اشتباه است'
                ]);
            }
        }
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'mobile' => 'required'
        ]);
        $user = User::where('mobileNumber', $request->mobile)->first();
        $user->password = Hash::make($request->password);
        $user->save();
        return redirect()->route('login')->with('success', 'رمز عبور شما با موفقیت تغییر کرد');
    }
}
