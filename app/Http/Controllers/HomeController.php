<?php

namespace App\Http\Controllers;

use App\Models\AppVersion;
use App\Models\Bearing;
use App\Models\BlockPhoneNumber;
use App\Models\ContactReportWithCargoOwner;
use App\Models\ContactUs;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\DriverActivity;
use App\Models\Load;
use App\Models\LoadBackup;
use App\Models\Owner;
use App\Models\Report;
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

        // صاحب بار
        $owners = Owner::where('mobileNumber', 'like', '%' . $request->title . '%')
            ->orWhere('nationalCode', $request->title)
            ->orWhere('name', 'like', '%' . $request->title . '%')
            ->orWhere('lastName', 'like', '%' . $request->title . '%')
            ->paginate(20);

        // شکایات راننده
        $drivers = Driver::with('provinceOwner')
            ->where('mobileNumber', 'like', '%' . $request->title . '%')
            ->orWhere('nationalCode', $request->title)
            ->orWhere('name', 'like', '%' . $request->title . '%')
            ->orWhere('lastName', 'like', '%' . $request->title . '%')
            ->paginate(20);

        // شکایات راننده
        $reportDrivers = Report::with(['cargo' => function ($query) {
            $query->withTrashed();
        }, 'driver', 'owner'])->where('type', 'driver')
            ->whereHas('driver', function ($q) use ($request) {
                $q->where('mobileNumber', $request->title);
                $q->orWhere('nationalCode', $request->title);
                $q->orWhere('name', 'like', '%' . $request->title . '%');
                $q->orWhere('lastName', 'like', '%' . $request->title . '%');
            })
            ->orderByDesc('created_at')->paginate(20);

        // شکایات صاحب بار
        $reportOwners = Report::with(['cargo' => function ($query) {
            $query->withTrashed();
        }, 'driver', 'owner'])->where('type', 'driver')
            ->whereHas('owner', function ($q) use ($request) {
                $q->where('mobileNumber', $request->title);
                $q->orWhere('nationalCode', $request->title);
                $q->orWhere('name', 'like', '%' . $request->title . '%');
                $q->orWhere('lastName', 'like', '%' . $request->title . '%');
            })
            ->orderByDesc('created_at')->paginate(20);

        // شماره های مسدودی
        $blockedPhoneNumbers = BlockPhoneNumber::orderByDesc('created_at')
            ->where('phoneNumber', 'like', '%' . $request->title . '%')
            ->orWhere('nationalCode', $request->title)
            ->orWhere('name', 'like', '%' . $request->title . '%')
            ->paginate(20);

        // پیام ها
        $messages = ContactUs::orderby('id', 'desc')
            ->where('mobileNumber', $request->title)
            ->orWhere('name', 'like', '%' . $request->title . '%')
            ->orWhere('lastName', 'like', '%' . $request->title . '%')
            ->paginate(20);

        // تماس با صاحب بار و باربری
        $contactReportWithCargoOwners = ContactReportWithCargoOwner::orderby('id', 'desc')
            ->where('mobileNumber', $request->title)
            ->orWhere('nameAndLastName', $request->title)
            ->paginate(20);

        return view('admin.searchAll', compact([
            'drivers',
            'owners',
            'reportDrivers',
            'reportOwners',
            'blockedPhoneNumbers',
            'messages',
            'contactReportWithCargoOwners'
        ]));
    }
}
