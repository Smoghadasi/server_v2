<?php

namespace App\Http\Controllers;

use App\Models\AppVersion;
use App\Models\Bearing;
use App\Models\ContactUs;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Load;
use App\Models\LoadBackup;
use App\Models\SiteOption;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home');
    }

    public function dashboard()
    {
        $khavars = Driver::where('fleet_id', 75)->get();
        $baghals = Driver::where('fleet_id', 44)->get();
        $rolbars = Driver::where('fleet_id', 62)->get();

        foreach ($khavars as $khavar) {
            $khavar->fleet_id = 47;
            $khavar->save();
        }
        foreach ($baghals as $baghal) {
            $baghal->fleet_id = 45;
            $baghal->save();
        }
        foreach ($rolbars as $rolbar) {
            $rolbar->fleet_id = 59;
            $rolbar->save();
        }

        if (in_array('dashboard', auth()->user()->userAccess)) {
            try {
                $cargoAcceptsCount = Load::where('status', BEFORE_APPROVAL)->count();
                $countOfLoads = LoadBackup::count();
                $countOfBearings = Bearing::count();
                $countOfCustomers = Customer::count();
                $countOfContactUses = ContactUs::count();
                $countOfDrivers = Driver::count();

                $users = UserController::getOnlineAndOfflineUsers();

                return view('dashboard', compact('countOfLoads', 'countOfBearings', 'countOfCustomers', 'countOfContactUses', 'countOfDrivers', 'users', 'cargoAcceptsCount'));
            } catch (\Exception $exception) {
                return $exception->getMessage();
            }
        } else {
            return redirect('/dashboardOpererator');
        }
    }

    public function changeSiteOption($option)
    {
        $siteOption = SiteOption::first();
        switch ($option) {
            case 'newLoadAutoAccept':
                $siteOption->newLoadAutoAccept = !$siteOption->newLoadAutoAccept;
                break;
            case 'driverAutoActive':
                $siteOption->driverAutoActive = !$siteOption->driverAutoActive;
                break;
            case 'transportationCompanyAutoActive':
                $siteOption->transportationCompanyAutoActive = !$siteOption->transportationCompanyAutoActive;
                break;
        }
        $siteOption->save();

        return back();
    }

    /**************************************************************************************************************/
    /**************************************************************************************************************/

    public function appVersions()
    {
        $appVersion = AppVersion::orderby('id', 'desc')->first();
        if (!isset($appVersion->driverVersion)) {
            $appVersion = new AppVersion();
            $appVersion->driverVersion = 0;
            $appVersion->transportationCompanyVersion = 0;
            $appVersion->cargoOwnerVersion = 0;
            $appVersion->save();
        }

        $driverVersions = Driver::select('version', DB::raw('count(*) as total'))
            ->groupBy('version')
            ->orderBy('version', 'desc')
            ->get();


        return view('admin.appVersions', compact('appVersion', 'driverVersions'));
    }

    public function storeAppVersions(Request $request)
    {

        try {
            $appVersion = new AppVersion();
            $appVersion->driverVersion = $request->driverVersion;
            $appVersion->transportationCompanyVersion = $request->transportationCompanyVersion;
            $appVersion->cargoOwnerVersion = $request->cargoOwnerVersion;
            $appVersion->save();
        } catch (\Exception $exception) {
        }
        return back()->with('success', 'ذخیره شد');
    }
}
