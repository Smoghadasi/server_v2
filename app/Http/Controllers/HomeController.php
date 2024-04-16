<?php

namespace App\Http\Controllers;

use App\Models\AppVersion;
use App\Models\Bearing;
use App\Models\ContactUs;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\DriverActivity;
use App\Models\Load;
use App\Models\LoadBackup;
use App\Models\Owner;
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
        if (in_array('dashboard', auth()->user()->userAccess)) {
            try {
                $cargoAcceptsCount = Load::where('status', BEFORE_APPROVAL)->count();
                $countOfLoads = LoadBackup::count();
                $countOfBearings = Bearing::count();
                $countOfCustomers = Customer::count();
                $countOfOwners = Owner::count();
                $countOfContactUses = ContactUs::count();
                $countOfDrivers = Driver::count();

                $users = UserController::getOnlineAndOfflineUsers();

                return view('dashboard', compact(
                    'countOfLoads',
                    'countOfBearings',
                    'countOfCustomers',
                    'countOfContactUses',
                    'countOfDrivers',
                    'users',
                    'cargoAcceptsCount',
                    'countOfOwners'
                ));
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
            case 'sendBotLoadOwner':
                $siteOption->sendBotLoadOwner = !$siteOption->sendBotLoadOwner;
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

    public function driverActivityVersion($version)
    {

        $activityReportOfDriversFromPreviousMonth = [];
        for ($index = 30; $index >= 0; $index--) {
            $day = date('Y-m-d', strtotime('-' . $index . 'day', time()));
            $activityReportOfDriversFromPreviousMonth[] = [
                'label' => str_replace('-', '/', convertEnNumberToFa(gregorianDateToPersian($day, '-'))),
                'value' => DriverActivity::whereHas('driver', function ($q) use ($version) {
                    $q->where('version', $version);
                })->where([
                    ['created_at', '>', $day . ' 00:00:00'],
                    ['created_at', '<', $day . ' 23:59:59']
                ])->count()
            ];
        }
        return view(
            'admin.reporting.driverActivityReportVersion',
            compact('activityReportOfDriversFromPreviousMonth')
        );
    }

    public function searchAll(Request $request)
    {
        $owners = Owner::where('mobileNumber', 'like', '%' . $request->mobileNumber . '%')->paginate(20);
        $drivers = Driver::where('mobileNumber', 'like', '%' . $request->mobileNumber . '%')->paginate(20);
        return view('admin.searchAll', compact('drivers', 'owners'));
    }
}
