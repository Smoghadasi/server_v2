<?php

namespace App\Http\Controllers;

use App\Models\Support;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $fromDate = persianDateToGregorian(str_replace('/', '-', $request->fromDate), '-') . ' 00:00:00';
        $toDate = persianDateToGregorian(str_replace('/', '-', $request->toDate), '-') . ' 23:59:59';
        $supports = Support::with('driver', 'owner', 'user')
            ->where('type', $request->type)
            ->when($request->toDate !== null, function ($query) use ($fromDate, $toDate) {
                return $query->whereBetween('created_at', [$fromDate, $toDate]);
            })
            ->orderByDesc('created_at')
            ->paginate(15);

        if ($request->type == 'driver') {
            return view('admin.support.driver', compact('supports'));
        }
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($driverId)
    {
        $supports = Support::with('driver', 'owner', 'user')
            ->where('type', 'Driver')
            ->where('driver_id', $driverId)
            ->orderByDesc('created_at')
            ->paginate(15);
        return view('admin.support.driver', compact('supports'));
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
    public function update(Request $request, Support $support)
    {
        $support->result = $request->result;
        $support->subject = $request->subject;
        $support->user_id = Auth::id();
        $support->save();
        return back()->with('success', 'با موفقیت ثبت شد');
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
