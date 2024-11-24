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
    public function index()
    {
        $transactionManuals = TransactionManual::with('driver')
        ->orderByDesc('created_at')
        ->paginate(30);
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
            $transactionManual->date = $request->date . " " . $request->time;
            $transactionManual->save();
            return back()->with('success', 'آیتم مورد نظر ثبت شد');
        }
        return back()->with('danger', 'راننده با این مشخصات یافت نشد');

    }

    public function changeStatus(TransactionManual $transactionManual)
    {
        $transactionManual->status = 1;
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
