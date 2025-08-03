<?php

namespace App\Http\Controllers;

use App\Models\FleetlessNumbers;
use Illuminate\Http\Request;

class FleetlessNumberController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $fleetlessNumbers = FleetlessNumbers::paginate(20);
        return view('admin.fleetlessNumber.index', compact('fleetlessNumbers'));
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
        FleetlessNumbers::create([
            'mobileNumber' => $request->mobileNumber
        ]);
        return redirect()->back()->with('success', 'شماره موبایل مورد نظر اضافه شد');
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
    public function update(Request $request, FleetlessNumbers $fleetlessNumber)
    {
        $fleetlessNumber->update([
            'mobileNumber' => $request->mobileNumber
        ]);
        return redirect()->back()->with('success', 'شماره موبایل مورد نظر ویرایش شد');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(FleetlessNumbers $fleetlessNumber)
    {
        $fleetlessNumber->delete();
        return redirect()->back()->with('danger', 'شماره موبایل مورد نظر حذف شد');
    }
}
