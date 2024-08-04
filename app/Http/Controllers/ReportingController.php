<?php

namespace App\Http\Controllers;

use App\Models\Bearing;
use App\Models\CargoReportByFleet;
use App\Models\CityOwner;
use App\Models\ContactReportWithCargoOwner;
use App\Models\ContactReportWithCargoOwnerResult;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\DriverActivity;
use App\Models\DriverCall;
use App\Models\DriverCallCount;
use App\Models\DriverCallReport;
use App\Models\Fleet;
use App\Models\FleetLoad;
use App\Models\FleetRatioToDriverActivityReport;
use App\Models\Load;
use App\Models\LoadBackup;
use App\Models\Owner;
use App\Models\ProvinceCity;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserActivityReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReportingController extends Controller
{

    private $persianDateList;

    // خلاصه گزارش روز
    public function summaryOfDaysReport()
    {

        $todayDate = date('Y-m-d', time()) . ' 00:00:00';
        $yesterdayDate = date('Y-m-d', strtotime('-1 day', time())) . ' 00:00:00';
        $weekDate = date('Y-m-d', strtotime('last friday')) . ' 23:59:59';
        $monthDate = $this->getMonthDate();

        $drivers = [
            'total' => Driver::count(),
            'toDay' => Driver::where('created_at', '>', $todayDate)->count(),
            'yesterday' => Driver::where([
                ['created_at', '>', $yesterdayDate],
                ['created_at', '<', $todayDate]
            ])->count(),
            'week' => Driver::where('created_at', '>', $weekDate)->count(),
            'month' => Driver::where('created_at', '>=', $monthDate)->count()
        ];

        $owners = [
            'total' => Owner::count(),
            'toDay' => Owner::where('created_at', '>', $todayDate)->count(),
            'yesterday' => Owner::where([
                ['created_at', '>', $yesterdayDate],
                ['created_at', '<', $todayDate]
            ])->count(),
            'week' => Owner::where('created_at', '>', $weekDate)->count(),
            'month' => Owner::where('created_at', '>=', $monthDate)->count(),
            'toDayLoads' => Load::where([
                ['created_at', '>', $todayDate],
                ['userType', ROLE_OWNER],
                ['operator_id', 0]
            ])->withTrashed()->count(),
            'yesterdayLoads' => Load::where([
                ['created_at', '>', $yesterdayDate],
                ['created_at', '<', $todayDate],
                ['userType', ROLE_OWNER],
                ['operator_id', 0]
            ])->withTrashed()->count(),
            'weekLoads' => Load::where([
                ['created_at', '>', $weekDate],
                ['userType', ROLE_OWNER],
                ['operator_id', 0]
            ])->withTrashed()->count(),
            'monthLoads' => Load::where([
                ['created_at', '>=', $monthDate],
                ['userType', ROLE_OWNER],
                ['operator_id', 0]
            ])->count()
        ];

        $cargoOwners = [
            'total' => Customer::count(),
            'toDay' => Customer::where('created_at', '>', $todayDate)->count(),
            'yesterday' => Customer::where([
                ['created_at', '>', $yesterdayDate],
                ['created_at', '<', $todayDate]
            ])->count(),
            'week' => Customer::where('created_at', '>', $weekDate)->count(),
            'month' => Customer::where('created_at', '>=', $monthDate)->count(),
            'toDayLoads' => LoadBackup::where([
                ['created_at', '>', $todayDate],
                ['userType', ROLE_CUSTOMER],
                ['operator_id', 0]
            ])->count(),
            'yesterdayLoads' => LoadBackup::where([
                ['created_at', '>', $yesterdayDate],
                ['created_at', '<', $todayDate],
                ['userType', ROLE_CUSTOMER],
                ['operator_id', 0]
            ])->count(),
            'weekLoads' => LoadBackup::where([
                ['created_at', '>', $weekDate],
                ['userType', ROLE_CUSTOMER],
                ['operator_id', 0]
            ])->count(),
            'monthLoads' => LoadBackup::where([
                ['created_at', '>=', $monthDate],
                ['userType', ROLE_CUSTOMER],
                ['operator_id', 0]
            ])->count()
        ];

        $operators = [
            'toDayLoads' => LoadBackup::where([
                ['created_at', '>', $todayDate],
                ['operator_id', '>', 0]
            ])->count(),
            'yesterdayLoads' => LoadBackup::where([
                ['created_at', '>', $yesterdayDate],
                ['created_at', '<', $todayDate],
                ['operator_id', '>', 0]
            ])->count(),
            'weekLoads' => LoadBackup::where([
                ['created_at', '>', $weekDate],
                ['operator_id', '>', 0]
            ])->count(),
            'monthLoads' => LoadBackup::where([
                ['created_at', '>=', $monthDate],
                ['operator_id', '>', 0]
            ])->count()
        ];

        $incomes = [
            'total' => Transaction::where('status', '>', 0)->sum('amount'),
            'toDay' => Transaction::where([
                ['created_at', '>', $todayDate],
                ['status', '>', 0]
            ])->sum('amount'),
            'yesterday' => Transaction::where([
                ['created_at', '>', $yesterdayDate],
                ['created_at', '<', $todayDate],
                ['status', '>', 0]
            ])->sum('amount'),
            'week' => Transaction::where([
                ['created_at', '>', $weekDate],
                ['status', '>', 0]
            ])->sum('amount'),
            'month' => Transaction::where([
                ['created_at', '>=', $monthDate],
                ['status', '>', 0]
            ])->sum('amount'),
            'drivers' => Transaction::where([
                ['created_at', '>', $weekDate],
                ['status', '>', 0],
                ['userType', ROLE_DRIVER]
            ])->sum('amount'),
            'transportationCompany' => Transaction::where([
                ['created_at', '>', $weekDate],
                ['status', '>', 0],
                ['userType', ROLE_TRANSPORTATION_COMPANY]
            ])->sum('amount'),
            'cargoOwner' => Transaction::where([
                ['created_at', '>', $weekDate],
                ['status', '>', 0],
                ['userType', ROLE_CARGo_OWNER]
            ])->sum('amount'),
        ];


        //تعداد بار ثبت شده 30 روز قبل
        $countOfLoadsInPrevious30Days = []; // $this->countOfLoadsInPrevious30Days();

        return view('admin.reporting.summaryOfDaysReport', compact('drivers', 'owners', 'cargoOwners', 'operators', 'incomes', 'countOfLoadsInPrevious30Days'));
    }

    // هزینه وایزی توسط کاربران از 60 روز قبل
    public function getDepositDee60DaysInAdvance($userType)
    {
        $incomes = [];
        for ($index = 60; $index >= 0; $index--) {
            $day = date('Y-m-d', strtotime('-' . $index . 'day', time()));
            $incomes[] = [
                'label' => str_replace('-', '/', convertEnNumberToFa(gregorianDateToPersian($day, '-'))),
                'value' => Transaction::where([
                    ['status', '>', 0],
                    ['created_at', '>', $day . ' 00:00:00'],
                    ['created_at', '<', $day . ' 23:59:59'],
                    ['userType', $userType]
                ])->sum('amount')
            ];
        }
        return $incomes;
    }


    //تعداد بار ثبت شده 30 روز قبل همه کاربران
    private function countOfLoadsInPrevious30Days()
    {
        $loadsCount = [];

        for ($day = 30; $day >= 0; $day--) {
            $dateFrom = date('Y-m-d', strtotime('-' . $day . 'day', time()));
            $dateTo = date('Y-m-d', strtotime('-' . ($day - 1) . 'day', time()));

            $value = LoadBackup::where([
                ['created_at', '>=', $dateFrom . ' 00:00:00'],
                ['created_at', '<', $dateTo . ' 00:00:00']
            ])->count();

            $loadsCount[] = [
                'value' => $value,
                'label' => str_replace('-', '/', convertEnNumberToFa(gregorianDateToPersian($dateFrom, '-')))
            ];
        }
        return $loadsCount;
    }

    /****************************************************************************************************/

    // گزارش فعالیت رانندگان
    public function driverActivityReport()
    {

        $totalDrivers = Driver::count();
        // روند افزایش راننده ها از 12 ماه قبل
        $this->persianDateList = $this->getTheListOfPersianDatesOfAYear();
        for ($index = 11; $index >= 0; $index--)
            $increaseOfDriversSince12MonthsAgo[$index] = [
                'label' => str_replace('-', '/', convertEnNumberToFa($this->persianDateList[$index])),
                'value' => $this->getIncreaseOfDriversSince12MonthsAgo($index)
            ];
        // تفکیک ناوگان ثبت نامی از ابتدا
        $separationOfTheFleetsFromTheFirst = $this->getSeparationOfTheFleetsFromTheFirst();

        // گزارش فعالیت راننده ها از ماه قبل
        $activityReportOfDriversFromPreviousMonth = $this->getActivityReportOfDriversFromPreviousMonth();

        // فعالیت به تفکیک ناوگان هفتگی
        $activityReportOfDriversFromPreviousMonthByFleets = $this->getActivityReportOfDriversFromPreviousMonthByFleets();


        // هزینه وایزی توسط راننده ها از 60 روز قبل
        $feesPaidByDrivers60DaysInAdvance = $this->getDepositDee60DaysInAdvance(ROLE_DRIVER);

        return view(
            'admin.reporting.driverActivityReport',
            compact('totalDrivers', 'activityReportOfDriversFromPreviousMonthByFleets', 'increaseOfDriversSince12MonthsAgo', 'separationOfTheFleetsFromTheFirst', 'feesPaidByDrivers60DaysInAdvance', 'activityReportOfDriversFromPreviousMonth')
        );
    }

    // روند افزایش راننده ها از 12 ماه قبل
    private function getIncreaseOfDriversSince12MonthsAgo($index)
    {
        $fromDate = persianDateToGregorian($this->persianDateList[$index] . '-01', '-');
        if ($index > 0) {
            $toDate = persianDateToGregorian($this->persianDateList[$index - 1] . '-01', '-');

            return Driver::where([
                ['created_at', '>=', $fromDate],
                ['created_at', '<', $toDate]
            ])->count();
        }

        return Driver::where('created_at', '>=', $fromDate)->count();
    }

    // دریافت لیست تاریخ های خورشیدی یک سال
    private function getTheListOfPersianDatesOfAYear()
    {
        $dates = [];

        $currentDate = explode('-', date('Y-m-d', time()));
        $dateController = new DateController();
        $currentPersianDate = explode('-', $dateController->gregorian_to_jalali($currentDate[0], $currentDate[1], $currentDate[2], '-'));

        $month = $currentPersianDate[1];
        $year = $currentPersianDate[0];

        for ($index = 0; $index < 12; $index++) {

            if ($month == 0) {
                $month = 12;
                $year--;
            }
            $dates[] = $year . '-' . ($month < 10 ? '0' : '') . $month;
            $month--;
        }

        return $dates;
    }

    public function driversContactCall()
    {
        $basedCalls = DriverCallReport::with('fleet')->where('created_at', '>',  date('Y-m-d h:i:s', strtotime('-30 day', time())))
            ->groupBy('persian_date')
            ->select('persian_date', DB::raw('sum(calls) as countOfCalls'))
            ->get();

        $basedFleets = DriverCallReport::where('created_at', '>',  date('Y-m-d h:i:s', strtotime('-30 day', time())))
            ->orderBy('created_at', 'ASC')
            ->get();
        $groupBy = $basedFleets->groupBy('fleet.title');

        return view('admin.reporting.driversContactCall', compact(['basedCalls', 'basedFleets', 'groupBy']));
    }
    public function driversCountCall($basedCalls = [], $showSearchResult = false)
    {
        $fromDate = gregorianDateToPersian(date('Y/m/d', time()), '/');
        $toDate = $fromDate;
        if (!$showSearchResult) {
            $basedCalls = DriverCallCount::with('driver')
                ->groupBy('driver_id')
                ->select('driver_id', 'persian_date', 'created_date', DB::raw('sum(calls) as countOfCalls'))
                ->orderByDesc('countOfCalls')
                ->where('persian_date', $fromDate)
                ->paginate(20);
        }


        return view('admin.reporting.driversCountCall', compact('basedCalls', 'fromDate', 'toDate'));
    }

    public function driversInMonth(Request $request)
    {
        $date = Carbon::today()->subDays(30);

        $driverVersions = Driver::select('version', DB::raw('count(*) as total'))
            ->groupBy('version')
            ->orderBy('version', 'desc')
            ->where('version', '<=', 58)
            ->get();
        if ($request->has('version')) {
            $driversInMonths = DriverCall::with('driver')
                ->groupBy('driver_id')
                ->whereHas('driver', function ($q) use ($request) {
                    $q->where('version', $request->version);
                })
                ->select('driver_id', DB::raw('count(driver_id) as countOfCalls'))
                ->where('created_at', '>=', $date)
                ->get();
        } else {
            $driversInMonths = DriverCall::with('driver')
                ->groupBy('driver_id')
                ->whereHas('driver', function ($q) use ($request) {
                    $q->where('version', 58);
                })
                ->select('driver_id', DB::raw('count(driver_id) as countOfCalls'))
                ->where('created_at', '>=', $date)
                ->get();
        }


        return view('admin.driversInMonth', compact('driversInMonths', 'driverVersions'));
    }

    public function usersByCity()
    {
        $users = Driver::with('cityOwner')->select('city_id', DB::raw('count(`city_id`) as count'))
            ->groupBy('city_id')
            ->having('count', '>', 1)
            ->orderByDesc('count')
            ->paginate(15);
        $provinceCities = ProvinceCity::where('parent_id', '!=', 0)->get();
        return view('admin.reporting.usersByCity', compact('users', 'provinceCities'));
    }
    public function searchUsersByCity(Request $request)
    {
        if ($request->city_id == 0) {
            return redirect()->route('reporting.usersByCity');
        }
        $users = Driver::with('cityOwner')->select('city_id', DB::raw('count(`city_id`) as count'))
            ->groupBy('city_id')
            ->having('count', '>', 1)
            ->orderByDesc('count')
            ->where('city_id', $request->city_id)
            ->paginate(15);
        $provinceCities = ProvinceCity::where('parent_id', '!=', 0)->get();
        if ($users->isEmpty()) {
            return redirect()->route('reporting.usersByCity')->with('danger', 'شهر مورد نظر یافت نشد');
        }
        return view('admin.reporting.usersByCity', compact('users', 'provinceCities'));
    }
    public function usersByCustomCities(ProvinceCity $provinceCity, $drivers = [], $showSearchResult = false)
    {
        if (!$showSearchResult)
            $drivers = Driver::where('city_id', $provinceCity->id)->paginate(10);

        $fleets = Fleet::all();

        return view('admin.driver.city', compact(['drivers', 'provinceCity', 'fleets']));
    }

    public function usersByProvince()
    {
        $users = Driver::with('provinceOwner')->select('province_id', DB::raw('count(`province_id`) as count'))
            ->groupBy('province_id')
            ->having('count', '>', 1)
            ->orderByDesc('count')
            ->paginate(15);
        $provinceCities = ProvinceCity::where('parent_id', '=', 0)->get();
        return view('admin.reporting.usersByProvince', compact('users', 'provinceCities'));
    }

    public function usersByCustomProvinces(ProvinceCity $provinceCity, $drivers = [], $showSearchResult = false)
    {
        if (!$showSearchResult)
            $drivers = Driver::where('province_id', $provinceCity->id)->paginate(10);

        $fleets = Fleet::all();

        return view('admin.driver.province', compact(['drivers', 'provinceCity', 'fleets']));
    }

    public function searchUsersByProvince(Request $request)
    {
        if ($request->province_id == 0)
            return redirect()->route('reporting.usersByProvince');

        $users = Driver::with('provinceOwner')->select('province_id', DB::raw('count(`province_id`) as count'))
            ->groupBy('province_id')
            ->having('count', '>', 1)
            ->orderByDesc('count')
            ->where('province_id', $request->province_id)
            ->paginate(15);
        $provinceCities = ProvinceCity::where('parent_id', '=', 0)->get();

        if ($users->isEmpty())
            return redirect()->route('reporting.usersByProvince')->with('danger', 'شهر مورد نظر یافت نشد');

        return view('admin.reporting.usersByProvince', compact('users', 'provinceCities'));
    }

    public function searchDriversCountCall(Request $request)
    {
        $basedCalls = DriverCallCount::with('driver')
            ->whereHas('driver', function ($q) use ($request) {
                $q->where('mobileNumber', $request->mobileNumber);
            })
            ->groupBy('driver_id')
            ->select('driver_id', 'persian_date', 'created_date', DB::raw('sum(calls) as countOfCalls'))
            ->orderByDesc('countOfCalls')
            ->whereBetween('persian_date', [$request->fromDate, $request->toDate])
            ->paginate(20);
        if (count($basedCalls))
            return $this->driversCountCall($basedCalls, true);
        else
            return back()->with('danger', 'در این تاریخ یافت نشد.');
    }




    // تفکیک ناوگان ثبت نامی از ابتدا
    private function getSeparationOfTheFleetsFromTheFirst()
    {
        $fleets = Driver::join('fleets', 'fleets.id', 'drivers.fleet_id')
            ->select('fleets.id', 'fleets.title', DB::raw('count(*) as total'))
            ->groupBy('fleets.id', 'fleets.title')
            ->orderBy('total', 'asc')
            ->pluck('total', 'fleets.title');


        return $fleets;
    }

    // گزارش فعالیت راننده ها از ماه قبل
    private function getActivityReportOfDriversFromPreviousMonth()
    {

        $driverActivities = [];
        for ($index = 30; $index >= 0; $index--) {
            $day = date('Y-m-d', strtotime('-' . $index . 'day', time()));
            $driverActivities[] = [
                'label' => str_replace('-', '/', convertEnNumberToFa(gregorianDateToPersian($day, '-'))),
                'value' => DriverActivity::where([
                    ['created_at', '>', $day . ' 00:00:00'],
                    ['created_at', '<', $day . ' 23:59:59']
                ])->count()
            ];
        }
        return $driverActivities;
    }

    // فعالیت به تفکیک ناوگان ماهانه
    private function getActivityReportOfDriversFromPreviousMonthByFleets()
    {

        $date = date('Y-m-d', strtotime('-30day', time())) . ' 00:00:00';

        $days = DriverActivity::where('created_at', '>', $date)
            ->select('persianDate')
            ->groupBy('persianDate')
            ->get();


        $data = [];
        foreach ($days as $day) {

            $fleets = Fleet::join('drivers', 'drivers.fleet_id', 'fleets.id')
                ->join('driver_activities', 'driver_activities.driver_id', 'drivers.id')
                ->select('fleets.title', 'fleets.id', DB::raw('count(*) as total'))
                ->where('persianDate', $day->persianDate)
                ->groupBy('fleets.title', 'fleets.id')
                ->orderBy('total', 'asc')
                ->get();
            foreach ($fleets as $fleet) {
                $data[$fleet->id]['title'] = $fleet->title;
                $data[$fleet->id]['data'][] = [
                    'value' => $fleet->total,
                    'label' => $day->persianDate
                ];
            }
        }

        return $data;
    }

    // گزارش نصب رانندگان در 30 روز گذشته
    public function driverInstallationInLast30Days()
    {
        $driverInstallInLast30Days = [];

        for ($index = 30; $index >= 0; $index--) {
            $day = date('Y-m-d', strtotime('-' . $index . 'day', time()));
            $value = Driver::where([
                ['created_at', '>=', $day . ' 00:00:00'],
                ['created_at', '<', $day . ' 23:59:59'],
            ])->count();

            $driverInstallInLast30Days[] = [
                'value' => $value,
                'label' => str_replace('-', '/', convertEnNumberToFa(gregorianDateToPersian($day, '-')))
            ];
        }

        return view('admin.reporting.driverInstallationInLast30DaysReport', compact('driverInstallInLast30Days'));
    }

    /****************************************************************************************************/

    // گزارش فعالیت باربری ها
    public function transportationCompaniesActivityReport()
    {
        $totalTransportationCompanies = Bearing::count();

        // روند افزایش باربری ها از 12 ماه قبل
        $increaseOfTransportationCompaniesSince12MonthsAgo = [];
        $this->persianDateList = $this->getTheListOfPersianDatesOfAYear();
        for ($index = 11; $index >= 0; $index--)
            $increaseOfTransportationCompaniesSince12MonthsAgo[$index] = [
                'label' => str_replace('-', '/', convertEnNumberToFa($this->persianDateList[$index])),
                'value' => $this->getIncreaseOfTransportationCompaniesSince12MonthsAgo($index)
            ];

        //تعداد بار ثبت شده 30 روز قبل شرکت های باربری
        $countOfTransportationCompaniesLoadsInPrevious30Days = $this->getCountOfTransportationCompaniesLoadsInPrevious30Days();

        // ثبت بار به تفکیک ناوگان از 30 روز قبل
        $transportationCompaniesLoadsByFleetInPrevious30Days = $this->getTransportationCompaniesLoadsByFleetInPrevious30Days();

        // هزینه وایزی توسط باربری ها از 60 روز قبل
        $depositDee60DaysInAdvance = $this->getDepositDee60DaysInAdvance(ROLE_TRANSPORTATION_COMPANY);

        return view('admin.reporting.transportationCompaniesActivityReport', compact('totalTransportationCompanies', 'increaseOfTransportationCompaniesSince12MonthsAgo', 'countOfTransportationCompaniesLoadsInPrevious30Days', 'transportationCompaniesLoadsByFleetInPrevious30Days', 'depositDee60DaysInAdvance'));
    }

    // روند افزایش باربری ها از 12 ماه قبل
    private function getIncreaseOfTransportationCompaniesSince12MonthsAgo($index)
    {
        $fromDate = persianDateToGregorian($this->persianDateList[$index] . '-01', '-');
        if ($index > 0) {
            $toDate = persianDateToGregorian($this->persianDateList[$index - 1] . '-01', '-');

            return Bearing::where([
                ['created_at', '>=', $fromDate],
                ['created_at', '<', $toDate]
            ])->count();
        }

        return Bearing::where('created_at', '>=', $fromDate)->count();
    }

    //تعداد بار ثبت شده 30 روز قبل شرکت های باربری
    private function getCountOfTransportationCompaniesLoadsInPrevious30Days()
    {
        $loadsCount = [];

        for ($day = 30; $day >= 0; $day--) {
            $dateFrom = date('Y-m-d', strtotime('-' . $day . 'day', time()));
            $dateTo = date('Y-m-d', strtotime('-' . ($day - 1) . 'day', time()));

            $value = LoadBackup::where([
                ['created_at', '>=', $dateFrom . ' 00:00:00'],
                ['created_at', '<', $dateTo . ' 00:00:00'],
                ['userType', ROLE_TRANSPORTATION_COMPANY],
                ['operator_id', 0],
            ])->count();

            $loadsCount[] = [
                'value' => $value,
                'label' => str_replace('-', '/', convertEnNumberToFa(gregorianDateToPersian($dateFrom, '-')))
            ];
        }
        return $loadsCount;
    }

    // ثبت بار به تفکیک ناوگان
    private function getTransportationCompaniesLoadsByFleetInPrevious30Days()
    {
        $loadsByFleets = FleetLoad::join('fleets', 'fleets.id', 'fleet_loads.fleet_id')
            ->join('load_backups', 'load_backups.id', 'fleet_loads.load_id')
            ->where([
                ['load_backups.userType', ROLE_TRANSPORTATION_COMPANY],
                ['load_backups.operator_id', 0],
                ['load_backups.created_at', '>', date('Y-m-d', strtotime('-30day', time())) . ' 00:00:00']
            ])
            ->select('fleets.id', 'fleets.title', DB::raw('count(*) as total'))
            ->groupBy('fleets.id', 'fleets.title')
            ->orderBy('total', 'asc')
            ->pluck('total', 'fleets.title');

        return $loadsByFleets;
    }

    /****************************************************************************************************/

    // گزارش فعالیت صاحب بارها
    public function cargoOwnersActivityReport()
    {
        $totalCargoOwners = Customer::count();

        // روند افزایش باربری ها از 12 ماه قبل
        $increaseOfCargoOwnersSince12MonthsAgo = [];
        $this->persianDateList = $this->getTheListOfPersianDatesOfAYear();
        for ($index = 11; $index >= 0; $index--)
            $increaseOfCargoOwnersSince12MonthsAgo[$index] = [
                'label' => str_replace('-', '/', convertEnNumberToFa($this->persianDateList[$index])),
                'value' => $this->getIncreaseOfCargoOwnersSince12MonthsAgo($index)
            ];

        //تعداد بار ثبت شده 30 روز قبل شرکت های باربری
        $countOfCargoOwnersLoadsInPrevious30Days = $this->getCountOfCargoOwnersLoadsInPrevious30Days();

        // ثبت بار به تفکیک ناوگان از 30 روز قبل
        $cargoOwnersLoadsByFleetInPrevious30Days = $this->getCargoOwnersLoadsByFleetInPrevious30Days();

        // هزینه وایزی توسط باربری ها از 60 روز قبل
        $depositDee60DaysInAdvance = $this->getDepositDee60DaysInAdvance(ROLE_CARGo_OWNER);

        return view('admin.reporting.cargoOwnersActivityReport', compact('totalCargoOwners', 'increaseOfCargoOwnersSince12MonthsAgo', 'countOfCargoOwnersLoadsInPrevious30Days', 'cargoOwnersLoadsByFleetInPrevious30Days', 'depositDee60DaysInAdvance'));
    }

    // روند افزایش صاحب بار ها از 12 ماه قبل
    private function getIncreaseOfCargoOwnersSince12MonthsAgo($index)
    {
        $fromDate = persianDateToGregorian($this->persianDateList[$index] . '-01', '-');
        if ($index > 0) {
            $toDate = persianDateToGregorian($this->persianDateList[$index - 1] . '-01', '-');

            return Customer::where([
                ['created_at', '>=', $fromDate],
                ['created_at', '<', $toDate]
            ])->count();
        }

        return Customer::where('created_at', '>=', $fromDate)->count();
    }

    //تعداد بار ثبت شده 30 روز قبل صاحب بار
    private function getCountOfCargoOwnersLoadsInPrevious30Days()
    {
        $loadsCount = [];

        for ($day = 30; $day >= 0; $day--) {
            $dateFrom = date('Y-m-d', strtotime('-' . $day . 'day', time()));
            $dateTo = date('Y-m-d', strtotime('-' . ($day - 1) . 'day', time()));

            $value = LoadBackup::where([
                ['created_at', '>=', $dateFrom . ' 00:00:00'],
                ['created_at', '<', $dateTo . ' 00:00:00'],
                ['userType', ROLE_CUSTOMER],
                ['operator_id', 0],
            ])->count();

            $loadsCount[] = [
                'value' => $value,
                'label' => str_replace('-', '/', convertEnNumberToFa(gregorianDateToPersian($dateFrom, '-')))
            ];
        }
        return $loadsCount;
    }

    // ثبت بار به تفکیک ناوگان
    private function getCargoOwnersLoadsByFleetInPrevious30Days()
    {
        $loadsByFleets = FleetLoad::join('fleets', 'fleets.id', 'fleet_loads.fleet_id')
            ->where('userType', ROLE_CARGo_OWNER)
            ->select('fleets.id', 'fleets.title', DB::raw('count(*) as total'))
            ->groupBy('fleets.id', 'fleets.title')
            ->orderBy('total', 'asc')
            ->pluck('total', 'fleets.title');

        return $loadsByFleets;
    }

    /****************************************************************************************************/

    //    public function daysActivityNissan()
    //    {
    //        return DriverActivity::all();
    //    }

    // گزارش فعالیت اپرتورها
    public function operatorsActivityReport()
    {

        //تعداد بار ثبت شده 30 روز قبل
        $countOfOperatorsLoadsInPrevious30Days = []; // $this->getCountOfOperatorsLoadsInPrevious30Days();

        //تعداد بار ثبت شده 30 روز قبل به تفکیک اپراتور
        $countOfOperatorsLoadsInPrevious30DaysByOperator = $this->getCountOfOperatorsLoadsInPrevious30DaysByOperator();

        // گزارش فعالیت اپراتور ها به صورت هفته به هفته
        $operatorActivityReportOnAWeeklyBasis = []; // $this->getOperatorActivityReportOnAWeeklyBasis();

        // ثبت بار به تفکیک اپراتور
        $loadRegistrationByOperator = []; //$this->getLoadRegistrationByOperator();

        // ثبت بار به تفکیک اپراتور در هفته گذشته
        $getLoadRegistrationByOperatorInPastWeek = []; // $this->getLoadRegistrationByOperatorInPastWeek();

        // ثبت بار به تفکیک ناوگان
        $operatorsLoadsByFleet = []; // $this->getOperatorsLoadsByFleet();


        return view('admin.reporting.operatorsActivityReport', compact('countOfOperatorsLoadsInPrevious30Days', 'loadRegistrationByOperator', 'operatorsLoadsByFleet', 'countOfOperatorsLoadsInPrevious30DaysByOperator', 'operatorActivityReportOnAWeeklyBasis', 'getLoadRegistrationByOperatorInPastWeek'));
    }

    //تعداد بار ثبت شده 30 روز قبل اپرتور
    private function getCountOfOperatorsLoadsInPrevious30Days()
    {
        $loadsCount = [];

        for ($day = 30; $day >= 0; $day--) {
            $dateFrom = date('Y-m-d', strtotime('-' . $day . 'day', time()));
            $dateTo = date('Y-m-d', strtotime('-' . ($day - 1) . 'day', time()));

            $value = LoadBackup::where([
                ['created_at', '>=', $dateFrom . ' 00:00:00'],
                ['created_at', '<', $dateTo . ' 00:00:00'],
                ['operator_id', '>', 0],
            ])->count();

            $loadsCount[] = [
                'value' => $value,
                'label' => str_replace('-', '/', convertEnNumberToFa(gregorianDateToPersian($dateFrom, '-')))
            ];
        }
        return $loadsCount;
    }

    //تعداد بار ثبت شده 30 روز قبل به تفکیک اپرتور
    private function getCountOfOperatorsLoadsInPrevious30DaysByOperator()
    {
        $loadsCount = [];
        $operators = LoadBackup::join('users', 'users.id', 'load_backups.operator_id')
            ->where('users.role', 'operator')
            ->select('users.id', 'users.name', 'users.lastName')
            ->distinct('users.id')
            ->get();


        for ($day = 10; $day >= 0; $day--) {
            $dateFrom = date('Y-m-d', strtotime('-' . $day . 'day', time()));
            $dateTo = date('Y-m-d', strtotime('-' . ($day - 1) . 'day', time()));
            foreach ($operators as $operator) {
                $value = LoadBackup::where([
                    ['created_at', '>=', $dateFrom . ' 00:00:00'],
                    ['created_at', '<', $dateTo . ' 00:00:00'],
                    ['operator_id', $operator->id],
                ])->count();

                $loadsCount[$operator->id]['name'] = $operator->name . ' ' . $operator->lastName;
                $loadsCount[$operator->id]['data'][] = [
                    'value' => $value,
                    'label' => str_replace('-', '/', convertEnNumberToFa(gregorianDateToPersian($dateFrom, '-')))
                ];
            }
        }
        return $loadsCount;
    }

    //تعداد بار ثبت شده 30 روز قبل به تفکیک اپرتور
    private function getOperatorActivityReportOnAWeeklyBasis()
    {
        $loadsCount = [];
        $operators = LoadBackup::join('users', 'users.id', 'load_backups.operator_id')
            ->where('users.role', 'operator')
            ->select('users.id', 'users.name', 'users.lastName')
            ->distinct('users.id')
            ->get();

        $friday = strtotime('friday last week');

        for ($day = 70; $day >= 0; $day -= 7) {
            $dateFrom = date('Y-m-d', strtotime('-' . $day . 'day', $friday));
            $dateTo = date('Y-m-d', strtotime('-' . ($day - 7) . 'day', $friday));

            foreach ($operators as $operator) {
                $value = LoadBackup::where([
                    ['created_at', '>=', $dateFrom . ' 00:00:00'],
                    ['created_at', '<', $dateTo . ' 00:00:00'],
                    ['operator_id', $operator->id],
                ])->count();

                $loadsCount[$operator->id]['name'] = $operator->name . ' ' . $operator->lastName;
                $loadsCount[$operator->id]['data'][] = [
                    'value' => $value,
                    'label' => str_replace('-', '/', convertEnNumberToFa(gregorianDateToPersian($dateFrom, '-')))
                ];
            }
        }
        return $loadsCount;
    }

    // ثبت بار به تفکیک اپراتور
    private function getLoadRegistrationByOperator()
    {
        $loadsByOperators = User::join('load_backups', 'load_backups.operator_id', 'users.id')
            ->where('users.role', 'operator')
            ->select('users.name', 'users.lastName', 'users.id', DB::raw('count(*) as total'))
            ->groupBy('users.id', 'users.name', 'users.lastName')
            ->orderBy('total', 'asc')
            ->get();


        return $loadsByOperators;
    }


    // ثبت بار به تفکیک اپراتور در هفته گذشته
    private function getLoadRegistrationByOperatorInPastWeek()
    {

        try {

            $friday = strtotime('last friday');
            $dateFrom = date('Y-m-d', strtotime('-7day', $friday));
            $dateTo = date('Y-m-d', $friday);

            $loadsByOperators = User::join('load_backups', 'load_backups.operator_id', 'users.id')
                ->where([
                    ['users.role', 'operator'],
                    ['load_backups.created_at', '>=', $dateFrom . ' 00:00:00'],
                    ['load_backups.created_at', '<', $dateTo . ' 00:00:00']
                ])
                ->select('users.name', 'users.lastName', 'users.id', DB::raw('count(*) as total'))
                ->groupBy('users.id', 'users.name', 'users.lastName')
                ->orderBy('total', 'asc')
                ->get();

            return $loadsByOperators;
        } catch (\Exception $exception) {
            Log::emergency("---------------------------------- getLoadRegistrationByOperatorInPastWeek ---------------------------------");
            Log::emergency($exception->getMessage());
            Log::emergency("------------------------------------------------------------------------------------");
        }

        return [];
    }

    // ثبت بار به تفکیک ناوگان توسط اپراتور ها
    private function getOperatorsLoadsByFleet()
    {
        $loadsByFleets = FleetLoad::join('fleets', 'fleets.id', 'fleet_loads.fleet_id')
            ->join('load_backups', 'load_backups.id', 'fleet_loads.load_id')
            ->where('load_backups.operator_id', '>', 0)
            ->select('fleets.id', 'fleets.title', DB::raw('count(*) as total'))
            ->groupBy('fleets.id', 'fleets.title')
            ->orderBy('total', 'asc')
            ->pluck('total', 'fleets.title');

        return $loadsByFleets;
    }

    /*******************************************************************************************************/

    // گزارش های ترکیبی
    public function combinedReports()
    {
        $totalLoads = LoadBackup::count();

        // بار ثبت شده به تفکیک فعالین
        $countOfLoadByOperators = $this->getCountOfLoadByOperators();

        // بار ثبت شده و تعداد کل رانندگان

        return view('admin.reporting.combinedReports', compact('totalLoads', 'countOfLoadByOperators'));
    }

    // بار ثبت شده به تفکیک فعالین
    private function getCountOfLoadByOperators()
    {
        $loadsByFleets = [];

        $parentFleets = Fleet::where('parent_id', 0)->get();

        foreach ($parentFleets as $parentFleet) {

            $fleets = Fleet::where('parent_id', $parentFleet->id)->get();

            foreach ($fleets as $fleet) {
                //                $loadsByFleets[$parentFleet->title]['total'][$fleet->title] = FleetLoad::join('load_backups', 'load_backups.id', 'fleet_loads.load_id')
                //                    ->where('fleet_loads.fleet_id', $fleet->id)->count();

                $loadsByFleets[$parentFleet->title][ROLE_CARGo_OWNER][$fleet->title] = FleetLoad::join('load_backups', 'load_backups.id', 'fleet_loads.load_id')
                    ->where([
                        ['fleet_loads.fleet_id', $fleet->id],
                        ['load_backups.userType', ROLE_CARGo_OWNER],
                        ['operator_id', 0]
                    ])->count();
                $loadsByFleets[$parentFleet->title][ROLE_TRANSPORTATION_COMPANY][$fleet->title] = FleetLoad::join('load_backups', 'load_backups.id', 'fleet_loads.load_id')
                    ->where([
                        ['fleet_loads.fleet_id', $fleet->id],
                        ['load_backups.userType', ROLE_TRANSPORTATION_COMPANY],
                        ['operator_id', 0]
                    ])->count();
                $loadsByFleets[$parentFleet->title][ROLE_OPERATOR][$fleet->title] = FleetLoad::join('load_backups', 'load_backups.id', 'fleet_loads.load_id')
                    ->where([
                        ['fleet_loads.fleet_id', $fleet->id],
                        ['operator_id', '>', 0]
                    ])->count();

                $loadsByFleets[$parentFleet->title]['total'][$fleet->title] = $loadsByFleets[$parentFleet->title][ROLE_CARGo_OWNER][$fleet->title] + $loadsByFleets[$parentFleet->title][ROLE_TRANSPORTATION_COMPANY][$fleet->title] + $loadsByFleets[$parentFleet->title][ROLE_OPERATOR][$fleet->title];
            }
        }

        return $loadsByFleets;
    }

    // بار ثبت شده و تعداد کل رانندگان
    private function getCountOfLoadCompareWithFleets()
    {
        $loadsByFleets = [];
        $loadsByFleets['total'] = FleetLoad::join('fleets', 'fleets.id', 'fleet_loads.fleet_id')
            ->join('load_backups', 'load_backups.id', 'fleet_loads.load_id')
            ->select('fleet_loads.fleet_id', 'fleets.id', 'fleets.title', DB::raw('count(*) as total'))
            ->groupBy('fleet_loads.fleet_id')
            ->orderBy('fleet_id', 'asc')
            ->pluck('total', 'fleets.title');

        $loadsByFleets['customer'] = FleetLoad::join('fleets', 'fleets.id', 'fleet_loads.fleet_id')
            ->join('load_backups', 'load_backups.id', 'fleet_loads.load_id')
            ->where([
                ['load_backups.userType', ROLE_CUSTOMER],
                ['load_backups.operator_id', 0]
            ])
            ->select('fleet_loads.fleet_id', 'fleets.id', 'fleets.title', DB::raw('count(*) as total'))
            ->groupBy('fleet_loads.fleet_id')
            ->orderBy('fleet_id', 'asc')
            ->pluck('total', 'fleets.title');

        $loadsByFleets['transportation_company'] = FleetLoad::join('fleets', 'fleets.id', 'fleet_loads.fleet_id')
            ->join('load_backups', 'load_backups.id', 'fleet_loads.load_id')
            ->where([
                ['load_backups.userType', ROLE_TRANSPORTATION_COMPANY],
                ['load_backups.operator_id', 0]
            ])
            ->select('fleet_loads.fleet_id', 'fleets.id', 'fleets.title', DB::raw('count(*) as total'))
            ->groupBy('fleet_loads.fleet_id')
            ->orderBy('fleet_id', 'asc')
            ->pluck('total', 'fleets.title');

        $loadsByFleets['operators'] = FleetLoad::join('fleets', 'fleets.id', 'fleet_loads.fleet_id')
            ->join('load_backups', 'load_backups.id', 'fleet_loads.load_id')
            ->where('load_backups.operator_id', '>', 0)
            ->select('fleet_loads.fleet_id', 'fleets.id', 'fleets.title', DB::raw('count(*) as total'))
            ->groupBy('fleet_loads.fleet_id')
            ->orderBy('fleet_id', 'asc')
            ->pluck('total', 'fleets.title');

        return $loadsByFleets;
    }

    /*******************************************************************************************************/
    // گزارش جدول نسبت ناوگان (کل و فعال) به بار
    public function fleetRatioToDriverActivityReport(Request $request)
    {

        $fromDate = gregorianDateToPersian(date('Y/m/d', strtotime('-10 day', time())), '/');
        $toDate = gregorianDateToPersian(date('Y/m/d', time()), '/');
        if (isset($request->fromDate)) {
            $fromDate = $request->fromDate;
            $toDate = $request->toDate;
        }

        $fleetRatioToDriverActivityReport = FleetRatioToDriverActivityReport::where([
            ['persianDate', '>=', $fromDate],
            ['persianDate', '<=', $toDate]
        ])
            ->select('fleet_id')
            ->selectRaw('sum(countOfDrivers) as countOfDrivers')
            ->selectRaw('sum(countOfActiveDrivers) as countOfActiveDrivers')
            ->selectRaw('avg(countOfActiveDriverAccounts) as countOfActiveDriverAccounts')
            ->selectRaw('sum(countOfOperatorsLoads) as countOfOperatorsLoads')
            ->selectRaw('sum(countOfCargoOwnersLoads) as countOfCargoOwnersLoads')
            ->selectRaw('sum(countOfTransportationsLoads) as countOfTransportationsLoads')
            ->selectRaw('sum(countOfOperatorsLoads) + sum(countOfCargoOwnersLoads) + sum(countOfTransportationsLoads) as countOfAllLoads')
            ->groupBy('fleet_id')
            ->get();

        $fleetRatioToDriverActivityDiagram = FleetRatioToDriverActivityReport::where([
            ['persianDate', '>=', $fromDate],
            ['persianDate', '<=', $toDate]
        ])
            ->select('persianDate as date')
            ->selectRaw('sum(countOfActiveDrivers) as countOfActiveDrivers')
            ->selectRaw('sum(countOfDrivers) as countOfDrivers')
            ->selectRaw('sum(countOfOperatorsLoads) + sum(countOfCargoOwnersLoads) + sum(countOfTransportationsLoads) as countOfAllLoads')
            ->selectRaw('(sum(countOfOperatorsLoads) + sum(countOfCargoOwnersLoads) + sum(countOfTransportationsLoads)) / sum(countOfActiveDrivers) as value')
            ->groupBy('persianDate')
            ->get();

        $allDrivers = Driver::where('created_at', '<', persianDateToGregorian(str_replace('/', '-', $fromDate), '-') . ' 00:00:00')->count();


        return view('admin.reporting.fleetRatioToDriverActivityReport', compact('fleetRatioToDriverActivityReport', 'fleetRatioToDriverActivityDiagram', 'allDrivers', 'fromDate', 'toDate'));
    }

    // ذخیره اطلاعات گزارش نسبت ناوگان به بار
    public function storeFleetRatioToDriverActivityReportData()
    {
        $fleets = Fleet::where('fleets.parent_id', '>', 0)->get();

        $date = date('Y-m-d', strtotime('-1 day', time()));
        foreach ($fleets as $fleet) {

            $countOfActiveDrivers = Driver::join('driver_activities', 'driver_activities.driver_id', 'drivers.id')
                ->where([
                    ['drivers.fleet_id', $fleet->id],
                    ['driver_activities.created_at', '>=', $date . ' 00:00:00'],
                    ['driver_activities.created_at', '<=', $date . ' 23:59:59'],
                ])
                ->distinct('driver_activities.driver_id')
                ->count('driver_activities.driver_id');

            $countOfDrivers = Driver::where([
                ['fleet_id', $fleet->id],
                ['created_at', '>=', $date . ' 00:00:00'],
                ['created_at', '<=', $date . ' 23:59:59']
            ])->count();

            $countOfActiveDriverAccounts = Driver::where([
                ['fleet_id', $fleet->id],
                ['activeDate', '>=', $date]
            ])->count();

            $countOfOperatorsLoads = FleetLoad::join('load_backups', 'load_backups.id', 'fleet_loads.load_id')
                ->where([
                    ['fleet_loads.fleet_id', $fleet->id],
                    ['fleet_loads.created_at', '>=', $date . ' 00:00:00'],
                    ['fleet_loads.created_at', '<=', $date . ' 23:59:59'],
                    ['load_backups.operator_id', '>', 0]
                ])->count();

            $countOfCargoOwnersLoads = FleetLoad::join('load_backups', 'load_backups.id', 'fleet_loads.load_id')
                ->where([
                    ['fleet_loads.fleet_id', $fleet->id],
                    ['fleet_loads.created_at', '>=', $date . ' 00:00:00'],
                    ['fleet_loads.created_at', '<=', $date . ' 23:59:59'],
                    ['load_backups.operator_id', 0],
                    ['load_backups.userType', ROLE_CUSTOMER],
                ])->count();

            $countOfTransportationsLoads = FleetLoad::join('load_backups', 'load_backups.id', 'fleet_loads.load_id')
                ->where([
                    ['fleet_loads.fleet_id', $fleet->id],
                    ['fleet_loads.created_at', '>=', $date . ' 00:00:00'],
                    ['fleet_loads.created_at', '<=', $date . ' 23:59:59'],
                    ['load_backups.operator_id', 0],
                    ['load_backups.userType', ROLE_TRANSPORTATION_COMPANY],
                ])->count();

            $fleetRatioToDriverActivityReport = new FleetRatioToDriverActivityReport();
            $fleetRatioToDriverActivityReport->fleet_id = $fleet->id;
            $fleetRatioToDriverActivityReport->countOfDrivers = $countOfDrivers;
            $fleetRatioToDriverActivityReport->countOfActiveDrivers = $countOfActiveDrivers;
            $fleetRatioToDriverActivityReport->countOfActiveDriverAccounts = $countOfActiveDriverAccounts;
            $fleetRatioToDriverActivityReport->countOfOperatorsLoads = $countOfOperatorsLoads;
            $fleetRatioToDriverActivityReport->countOfCargoOwnersLoads = $countOfCargoOwnersLoads;
            $fleetRatioToDriverActivityReport->countOfTransportationsLoads = $countOfTransportationsLoads;
            $fleetRatioToDriverActivityReport->persianDate = str_replace('-', '/', gregorianDateToPersian($date, '-'));

            $fleetRatioToDriverActivityReport->save();
        }
    }
    /*******************************************************************************************************/
    // لیست صاحب بارها به ترتیب بیشترین بار
    public function getCargoOwnersListSortedByMostLoad()
    {
        $cargoOwners = LoadBackup::select('mobileNumberForCoordination', DB::raw('count(*) as total'))
            ->groupBy('mobileNumberForCoordination')
            ->orderBy('total', 'desc')
            ->paginate(30);
    }

    /*******************************************************************************************************/

    /**
     * @return string
     */
    private function getMonthDate(): string
    {
        $monthDate = explode('-', gregorianDateToPersian(date('Y-m-d', time()), '-'));
        if (isset($monthDate[0]) && isset($monthDate[1]))
            $monthDate = persianDateToGregorian($monthDate[0] . '-' . $monthDate[1] . '-01', '-') . ' 00:00:00';
        else
            $monthDate = date('Y-m-d', strtotime('-30 day', time())) . ' 00:00:00';
        return $monthDate;
    }

    /****************************************************************************************************/

    // گزارش پرداخت ها
    public function paymentReport($userType, $status)
    {

        if ($status == 0) {
            $today = date('Y-m-d', time()) . ' 00:00:00';
            $successTransactions = Transaction::where([
                ['status', '>', 0],
                ['userType', $userType],
                ['created_at', '>=', $today]
            ])->pluck('user_id');
            $transactions = Transaction::where([
                ['status', 0],
                ['userType', $userType],
                ['created_at', '>=', $today]
            ])
                ->whereNotIn('user_id', $successTransactions)
                ->select('*', DB::raw('count(*) as total'))
                ->orderBy('id', 'desc')
                ->groupby('user_id')
                ->paginate(100);
        } else if ($status > 0)
            $transactions = Transaction::where([
                ['status', '>', 0],
                ['userType', $userType]
            ])->orderBy('id', 'desc')->paginate(100);

        $counter = [
            'unsuccess' => Transaction::where([['status', 0], ['userType', $userType]])->count(),
            'success' => Transaction::where([['status', '>', 0], ['userType', $userType]])->count()
        ];


        return view('admin.reporting.paymentReport', compact('transactions', 'counter'));
    }

    public function viewPDF(Request $request)
    {
        // $today = date('Y-m-d', time()) . ' 00:00:00';
        $fromDate = persianDateToGregorian(str_replace('/', '-', $request->from), '-') . ' 00:00:00';
        $toDate = persianDateToGregorian(str_replace('/', '-', $request->to), '-') . ' 00:00:00';

        $transactions = Transaction::where('status', '>', 0)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->orderBy('created_at', 'asc')
            ->select('id', 'created_at', 'amount')
            ->get();

        $pdf = Pdf::loadView('admin.reportToPdf.payments', array('transactions' =>  $transactions))->setPaper('a4', 'portrait');

        $rnd = rand(10, 10000);

        return $pdf->download('transactions' . $rnd . '.pdf');
    }

    public function unSuccessPeyment()
    {
        $today = date('Y-m-d', time()) . ' 00:00:00';
        $successTransactions = Transaction::where([
            ['status', '>', 0],
            ['userType', 'driver'],
            ['created_at', '>=', $today]
        ])->pluck('user_id');
        $transactions = Transaction::where([
            ['status', 0],
            ['userType', 'driver'],
            ['created_at', '>=', $today]
        ])
            ->whereNotIn('user_id', $successTransactions)
            ->select('*', DB::raw('count(*) as total'))
            ->orderBy('id', 'desc')
            ->groupby('user_id')
            ->paginate(100);
        return view('admin.reporting.unSuccessPeyment', compact('transactions'));
    }

    // گزارش بیشترین پرداخت رانندگان
    public function mostPaidDriversReport()
    {
        $transactions = Transaction::where([
            ['status', '>', 0],
            ['userType', ROLE_DRIVER]
        ])
            ->select('user_id', 'userType', DB::raw('count(*) as total'), DB::raw('sum(amount) as totalAmount'))
            ->groupBy('user_id', 'userType')
            ->orderBy('total', 'desc')
            ->paginate(20);

        return view('admin.reporting.mostPaidDriversReport', compact('transactions'));
    }

    // گزارش پرداخت براساس ناوگان
    public function paymentByFleetReport()
    {

        $paymentByFleetReport = Fleet::join('drivers', 'drivers.fleet_id', 'fleets.id')
            ->join('transactions', 'transactions.user_id', 'drivers.id')
            ->where([
                ['transactions.status', '>', 0],
                ['userType', ROLE_DRIVER]
            ])
            ->select('fleets.id', 'fleets.title', DB::raw('count(*) as total'), DB::raw('sum(amount) as totalAmount'))
            ->groupBy('fleets.id', 'fleets.title')
            ->orderBy('totalAmount', 'desc')
            ->get();

        return view('admin.reporting.paymentByFleetReport', compact('paymentByFleetReport'));
    }

    // میزان ساعت فعالیت اپراتور ها
    public function operatorsWorkingHoursActivityReport(Request $request)
    {
        $fromDate = gregorianDateToPersian(date('Y/m/d', time()), '/');
        $toDate = $fromDate;

        if (isset($request->fromDate) && isset($request->toDate)) {
            $fromDate = $request->fromDate;
            $toDate = $request->toDate;
        }

        $users = UserActivityReport::join('users', 'users.id', 'user_activity_reports.user_id')
            ->where([
                ['users.status', 1],
                ['user_activity_reports.created_at', '>', persianDateToGregorian($fromDate, '/') . ' 00:00:00'],
                ['user_activity_reports.created_at', '<', persianDateToGregorian($toDate, '/') . ' 23:59:59']
            ])
            ->select('users.name', 'users.lastName', 'user_activity_reports.user_id', DB::raw('count(*) as userActivityReport'))
            ->groupBy('user_activity_reports.user_id')
            ->get();


        return view('admin.reporting.operatorsWorkingHoursActivityReport', compact('users', 'fromDate', 'toDate'));
    }

    // گزارش بار ها به تفکیک ناوگان
    public function cargoFleetsReport()
    {
        try {
            $persian_date = gregorianDateToPersian(date('Y/m/d', time()), '/');
            $cargoReports = CargoReportByFleet::with('fleet')
                // ->where('date', $persian_date)
                ->orderByDesc('date')
                ->paginate(25);
            $fleets = Fleet::where('parent_id', '>', 0)->orderBy('parent_id', 'asc')->get();
            return view('admin.reporting.cargoFleetsReport', compact('cargoReports', 'fleets'));
        } catch (\Exception $exception) {
            Log::emergency("---------------------------------- cargoFleetsReport ---------------------------------");
            Log::emergency($exception->getMessage());
            Log::emergency("------------------------------------------------------------------------------------");
        }
    }

    // جستجو گزارش بار ها به تفکیک ناوگان
    public function searchCargoFleets(Request $request)
    {
        try {
            // $persian_date = gregorianDateToPersian(date('Y/m/d', time()), '/');
            // $fleet_id = $request->fleet_id;
            // $cargoReports = CargoReportByFleet::with('fleet')
            //     ->where('fleet_id', $request->fleet_id)
            //     ->orderByDesc('date')
            //     ->take(50)
            //     ->get();

            $fromDate = persianDateToGregorian(str_replace('/', '-', $request->fromDate), '-') . ' 00:00:00';
            $toDate = persianDateToGregorian(str_replace('/', '-', $request->toDate), '-') . ' 23:59:00';
            $cargoReports = CargoReportByFleet::with('fleet')
                ->groupBy('fleet_id')
                ->select('fleet_id', 'date', 'count_owner', DB::raw('sum(count) as count'))
                ->orderByDesc('count')
                ->whereBetween('created_at', [$fromDate, $toDate])
                ->when($request->fleet_id !== null, function ($query) use ($request) {
                    return $query->where('fleet_id', $request->fleet_id);
                })
                ->get();
            // $fleets = Fleet::where('parent_id', '>', 0)->orderBy('parent_id', 'asc')->get();
            return view('admin.reporting.searchCargoFleetsReport', compact('cargoReports'));
        } catch (\Exception $exception) {
            Log::emergency("---------------------------------- cargoFleetsReport ---------------------------------");
            Log::emergency($exception->getMessage());
            Log::emergency("------------------------------------------------------------------------------------");
        }
    }

    /*****************************************************************************************************/
    // گزارش پرداخت رانندگان
    public function driversPaymentReport(Request $request)
    {

        $driver = null;
        $transactions = null;

        if (isset($request->mobileNumber)) {
            $driver = Driver::where('mobileNumber', $request->mobileNumber)->first();
            if (isset($driver->id))
                $transactions = Transaction::where('user_id', $driver->id)
                    ->where('userType', ROLE_DRIVER)
                    ->orderBy('id', 'desc')
                    ->paginate(50);
            else
                return back()->with('danger', 'تراکنشی برای راننده مورد نظر پیدا نشد.');
        }

        return view('admin.reporting.driversPaymentReport', compact('driver', 'transactions'));
    }
}
