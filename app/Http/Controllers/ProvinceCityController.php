<?php

namespace App\Http\Controllers;

use App\Models\ProvinceCity;
use Illuminate\Http\Request;

class ProvinceCityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $provinces = ProvinceCity::where('parent_id', 0)->get();
        return view('admin.provinceCity.index', compact('provinces'));
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
        try {
            $city = new ProvinceCity();
            $city->latitude = $request->latitude;
            $city->longitude = $request->longitude;
            $city->name = $request->name;
            $city->parent_id = $request->parent_id;
            $city->save();

            return back()->with('success', 'شهر ' . $city->name . ' اضافه شد.');
        } catch (\Exception $exception) {
            return $exception;
        }
        return back()->with('danger', 'خطا در ثبت شهر جدید');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(ProvinceCity $provinceCity)
    {
        $provinces = ProvinceCity::where('parent_id', $provinceCity->id)->get();
        return view('admin.provinceCity.show', compact('provinceCity', 'provinces'));
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
    public function update(Request $request, ProvinceCity $provinceCity)
    {
        $provinceCity->name = $request->name;
        $provinceCity->save();
        return back()->with('success', 'شهر ' . $provinceCity->name . ' ویرایش.');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProvinceCity $provinceCity)
    {
        try {
            $provinceCity->delete();
            return back()->with('success', 'شهر مورد نظر حذف شد.');
        } catch (\Exception $exception) {
            return back()->with('danger', 'خطا در حذف شهر جدید');
        }

    }
}
