<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\FreeSubscription;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\TransactionManual;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionManualController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // تاریخ امروز
        $transactionManuals = TransactionManual::with('driver')
            ->select('*', DB::raw('count(`driver_id`) as total'))
            ->groupBy('driver_id')
            ->where('created_at', '>', date('Y-m-d', time()) . ' 00:00:00')
            ->orderByDesc('created_at')
            ->paginate(150);


        $oldtransactionManuals = TransactionManual::with('driver')
            ->where('status', '1')
            ->where('created_at', '>', date('Y-m-d', time()) . ' 00:00:00')
            ->orWhere('driver_id', '147552')
            ->withTrashed()
            ->orderByDesc('miladiDate')
            ->paginate(150);
        // return $oldtransactionManuals;
        return view('admin.transactionManual.index', compact('transactionManuals', 'oldtransactionManuals'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $driver = Driver::where('mobileNumber', $request->mobileNumber)->first();
        if ($driver) {
            $duplicateTransaction = TransactionManual::where('driver_id', $driver->id)->orderByDesc('created_at')->first();
            if ($duplicateTransaction) {
                $to = Carbon::createFromFormat('Y-m-d H:s:i', $duplicateTransaction->miladiDate);
                $from = Carbon::createFromFormat('Y-m-d H:s:i', persianDateToGregorian(str_replace('/', '-', $request->date), '-') . ' 00:00:00');
                $diff_in_days = $to->diffInDays($from);
                if ($diff_in_days > 30) {
                    TransactionManual::whereDriverId($driver->id)->update(['status' => 0]);
                    TransactionManual::whereDriverId($driver->id)->delete();
                }
            }
            $transactionManual = new TransactionManual();
            $transactionManual->amount = $request->amount;
            $transactionManual->driver_id = $driver->id;
            $transactionManual->type = $request->type;
            $transactionManual->description = $request->description;
            $transactionManual->status = 2;
            $transactionManual->date = $request->date . " " . $request->time;
            $transactionManual->miladiDate = persianDateToGregorian(str_replace('/', '-', $request->date), '-') . ' ' . $request->time;
            $transactionManual->save();
            return back()->with('success', 'آیتم مورد نظر ثبت شد');
        }
        return back()->with('danger', 'راننده با این مشخصات یافت نشد');
    }

    public function changeStatus(Request $request, TransactionManual $transactionManual)
    {
        $driver = Driver::where('mobileNumber', $request->mobileNumber)->first();

        $transactionManual->status = $request->status;
        $transactionManual->driver_id = $driver->id;
        $transactionManual->save();
        if ($request->status == 1) {
            $driver = Driver::find($transactionManual->driver_id);
            $currentDate = Carbon::now();
            $difference = $currentDate->diffInDays($driver->activeDate);
            if ($currentDate < $driver->activeDate && $difference > 15) {
                TransactionManual::whereDriverId($transactionManual->driver_id)->delete();
                return redirect()->route('transaction-manual.index')->with('danger', 'اشتراک مورد نظر بیشتر از 15 روز می باشد');
            }

            if ($transactionManual->amount == MONTHLY) {
                $this->updateActivationDateAndFreeCallsAndFreeAcceptLoads($driver, 1);
            } elseif ($transactionManual->amount == TRIMESTER) {
                $this->updateActivationDateAndFreeCallsAndFreeAcceptLoads($driver, 3);
            } elseif ($transactionManual->amount == SIXMONTHS) {
                $this->updateActivationDateAndFreeCallsAndFreeAcceptLoads($driver, 6);
            }
        }

        TransactionManual::whereDriverId($transactionManual->driver_id)->delete();

        return redirect()->route('transaction-manual.index')->with('success', 'آیتم مورد نظر تغییر کرد');
    }

    /**
     * @param Driver $driver
     * @param Request $request
     * @return void
     * @throws Exception
     */
    public function updateActivationDateAndFreeCallsAndFreeAcceptLoads(Driver $driver, $month): bool
    {
        try {

            $date = new \DateTime($driver->activeDate);
            $time = $date->getTimestamp();
            if ($time < time())
                $driver->activeDate = date('Y-m-d', time() + $month * 30 * 24 * 60 * 60);
            else
                $driver->activeDate = date('Y-m-d', $time + $month * 30 * 24 * 60 * 60);

            $driver->save();
            $persian_date = gregorianDateToPersian(date('Y/m/d', time()), '/');
            $oneMonth = gregorianDateToPersian(date('Y/m/d', strtotime('+30 day', time())), '/');
            $threeMonth = gregorianDateToPersian(date('Y/m/d', strtotime('+90 day', time())), '/');
            $sixMonth = gregorianDateToPersian(date('Y/m/d', strtotime('+180 day', time())), '/');
            $setting = Setting::first();
            try {
                if ($month > 0) {
                    $free_subscription = new FreeSubscription();
                    $free_subscription->type = AUTH_VALIDITY;
                    $free_subscription->value = $month;
                    $free_subscription->driver_id = $driver->id;
                    $free_subscription->operator_id = Auth::id();
                    $free_subscription->save();

                    $months = [1 => $oneMonth, 3 => $threeMonth, 6 => $sixMonth];
                    $amountKeys = [
                        1 => 'monthly',
                        3 => 'trimester',
                        6 => 'sixMonths'
                    ];

                    if (array_key_exists($month, $months)) {
                        $sms = new Driver();

                        if ($setting->sms_panel == 'SMSIR') {
                            $sms->freeSubscriptionSmsIr($driver->mobileNumber, $persian_date, $months[$month]);
                        } else {
                            $sms->freeSubscription($driver->mobileNumber, $persian_date, $months[$month]);
                        }
                        $driverPackagesInfo = getDriverPackagesInfo();
                        $amount = $driverPackagesInfo['data'][$amountKeys[$month]]['price'];
                    }


                    $transaction = new Transaction();
                    $transaction->user_id = $driver->id;
                    $transaction->userType = ROLE_DRIVER;
                    $transaction->authority = $driver->id . time();
                    $transaction->amount = $amount;
                    $transaction->status = 100;
                    $transaction->payment_type = 'cardToCard';
                    $transaction->monthsOfThePackage = $month;
                    $transaction->save();
                }
            } catch (Exception $e) {
            }

            return true;
        } catch (Exception $exception) {
        }

        return false;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($driverId)
    {
        $driver = Driver::find($driverId);

        $transactionManuals = TransactionManual::with('driver')
            ->whereDriverId($driverId)
            ->orderByDesc('date')
            ->get();
        return view('admin.transactionManual.show', compact('transactionManuals', 'driver'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, string $transactionManualId)
    {
        $transactionManual = TransactionManual::whereId($transactionManualId)->withTrashed()->first();

        $driver = Driver::where('mobileNumber', $request->mobileNumber)->first();
        if ($driver) {
            $transactionManual->amount = $request->amount;
            $transactionManual->driver_id = $driver->id;
            $transactionManual->type = $request->type;
            $transactionManual->date = $request->date . " " . $request->time;
            $transactionManual->description = $request->description;
            $transactionManual->miladiDate = persianDateToGregorian(str_replace('/', '-', $request->date), '-') . ' ' . $request->time;
            $transactionManual->save();
            return redirect()->route('transaction-manual.index')->with('success', 'آیتم مورد نظر ثبت شد');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(string $transactionManualId)
    {
        $transactionManual = TransactionManual::whereId($transactionManualId)->withTrashed()->first();
        $transactionManual->delete();
        return redirect()->route('transaction-manual.index')->with('danger', 'آیتم مورد نظر حذف شد');
    }

    public function search(Request $request)
    {
        $transactionManuals = TransactionManual::with('driver')
            ->when($request->mobileNumber !== null, function ($query) use ($request) {
                return $query->whereHas('driver', function ($q) use ($request) {
                    $q->where('mobileNumber', $request->mobileNumber);
                });
            })
            ->when($request->toDate !== null, function ($query) use ($request) {
                return $query->whereBetween('miladiDate', [persianDateToGregorian(str_replace('/', '-', $request->fromDate), '-') . ' 00:00:00', persianDateToGregorian(str_replace('/', '-', $request->toDate), '-') . ' 23:59:59']);
            })
            ->when($request->status !== null, function ($query) use ($request) {
                return $query->whereStatus('1');
            })
            ->get();

        $oldtransactionManuals = TransactionManual::with('driver')
            ->where('status', '1')
            ->orWhere('driver_id', '147552')
            ->when($request->toDate !== null, function ($query) use ($request) {
                return $query->whereBetween('miladiDate', [persianDateToGregorian(str_replace('/', '-', $request->fromDate), '-') . ' 00:00:00', persianDateToGregorian(str_replace('/', '-', $request->toDate), '-') . ' 23:59:59']);
            })
            ->when($request->status !== null, function ($query) use ($request) {
                return $query->whereStatus('1');
            })
            ->withTrashed()
            ->orderByDesc('miladiDate')
            ->paginate(150);
        return view('admin.transactionManual.search', compact('transactionManuals', 'oldtransactionManuals'));
    }
}
