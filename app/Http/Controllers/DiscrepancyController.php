<?php

namespace App\Http\Controllers;

use App\Models\Discrepancy;
use App\Models\Transaction;
use App\Models\TransactionManual;
use Illuminate\Http\Request;

class DiscrepancyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $fromDate = persianDateToGregorian(str_replace('/', '-', $request->fromDate), '-') . ' 00:00:00';
        $toDate = persianDateToGregorian(str_replace('/', '-', $request->toDate), '-') . ' 00:00:00';
        $discrepancies = Discrepancy::orderByDesc('created_at')
            ->when($request->toDate !== null, function ($query) use ($fromDate, $toDate) {
                return $query->whereBetween('created_at', [$fromDate, $toDate]);
            })
            ->paginate(100);
        return view('admin.discrepancy.index', compact('discrepancies'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $todayDate = date('Y/m/d', time()) . ' 00:00:00';
        $oldtransactionManuals = TransactionManual::with('driver')
            ->where('status', '1')
            ->where('miladiDate', '>', $todayDate)
            ->Where('driver_id', '!=', '147552')
            ->withTrashed()
            ->get();

        $oldtransactionNonDrivers = TransactionManual::with('driver')
            // ->where('status', '1')
            ->where('miladiDate', '>', $todayDate)
            ->Where('driver_id', '147552')
            ->withTrashed()
            ->get();
        $anotherTransactions = $oldtransactionManuals->merge($oldtransactionNonDrivers)->sortByDesc('miladiDate');

        $onlineTotal = Transaction::where([
            ['created_at', '>', date('Y-m-d', time()) . ' 00:00:00'],
            ['status', '>', 2]
        ])->sum('amount');

        $cardToCardTotal = $anotherTransactions->sum('amount');
        return view('admin.discrepancy.create', compact('cardToCardTotal', 'onlineTotal'));
        // return $incomesToDay;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $discrepancy = new Discrepancy();
        $discrepancy->total_card = $request->total_card;
        $discrepancy->total_site = $request->total_site;
        $discrepancy->total_all = $request->total_all;
        $discrepancy->save();
        return back()->with('success', 'با موفقیت ثبت شد.');
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
    public function update(Request $request, $id)
    {
        //
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
