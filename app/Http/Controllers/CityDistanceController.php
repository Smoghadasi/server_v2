<?php

namespace App\Http\Controllers;

use App\Models\CityDistanceCalculate;
use Illuminate\Http\Request;

class CityDistanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $cityDistances = CityDistanceCalculate::with(['fromCity', 'toCity'])->orderBy('value', 'asc')->paginate(30);
        // return $cityDistances;
        return view('admin.cityDistance.index', compact('cityDistances'));
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
    public function update(Request $request, CityDistanceCalculate $cityDistanceCalculate)
    {
        $cityDistanceCalculate->value = $request->value;
        $cityDistanceCalculate->save();
        return back()->with('success', 'ویرایش شد');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(CityDistanceCalculate $cityDistanceCalculate)
    {
        $cityDistanceCalculate->delete();
        return back()->with('success', 'ویرایش شد');
    }
}
