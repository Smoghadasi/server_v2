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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ReportingController extends Controller
{

    private $persianDateList;


    public function getDriverActivityData()
    {
        $driverActivities = Cache::remember('driver_activity_report', now()->addHours(1), function () {
            return DriverActivity::selectRaw("DATE(created_at) as date, COUNT(DISTINCT driver_id) as count")
                ->whereBetween('created_at', [now()->subDays(30)->startOfDay(), now()->endOfDay()])
                ->groupBy('date')
                ->orderBy('date')
                ->get();
        });

        return response()->json([
            'labels' => $driverActivities->pluck('date')->map(fn($date) => str_replace('-', '/', convertEnNumberToFa(gregorianDateToPersian($date, '-')))),
            'values' => $driverActivities->pluck('count')
        ]);
    }

    public function fleetReportSummary()
    {
        $fleets = Cache::remember('fleet_report_summary', now()->addHour(), function () {

            $date = now()->subDays(30)->startOfDay();
            $now = now();
            $today = now()->toDateString();
            $yesterday = now()->subDay()->toDateString();



            // -----------------------------
            // 1. نسبت فعالیت رانندگان نسبت به روز گذشته
            // -----------------------------

            $driverIds = Transaction::where('status', '>', 0)
                ->where('created_at', '>', $date)
                ->pluck('user_id');

            $activityStatsAll = DB::table('fleets')
                ->join('drivers', 'drivers.fleet_id', '=', 'fleets.id')
                ->join('driver_activities', 'driver_activities.driver_id', '=', 'drivers.id')
                ->where('driver_activities.created_at', '>', $date)
                ->groupBy('fleets.id', 'fleets.title')
                ->select(
                    'fleets.id as fleet_id',
                    // تعداد راننده‌های منحصربه‌فرد در 30 روز
                    DB::raw('COUNT(DISTINCT driver_activities.driver_id) as total'),
                )
                ->get()
                ->keyBy('fleet_id');


            $activityStats = DB::table('fleets')
                ->join('drivers', 'drivers.fleet_id', '=', 'fleets.id')
                ->join('driver_activities', 'driver_activities.driver_id', '=', 'drivers.id')
                ->where('driver_activities.created_at', '>', $date)
                ->whereIn('driver_activities.driver_id', $driverIds) // 🔹 فقط رانندگانی که تراکنش دارند یا در لیست خاص هستند
                ->groupBy('fleets.id', 'fleets.title')
                ->select(
                    'fleets.id as fleet_id',

                    // تعداد راننده‌های منحصربه‌فرد در 30 روز
                    DB::raw('COUNT(DISTINCT driver_activities.driver_id) as total'),

                    // تعداد راننده‌های دارای اشتراک فعال (activeDate >= now)
                    DB::raw("COUNT(DISTINCT CASE WHEN drivers.activeDate >= '{$now}' THEN driver_activities.driver_id END) as active"),

                    // ✅ رانندگانی که در 30 روز گذشته فعالیت داشته‌اند ولی هیچ خریدی نداشته‌اند
                    DB::raw("COUNT(DISTINCT CASE
                        WHEN drivers.id NOT IN (
                            SELECT DISTINCT transactions.user_id
                            FROM transactions
                            WHERE transactions.created_at > DATE_SUB('{$now}', INTERVAL 30 DAY)
                        )
                        THEN driver_activities.driver_id
                    END) as notActive"),

                    // تعداد راننده‌هایی که دیروز فعالیت داشتند
                    DB::raw("COUNT(DISTINCT CASE WHEN DATE(driver_activities.created_at) = '{$yesterday}' THEN driver_activities.driver_id END) as yesterday_active")
                )
                ->get()
                ->keyBy('fleet_id');



            // -----------------------------
            // 2. آمار تماس‌های روز گذشته
            // -----------------------------
            $callStats = DB::table('fleets')
                ->join('drivers', 'drivers.fleet_id', '=', 'fleets.id')
                ->join('driver_calls', 'driver_calls.driver_id', '=', 'drivers.id')
                ->whereDate('driver_calls.callingDate', '=', $yesterday)
                ->whereIn('driver_calls.driver_id', $driverIds) // 🔹 فقط رانندگانی که تراکنش داشتن
                ->groupBy('fleets.id', 'fleets.title')
                ->select(
                    'fleets.id as fleet_id',
                    DB::raw('COUNT(*) as total_calls'),
                    DB::raw("SUM(CASE WHEN drivers.activeDate IS NULL OR drivers.activeDate < '{$now}' THEN 1 ELSE 0 END) as notActive_calls"),
                    DB::raw("SUM(CASE WHEN drivers.activeDate >= '{$now}' THEN 1 ELSE 0 END) as active_calls")
                )
                ->get()
                ->keyBy('fleet_id');


            // -----------------------------
            // 3. رانندگان جدید دیروز
            // -----------------------------
            $yesterdayNewDrivers  = DB::table('fleets')
                ->join('drivers', 'drivers.fleet_id', '=', 'fleets.id')
                ->whereDate('drivers.created_at', '=', $yesterday)
                ->groupBy('fleets.id', 'fleets.title')
                ->select(
                    'fleets.id as fleet_id',
                    DB::raw('COUNT(*) as new_drivers_yesterday')
                )
                ->get()
                ->keyBy('fleet_id');

            // -----------------------------
            // 4. نسبت رشد فعالیت امروز/دیروز
            // -----------------------------
            $growthStats = DB::table('fleets')
                ->join('drivers', 'drivers.fleet_id', '=', 'fleets.id')
                ->join('driver_activities', 'driver_activities.driver_id', '=', 'drivers.id')
                ->whereIn(DB::raw('DATE(driver_activities.created_at)'), [$yesterday, $today])
                ->groupBy('fleets.id', 'fleets.title')
                ->select(
                    'fleets.id as fleet_id',
                    DB::raw("SUM(CASE WHEN DATE(driver_activities.created_at) = '{$yesterday}' THEN 1 ELSE 0 END) as count_yesterday"),
                    DB::raw("SUM(CASE WHEN DATE(driver_activities.created_at) = '{$today}' THEN 1 ELSE 0 END) as count_today")
                )
                ->get()
                ->keyBy('fleet_id');

            // -----------------------------
            // 5. ترکیب با ناوگان‌ها
            // -----------------------------
            return Fleet::where('parent_id', '>', 0)
                ->withCount('drivers')
                ->get()
                ->makeHidden(['numOfDrivers'])
                ->map(function ($fleet) use (
                    $activityStatsAll,
                    $activityStats,
                    $callStats,
                    $yesterdayNewDrivers,
                    $growthStats
                ) {
                    $activityAll = $activityStatsAll[$fleet->id] ?? null;
                    $activity = $activityStats[$fleet->id] ?? null;
                    $calls = $callStats[$fleet->id] ?? null;
                    $newDrivers = $yesterdayNewDrivers[$fleet->id] ?? null;
                    $growth = $growthStats[$fleet->id] ?? null;

                    $count_today = $growth->count_today ?? 0;
                    $count_yesterday = $growth->count_yesterday ?? 0;

                    $fleet->activity_growth_percent = $count_yesterday > 0
                        ? round((($count_today - $count_yesterday) / $count_yesterday) * 100, 1)
                        : ($count_today > 0 ? 100 : 0);

                    $fleet->activity_total = $activity->total ?? 0;
                    $fleet->activity_active = $activity->active ?? 0;
                    $fleet->activity_notActive = $activity->notActive ?? 0;
                    $fleet->activity_yesterday = $activity->yesterday_active ?? 0;

                    $fleet->activityAll_total = $activityAll->total ?? 0;

                    $fleet->call_total = $calls->total_calls ?? 0;
                    $fleet->call_active = $calls->active_calls ?? 0;
                    $fleet->call_notActive = $calls->notActive_calls ?? 0;

                    $fleet->new_drivers_yesterday = $newDrivers->new_drivers_yesterday ?? 0;

                    return $fleet;
                });
        });

        return view('admin.reporting.fleetReportSummary', compact('fleets'));
    }



    public function driverActivityReportNonRepeat()
    {
        return view('admin.reporting.nonRepeate');
    }


    // خلاصه گزارش روز
    public function summaryOfDaysReport()
    {

        $todayDate = date('Y-m-d', time()) . ' 00:00:00';
        $yesterdayDate = date('Y-m-d', strtotime('-1 day', time())) . ' 00:00:00';
        $weekDate = date('Y-m-d', strtotime('last friday')) . ' 23:59:59';
        $monthDate = $this->getMonthDate();
        $lastMonthDate = $this->getLastMonthDate();

        $drivers = [
            'total' => Driver::count(),
            'todayPayment' => Transaction::where('created_at', '>', date('Y-m-d', time()) . ' 00:00:00')
                ->where('status', '>', 2)
                ->where('payment_type', '!=', 'gift')
                ->count(),
            'todayCartToCart' => Transaction::where('created_at', '>', date('Y-m-d', time()) . ' 00:00:00')
                ->where('status', '>', 2)
                ->where('payment_type', 'cardToCard')
                ->count(),
            'todayGift' => Transaction::where('created_at', '>', date('Y-m-d', time()) . ' 00:00:00')
                ->where('status', '>', 2)
                ->where('payment_type', 'gift')
                ->count(),
            'todayOnline' => Transaction::where('created_at', '>', date('Y-m-d', time()) . ' 00:00:00')
                ->where('status', '>', 2)
                ->where('payment_type', 'online')
                ->count(),
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
            'fullAuth' => Owner::where('isAccepted', 1)->count(),
            'weekLoads' => Load::where([
                ['created_at', '>', $weekDate],
                ['operator_id', 0]
            ])->withTrashed()->count(),
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


        $operators = [
            'toDayLoads' => Load::where([
                ['created_at', '>', $todayDate],
                ['operator_id', '>', 0]
            ])->withTrashed()->count(),
        ];

        $incomes = [
            'total' => Transaction::where('status', '>', 2)->sum('amount'),
            'toDay' => Transaction::where([
                ['created_at', '>', $todayDate],
                ['status', '>', 2]
            ])->sum('amount'),
            'yesterday' => Transaction::where([
                ['created_at', '>', $yesterdayDate],
                ['created_at', '<', $todayDate],
                ['status', '>', 2]
            ])->sum('amount'),
            'week' => Transaction::where([
                ['created_at', '>', $weekDate],
                ['status', '>', 2]
            ])->sum('amount'),
            'month' => Transaction::where([
                ['created_at', '>=', $monthDate],
                ['status', '>', 2]
            ])->sum('amount'),
            'lastMonth' => Transaction::whereBetween('created_at', [$lastMonthDate, $monthDate])
                ->where('status', '>', 2)
                ->sum('amount'),
            'drivers' => Transaction::where([
                ['created_at', '>', $weekDate],
                ['status', '>', 2],
                ['userType', ROLE_DRIVER]
            ])->sum('amount')
        ];


        //تعداد بار ثبت شده 30 روز قبل
        $countOfLoadsInPrevious30Days = []; // $this->countOfLoadsInPrevious30Days();

        return view('admin.reporting.summaryOfDaysReport', compact('drivers', 'owners', 'operators', 'incomes', 'countOfLoadsInPrevious30Days'));
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
                    ['status', '>', 2],
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

    public function searchDriverActivityReport(Request $request)
    {
        // return $request;
        $start = $request->fromDate;
        $end = $request->toDate;
        $startDate = persianDateToGregorian(str_replace('/', '-', $request->fromDate), '-') . ' 00:00:00';
        $endDate = persianDateToGregorian(str_replace('/', '-', $request->toDate), '-') . ' 23:59:00';

        $activityReportOfDriversFromPreviousMonth = DriverActivity::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as value')
            ->groupBy('day')
            ->get()
            ->map(function ($item) {
                return [
                    'label' => str_replace('-', '/', convertEnNumberToFa(gregorianDateToPersian($item->day, '-'))),
                    'value' => $item->value
                ];
            });
        // return $driverActivities;

        return view(
            'admin.reporting.searchDriverActivity',
            compact('activityReportOfDriversFromPreviousMonth', 'start', 'end')
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
    public function driversCountCall(Request $request, $basedCalls = [], $showSearchResult = false)
    {
        $fromDate = gregorianDateToPersian(date('Y/m/d', time()), '/');
        $toDate = $fromDate;
        $fleets = Fleet::where('parent_id', '!=', 0)->get();
        if (!$showSearchResult) {
            // محاسبه تعداد تماس‌های امروز برای هر راننده
            $basedCalls = DriverCallCount::join('drivers', 'driver_call_counts.driver_id', '=', 'drivers.id')
                ->groupBy('driver_call_counts.driver_id')
                ->select(
                    'driver_call_counts.driver_id',
                    'drivers.name',
                    'drivers.lastName',
                    // 'drivers.fleetTitle',
                    'drivers.mobileNumber',
                    'driver_call_counts.persian_date',
                    'driver_call_counts.created_date',
                    DB::raw('sum(driver_call_counts.calls) as countOfCalls')
                )
                ->orderByDesc('countOfCalls')
                ->where('driver_call_counts.persian_date', $fromDate)
                ->when($request->toDate !== null, function ($query) use ($request) {
                    return $query->whereBetween('driver_call_counts.created_date', [
                        persianDateToGregorian(str_replace('/', '-', $request->fromDate), '-') . ' 00:00:00',
                        persianDateToGregorian(str_replace('/', '-', $request->toDate), '-') . ' 23:59:59'
                    ]);
                })
                ->paginate(20);

            // دریافت شناسه‌های رانندگان برای محاسبه مجموع تماس‌ها
            $driverIds = $basedCalls->pluck('driver_id');

            // محاسبه مجموع تماس‌ها برای هر راننده
            $totalCalls = DriverCallCount::whereIn('driver_id', $driverIds)
                ->groupBy('driver_id')
                ->select('driver_id', DB::raw('sum(calls) as totalCalls'))
                ->get()
                ->keyBy('driver_id');

            // اضافه کردن مجموع تماس‌ها به نتیجه نهایی
            $basedCalls->each(function ($item) use ($totalCalls) {
                $item->totalCalls = $totalCalls[$item->driver_id]->totalCalls ?? 0;
            });

            // return $basedCalls;
        }



        return view('admin.reporting.driversCountCall', compact('basedCalls', 'fromDate', 'toDate', 'fleets'));
    }
    public function driversCountCallSearch(Request $request)
    {
        try {
            $fleets = Fleet::where('parent_id', '!=', 0)->get();

            $driverCalls = DriverCall::with('driver')
                ->when($request->mobileNumber !== null, function ($query) use ($request) {
                    return $query->whereHas('driver', function ($q) use ($request) {
                        $q->where('mobileNumber', $request->mobileNumber);
                    });
                })
                ->when($request->toDate !== null, function ($query) use ($request) {
                    return $query->whereBetween('created_at', [
                        persianDateToGregorian(str_replace('/', '-', $request->fromDate), '-') . ' 00:00:00',
                        persianDateToGregorian(str_replace('/', '-', $request->toDate), '-') . ' 23:59:59'
                    ]);
                })
                ->when($request->fleet_id !== null, function ($query) use ($request) {
                    return $query->whereHas('driver', function ($q) use ($request) {
                        $q->where('fleet_id', $request->fleet_id);
                    });
                })
                ->groupBy('callingDate')
                ->select('driver_calls.*', 'callingDate', DB::raw('count(*) as totalCalls'))
                ->orderByDesc('callingDate')
                ->get();

            return view('admin.reporting.driversCountCallSearch', compact('driverCalls', 'fleets', 'request'));
        } catch (\Throwable $th) {
            return back()->with('danger', 'تماس با این مشخصات یافت نشد');
        }
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

    public function usersByCustomProvinces(Request $request, ProvinceCity $provinceCity, $drivers = [], $showSearchResult = false)
    {
        $fromDate = persianDateToGregorian(str_replace('/', '-', $request->fromDate), '-') . ' 00:00:00';
        $toDate = persianDateToGregorian(str_replace('/', '-', $request->toDate), '-') . ' 23:59:00';
        if (!$showSearchResult)
            $drivers = Driver::where('province_id', $provinceCity->id)
                ->when($request->fleet_id !== null, function ($query) use ($request) {
                    $query->where('fleet_id', $request->fleet_id);
                })
                ->when($request->toDate !== null, function ($query) use ($fromDate, $toDate) {
                    $query->whereHas('driverActivities', function ($q) use ($fromDate, $toDate) {
                        $q->whereBetween('created_at', [$fromDate, $toDate]);
                    });
                })
                ->when($request->mobileNumber !== null, function ($query) use ($request) {
                    $query->where('mobileNumber', $request->mobileNumber);
                })
                ->paginate(10);

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
    private function getSearchActivityReportOfDriversFromPreviousMonth()
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
        $totalCargoOwners = Owner::count();

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
        // $depositDee60DaysInAdvance = $this->getDepositDee60DaysInAdvance(ROLE_OWNER);

        return view('admin.reporting.cargoOwnersActivityReport', compact('totalCargoOwners', 'increaseOfCargoOwnersSince12MonthsAgo', 'countOfCargoOwnersLoadsInPrevious30Days', 'cargoOwnersLoadsByFleetInPrevious30Days'));
    }

    // روند افزایش صاحب بار ها از 12 ماه قبل
    private function getIncreaseOfCargoOwnersSince12MonthsAgo($index)
    {
        $fromDate = persianDateToGregorian($this->persianDateList[$index] . '-01', '-');
        if ($index > 0) {
            $toDate = persianDateToGregorian($this->persianDateList[$index - 1] . '-01', '-');

            return Owner::where([
                ['created_at', '>=', $fromDate],
                ['created_at', '<', $toDate]
            ])->count();
        }

        return Owner::where('created_at', '>=', $fromDate)->count();
    }

    //تعداد بار ثبت شده 30 روز قبل صاحب بار
    private function getCountOfCargoOwnersLoadsInPrevious30Days()
    {
        $loadsCount = [];
        $usersCount = [];

        for ($day = 30; $day >= 0; $day--) {
            $dateFrom = date('Y-m-d', strtotime('-' . $day . ' day', time()));
            $dateTo = date('Y-m-d', strtotime('-' . ($day - 1) . ' day', time()));

            // تعداد کل بارهای ثبت‌شده
            $value = Load::where([
                ['created_at', '>=', $dateFrom . ' 00:00:00'],
                ['created_at', '<', $dateTo . ' 00:00:00'],
                ['userType', ROLE_OWNER],
                ['operator_id', 0],
                ['isBot', 0],
            ])
                ->withTrashed()
                ->count();

            // تعداد کاربران یکتایی که در این روز بار ثبت کرده‌اند
            $uniqueUsers = Load::where([
                ['created_at', '>=', $dateFrom . ' 00:00:00'],
                ['created_at', '<', $dateTo . ' 00:00:00'],
                ['userType', ROLE_OWNER],
                ['operator_id', 0],
                ['isBot', 0],
            ])
                ->withTrashed()
                ->distinct('user_id')
                ->count('user_id');

            $label = str_replace('-', '/', convertEnNumberToFa(gregorianDateToPersian($dateFrom, '-')));

            $loadsCount[] = [
                'value' => $value,
                'label' => $label
            ];

            $usersCount[] = [
                'value' => $uniqueUsers,
                'label' => $label
            ];
        }

        return [
            'loads' => $loadsCount,
            'users' => $usersCount
        ];
    }


    // ثبت بار به تفکیک ناوگان
    private function getCargoOwnersLoadsByFleetInPrevious30Days()
    {
        $loadsByFleets = FleetLoad::join('fleets', 'fleets.id', 'fleet_loads.fleet_id')
            ->where('userType', ROLE_OWNER)
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
    public function operatorsActivityReport($operator_id = null)
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
        $operatorsLoadsByFleet = $this->getOperatorsLoadsByFleet($operator_id);


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

    private function getOperatorsLoadsByFleet($userId = null)
    {
        $loadsByFleets = FleetLoad::join('fleets', 'fleets.id', '=', 'fleet_loads.fleet_id')
            ->join('loads', 'loads.id', '=', 'fleet_loads.load_id')
            ->where('loads.operator_id', '>', 0)
            ->when($userId, function ($query) use ($userId) {
                return $query->where('loads.operator_id', $userId);
            })
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
        try {

            $fleetRatioToDriverActivityReport = FleetRatioToDriverActivityReport::whereBetween('persianDate', [$fromDate, $toDate])
                ->leftJoin('drivers', 'drivers.fleet_id', '=', 'fleet_ratio_to_driver_activity_reports.fleet_id')
                ->leftJoin('transactions', function ($join) {
                    $join->on('transactions.user_id', '=', 'drivers.id')
                        ->where('transactions.status', '>', 2)
                        ->where('transactions.userType', 'driver');
                })
                ->select('fleet_ratio_to_driver_activity_reports.fleet_id')
                ->selectRaw('SUM(fleet_ratio_to_driver_activity_reports.countOfDrivers) AS countOfDrivers')
                ->selectRaw('SUM(fleet_ratio_to_driver_activity_reports.countOfActiveDrivers) AS countOfActiveDrivers')
                ->selectRaw('AVG(fleet_ratio_to_driver_activity_reports.countOfActiveDriverAccounts) AS countOfActiveDriverAccounts')
                ->selectRaw('SUM(fleet_ratio_to_driver_activity_reports.countOfOperatorsLoads) AS countOfOperatorsLoads')
                ->selectRaw('SUM(fleet_ratio_to_driver_activity_reports.countOfCargoOwnersLoads) AS countOfCargoOwnersLoads')
                ->selectRaw('SUM(fleet_ratio_to_driver_activity_reports.countOfTransportationsLoads) AS countOfTransportationsLoads')
                ->selectRaw('(SUM(fleet_ratio_to_driver_activity_reports.countOfOperatorsLoads)
                  + SUM(fleet_ratio_to_driver_activity_reports.countOfCargoOwnersLoads)
                  + SUM(fleet_ratio_to_driver_activity_reports.countOfTransportationsLoads)) AS countOfAllLoads')
                ->selectRaw("SUM(CASE WHEN transactions.payment_type = 'cardToCard' THEN 1 ELSE 0 END) AS countOfCardToCard")
                ->selectRaw("SUM(CASE WHEN transactions.payment_type = 'online' THEN 1 ELSE 0 END) AS countOfOnline")
                ->selectRaw("SUM(CASE WHEN transactions.payment_type = 'gift' THEN 1 ELSE 0 END) AS countOfGift")
                ->groupBy('fleet_ratio_to_driver_activity_reports.fleet_id')
                ->get();
        } catch (\Exception  $e) {
            // return 'زمان بر است';
            Log::warning("Fleet report query took too long and was killed: " . $e->getMessage());
            // $fleetRatioToDriverActivityReport = collect(); // نتیجه خالی

        }

        // return $fleetRatioToDriverActivityReport;
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
     * تاریخ شروع ماه جاری
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

    /**
     * تاریخ شروع ماه قبل
     * @return string
     */
    private function getLastMonthDate(): string
    {
        $monthDate = explode('-', gregorianDateToPersian(date('Y-m-d', time()), '-'));
        if (isset($monthDate[0]) && isset($monthDate[1])) {
            // یک ماه از ماه جاری کم می‌کنیم
            $year = (int)$monthDate[0];
            $month = (int)$monthDate[1] - 1;

            if ($month <= 0) {
                $month = 12;
                $year--;
            }

            $month = str_pad($month, 2, '0', STR_PAD_LEFT);

            $lastMonthDate = persianDateToGregorian($year . '-' . $month . '-01', '-') . ' 00:00:00';
        } else {
            $lastMonthDate = date('Y-m-d', strtotime('-60 day', time())) . ' 00:00:00';
        }

        return $lastMonthDate;
    }

    /****************************************************************************************************/

    // گزارش پرداخت ها
    public function paymentReport($userType, $status)
    {

        if ($status == 0 || $status == 2) {
            $today = date('Y-m-d', time()) . ' 00:00:00';
            $successTransactions = Transaction::where([
                ['status', '>', 2],
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
        } else if ($status > 2)
            $transactions = Transaction::where([
                ['status', '>', 2],
                ['userType', $userType]
            ])->orderBy('id', 'desc')->paginate(100);

        $counter = [
            'unsuccess' => Transaction::where([['status', 0], ['userType', $userType]])->count(),
            'success' => Transaction::where([['status', '>', 2], ['userType', $userType]])->count()
        ];
        return view('admin.reporting.paymentReport', compact('transactions', 'counter'));
    }

    public function viewPDF(Request $request)
    {
        // $today = date('Y-m-d', time()) . ' 00:00:00';
        $fromDate = persianDateToGregorian(str_replace('/', '-', $request->from), '-') . ' 00:00:00';
        $toDate = persianDateToGregorian(str_replace('/', '-', $request->to), '-') . ' 00:00:00';

        $transactions = Transaction::where('status', '>', 2)
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
            ['status', '>', 2],
            ['userType', 'driver'],
            ['created_at', '>=', $today]
        ])->pluck('user_id');

        $transactions = Transaction::where([
            ['userType', 'driver'],
            ['created_at', '>=', $today]
        ])
            ->whereIn('status', [0, 2])
            ->whereNotIn('user_id', $successTransactions)
            ->select('*', DB::raw('count(*) as total'))
            ->orderByDesc('updated_at')
            ->groupby('user_id')
            ->paginate(100);
        return view('admin.reporting.unSuccessPeyment', compact('transactions'));
    }

    // گزارش بیشترین پرداخت رانندگان
    public function mostPaidDriversReport()
    {
        $transactions = Transaction::where([
            ['status', '>', 2],
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
                ['transactions.status', '>', 2],
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
            // return $cargoReports;
            $fleets = Fleet::where('parent_id', '>', 0)->orderBy('parent_id', 'asc')->get();
            return view('admin.reporting.cargoFleetsReport', compact('cargoReports', 'fleets'));
        } catch (\Exception $exception) {
            Log::emergency("---------------------------------- cargoFleetsReport ---------------------------------");
            Log::emergency($exception->getMessage());
            Log::emergency("------------------------------------------------------------------------------------");
        }
    }

    public function searchCargoFleetsReport($fleet_id)
    {
        $loads = Load::where('fleets', 'Like', '%fleet_id":' . $fleet_id . ',%')
            ->select('origin_state_id',  'fleets',  DB::raw('count(`origin_state_id`) as count'))
            ->groupBy('origin_state_id')
            ->having('count', '>', 1)
            ->withTrashed()
            ->paginate(20);

        return view('admin.reporting.searchCargoFleets', compact('loads', 'fleet_id'));
    }

    public function searchCargoFleetsReportCity($fleet_id, $origin_state_id)
    {
        $loads = Load::where('fleets', 'Like', '%fleet_id":' . $fleet_id . ',%')
            ->where('origin_state_id', $origin_state_id)
            ->select('origin_city_id', 'origin_state_id',  'fleets',  DB::raw('count(`origin_city_id`) as count'))
            ->groupBy('origin_city_id')
            ->having('count', '>', 1)
            ->withTrashed()
            ->paginate(20);
        return view('admin.reporting.searchCargoFleetsCity', compact('loads', 'fleet_id'));
    }

    // جستجو گزارش بار ها به تفکیک ناوگان
    public function searchCargoFleets(Request $request)
    {
        try {
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
            $total_sum = CargoReportByFleet::with('fleet')
                ->whereBetween('created_at', [$fromDate, $toDate])
                ->when($request->fleet_id !== null, function ($query) use ($request) {
                    return $query->where('fleet_id', $request->fleet_id);
                })
                ->selectRaw('SUM(count + count_owner) as total_sum')->pluck('total_sum')->first();
            // $fleets = Fleet::where('parent_id', '>', 0)->orderBy('parent_id', 'asc')->get();
            return view('admin.reporting.searchCargoFleetsReport', compact('cargoReports', 'total_sum'));
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
