<?php

namespace App\Http\Controllers;

use App\Models\User;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AuthorizeController extends Controller
{
    public function loginPost(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        $credentials = $request->only('email', 'password');
        $user = User::where('email', $request->email)->first();
        if ($user != null) {
            if ($user->lockDate < now()) {
                // return $user;
                if (Auth::attempt($credentials)) {
                    $user->numOfRequest = null;
                    $user->lockDate = null;
                    $user->save();
                    return redirect('/dashboard');
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
                    return redirect(route('login'))->with('danger', 'شما تا تاریخ' . $user->lockDate . ' مسدود شده اید. ');
                }
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
                return redirect(route('login'))->with('danger', 'شما تا تاریخ' . $user->lockDate . ' مسدود شده اید. ');
            }
            // Auth::logout();
        } else {
            return redirect(route('login'))->with('danger', 'نام کاربری یا عبور اشتباه است ');
        }
    }
}
