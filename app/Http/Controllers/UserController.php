<?php

namespace App\Http\Controllers;

use App\Models\Bearing;
use App\Models\BlockedIp;
use App\Models\BlockPhoneNumber;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Fleet;
use App\Models\Load;
use App\Models\LoginHistory;
use App\Models\OperatorCargoListAccess;
use App\Models\Role;
use App\Models\User;
use Composer\Util\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // خروج از بخش کاربری
    public function logout()
    {
        LoginHistory::create(
            [
                'user_id' => Auth::user()->id,
                'ip_address' => request()->ip(),
                'status' => 1,
                'action' => 0,
            ]
        );
        Auth::logout();
        return redirect(url('/'));
    }

    // نمایش لیست اپراتور ها
    public function operators()
    {
        $users = User::all();
        $fleets = Fleet::where('parent_id', '>', 0)->get();
        return view('admin.operators', compact('users', 'fleets'));
    }

    // فرم افزودن اپراتور
    public function addNewOperatorForm()
    {
        $roles = Role::all();
        return view('admin.addNewOperatorForm', compact('roles'));
    }

    // اطلاعات اپراتور
    public function operatorInfo($id)
    {
        $user = User::where('id', $id)->first();

        return view('admin/operatorInfo', compact('user'));
    }

    // تغییر وضعیت اپراتور
    public function changeOperatorStatus(User $user)
    {
        if ($user->status == ACTIVE)
            $user->status = DE_ACTIVE;
        else
            $user->status = ACTIVE;

        $user->save();

        return back()->with('success', 'تغییر وضعیت اپراتور انجام شد');
    }

    // چک کردن نقش ادمین
    public static function checkAdminRole($id)
    {
        $user = User::find($id);

        if (\auth()->user()->role == ROLE_ADMIN)
            return true;
        return false;
    }

    // نمایش داشبورد کاربران
    public function displayUsersDashboard()
    {
        if (\auth('bearing')->check()) {
            return view('users.dashboardTransportaionCompany');
            return redirect(url('user/newLoads'));
        } else if (\auth('customer')->check()) {
            return view('users.dashboard');
        } else if (\auth('driver')->check()) {
        } else if (\auth('marketer')->check()) {
        } else {
            return view('auth/sendMobileNumberOfUserToLogin');
        }
    }

    // نمایش داشبورد کاربران
    public function wallet()
    {

        if (\auth('bearing')->check()) {
            $bearing_id = auth('bearing')->id();
            $bearing = Bearing::where('id', $bearing_id)->select('wallet')->first();
            $wallet = $bearing->wallet;
            return view('users.wallet', compact('wallet'));
        } else if (\auth('customer')->check()) {
        } else if (\auth('driver')->check()) {
        } else if (\auth('marketer')->check()) {
        } else {
            return response()->view('errors/404');
        }
    }

    // نمایش پروفایل
    public function profile()
    {

        if (\auth('bearing')->check()) {
            $bearing_id = \auth('bearing')->id();
            $bearing = Bearing::where('id', $bearing_id)->first();
            $userType = 'bearing';
            return view('users/profile', compact('bearing', 'userType'));
        } else if (\auth('customer')->check()) {
            $customer_id = \auth('customer')->id();
            $customer = Customer::where('id', $customer_id)->first();
            $userType = 'customer';
            return view('users/profile', compact('customer', 'userType'));
        } else if (\auth('driver')->check()) {
        } else if (\auth('marketer')->check()) {
        } else {
            return response()->view('errors/404');
        }
    }


    public function saveVersion(Request $request)
    {
        $version = $request->version;
        $userType = $request->userType;


        if ($userType == 'driver') {
            $driver_id = $request->driver_id;
            Driver::where('id', $driver_id)->update(['version' => $version]);
        } else if ($userType == 'bearing') {
            $bearing_id = $request->bearing_id;
            Bearing::where('id', $bearing_id)->update(['version' => $version]);
        } else if ($userType == 'customer') {
            $customer_id = $request->customer_id;
            Customer::where('id', $customer_id)->update(['version' => $version]);
        }

        return ['result' => 1];
    }

    // حذف اپراتور
    public function removeOperator(User $user)
    {
        $user->delete();

        return back()->with('success', 'اپراتور مورد نظر حذف شد');
    }

    // پروفایل ادمین و پراتور
    public function adminProfile()
    {
        return view('admin.profile');
    }

    //
    public function restPassword(Request $request, User $user)
    {
        $user->password = Hash::make($request->password);
        $user->save();
        return back()->with('success', 'رمز جدید ثبت شد');
    }

    public function operatorAccess(Request $request, User $user)
    {
        try {
            $access = '';
            foreach ($request->all() as $key => $item)
                $access .= $key . ',';

            $user->access = $access;
            $user->save();

            return back()->with('success', 'دسترسی ها ثبت شد');
        } catch (\Exception $exception) {
            return $exception;
        }

        return back()->with('danger', 'خطا در ثبت دسترسی ها');
    }


    /*****************************************************************************************/
    // شماره تلفن های مسدود شده

    public static function getOnlineAndOfflineUsers()
    {
        return User::select("*")
            ->whereNotNull('last_seen')
            ->orderBy('last_seen', 'DESC')
            ->get();
    }

    /*************************************************************************************************
     * ************************************************************************************************* */

    /**
     * لیست آی پی های مسدود
     * @return \Illuminate\Http\Response
     */
    public function blockedIps()
    {
        $blockedips = BlockedIp::orderBy('id', 'desc')->paginate(20);

        return view('admin.blockedIPs', compact('blockedips'));
    }


    /**
     * مسدود کردن ip کاربران
     *
     * @return \Illuminate\Http\Response
     */
    public function blockUserIp($user_id, $userType, $ip)
    {
        $blockedIp = BlockedIp::updateOrCreate([
            'user_id' => $user_id,
            'ip' => $ip,
            'userType' => $userType,
        ]);
        if (isset($blockedIp->id))
            return back()->with('success', 'IP کاربر مورد نظر مسدود شد.');

        return back()->with('danger', 'IP کاربر مورد نظر مسدود نشد، دوباره تلاش کنید.');
    }

    /**
     * حذف از لیست IP های مسدود
     * @return Response
     */
    public function unBlockUserIp($user_id, $userType)
    {

        BlockedIp::where([
            ['user_id', $user_id],
            ['userType', $userType],
        ])->delete();

        return back()->with('success', 'IP کاربر مورد نظر از لیست مسدود ها حذف شد.');
    }
}
