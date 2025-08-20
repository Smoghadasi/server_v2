<?php

namespace App\Http\Controllers;

use App\Models\Fleet;
use App\Models\FreightInquiry;
use App\Models\ProvinceCity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class FreightInquiryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $freightInquiries = FreightInquiry::with('fromCity', 'toCity', 'fleet')
            ->orderByDesc('created_at')
            ->paginate(20);
        $cities = Cache::remember('province', now()->addMinutes(60), function () {
            return ProvinceCity::where('parent_id', '!=', 0)->select('id', 'name', 'parent_id')->get();
        });

        $fleets = Cache::remember('fleets', now()->addMinutes(60), function () {
            return Fleet::where('parent_id', '!=', 0)->select('id', 'title')->orderBy('parent_id', 'asc')->get();
        });
        return view('admin.freightInquiry.index', compact('freightInquiries', 'fleets', 'cities'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
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
        $freightInquiry = new FreightInquiry();
        $freightInquiry->fleet_id = $request->fleet_id;
        $freightInquiry->from_city_id = $request->from_city_id;
        $freightInquiry->to_city_id = $request->to_city_id;
        $freightInquiry->price = $request->price;
        $freightInquiry->status = 1;
        $freightInquiry->save();
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
    public function update(Request $request, FreightInquiry $freightInquiry)
    {
        // return $freightInquiry;
        $freightInquiry->fleet_id = $request->fleet_id;
        $freightInquiry->from_city_id = $request->from_city_id;
        $freightInquiry->to_city_id = $request->to_city_id;
        $freightInquiry->price = $request->price;
        $freightInquiry->status = $request->status;
        $freightInquiry->save();
        return back()->with('success', 'با موفقیت ویرایش شد.');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(FreightInquiry $freightInquiry)
    {
        $freightInquiry->delete();
        return back()->with('danger', 'با موفقیت حذف شد.');

    }
}
