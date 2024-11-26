<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\TransactionManual;
use Illuminate\Http\Request;

class TransactionManualController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
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
            ->orderByDesc('created_at')
            ->paginate(150);
        return view('admin.transactionManual.index', compact('transactionManuals'));
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
            $transactionManual = new TransactionManual();
            $transactionManual->amount = $request->amount;
            $transactionManual->driver_id = $driver->id;
            $transactionManual->type = $request->type;
            $transactionManual->status = 2;
            $transactionManual->date = $request->date . " " . $request->time;
            $transactionManual->miladiDate = persianDateToGregorian(str_replace('/', '-', $request->date), '-') . ' 00:00:00';
            $transactionManual->save();
            return back()->with('success', 'آیتم مورد نظر ثبت شد');
        }
        return back()->with('danger', 'راننده با این مشخصات یافت نشد');
    }

    public function changeStatus(TransactionManual $transactionManual, $status = 1)
    {
        $transactionManual->status = $status;
        $transactionManual->save();
        return back()->with('success', 'آیتم مورد نظر حذف شد');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
    public function update(Request $request, TransactionManual $transactionManual)
    {
        $driver = Driver::where('mobileNumber', $request->mobileNumber)->first();
        if ($driver) {
            $transactionManual->amount = $request->amount;
            $transactionManual->driver_id = $driver->id;
            $transactionManual->type = $request->type;
            $transactionManual->date = $request->date . " " . $request->time;
            $transactionManual->miladiDate = persianDateToGregorian(str_replace('/', '-', $request->date), '-') . ' 00:00:00';
            $transactionManual->save();
            return back()->with('success', 'آیتم مورد نظر ثبت شد');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
