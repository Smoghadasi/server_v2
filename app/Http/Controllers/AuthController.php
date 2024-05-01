<?php

namespace App\Http\Controllers;

use App\Models\User;
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
        ]);

        // $credentials = $request->only('email', 'password');
        $user = User::where('email', $request->email)->first();

        if ($user) {
            $account = User::where('email', $request->email)->select('mobileNumber')->first();
            if ($account->mobileNumber == null || $account->mobileNumber == "") {
                return response()->json(['code' => 422, 'success' => 'شماره موبایل ثبت نشده است لطفا با پشتیبانی تماس بگیرید.']);
            }
            $sms = new User();
            if (SMS_PANEL == 'SMSIR') {
                $rnd = $sms->mobileSmsIr($account->mobileNumber);
            }else{
                $rnd = $sms->mobileSms($account->mobileNumber);
            }


            return response()->json([
                'code' => 200,
                'sms' => $rnd,
                'response' => 'با موفقیت ارسال شد'
            ]);
        } else {
            return response()->json(['code' => 400, 'success' => 'کاربر مورد نظر یافت نشد.']);
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
