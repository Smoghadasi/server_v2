<?php

namespace App\Http\Controllers;

use App\Models\Load;
use App\Models\Owner;
use Illuminate\Http\Request;

class OwnerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $loadsToday = Load::where('userType', ROLE_OWNER)
            ->where('created_at', '>', date('Y-m-d', time()) . ' 00:00:00')
            ->withTrashed()
            ->count();
        $loadsTodayOwner = Load::where('userType', ROLE_OWNER)
            ->where('created_at', '>', date('Y-m-d', time()) . ' 00:00:00')
            ->where('isBot', 0)
            ->withTrashed()
            ->count();
        $ownerPenddingCounts = Owner::where('isAuth', 2)->count();
        $ownerRejectCounts = Owner::where('isAuth', 0)->count();
        $ownerAcceptCounts = Owner::where('isAuth', 1)->count();

        $owners = Owner::orderByDesc('created_at')->paginate(10);
        return view('admin.owner.index', compact([
            'owners',
            'ownerPenddingCounts',
            'ownerRejectCounts',
            'ownerAcceptCounts',
            'loadsToday',
            'loadsTodayOwner'
        ]));
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

    public function generateSKU()
    {
        $number = mt_rand(10000, 99999);
        if ($this->checkSKU($number)) {
            return $this->generateSKU();
        }
        return (string)$number;
    }

    public function checkSKU($number)
    {
        return Owner::where('sku', $number)->exists();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Owner $owner)
    {
        if ($owner->isAccepted == 1 && $owner->sku == null) {
            $owner->sku = $this->generateSKU();
            $owner->save();
        }
        return view('admin.owner.show', compact('owner'));
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
    public function destroy(Owner $owner)
    {
        $owner->delete();
        return redirect()->route('owner.index')->with('danger', 'صاحب بار مورد نظر حذف شد');
    }

    public function changeOwnerStatus(Owner $owner)
    {
        $owner->status = !$owner->status;
        $owner->save();
        return back()->with("danger", "وضعیت با موفقیت تغییر کرد");
    }

    // جستجوی صاحبان بار
    public function searchOwners(Request $request)
    {
        $loadsToday = Load::where('userType', ROLE_OWNER)
            ->where('created_at', '>', date('Y-m-d', time()) . ' 00:00:00')
            ->withTrashed()
            ->count();
        $loadsTodayOwner = Load::where('userType', ROLE_OWNER)
            ->where('created_at', '>', date('Y-m-d', time()) . ' 00:00:00')
            ->where('isBot', 0)
            ->withTrashed()
            ->count();
        $ownerPenddingCounts = Owner::where('isAuth', 2)->count();
        $ownerRejectCounts = Owner::where('isAuth', 0)->count();
        $ownerAcceptCounts = Owner::where('isAuth', 1)->count();

        $owners = Owner::where('nationalCode', 'LIKE', "%$request->searchWord%")
            ->orWhere('mobileNumber', 'LIKE', "%$request->searchWord%")
            ->orWhere('name', 'LIKE', "%$request->searchWord%")
            ->orWhere('lastName', 'LIKE', "%$request->searchWord%")
            ->orderby('id', 'desc')
            ->paginate(5);
        return view('admin.owner.index', compact(
            'owners',
            'ownerPenddingCounts',
            'ownerRejectCounts',
            'ownerAcceptCounts',
            'loadsToday',
            'loadsTodayOwner'
        ));
    }

    public function removeProfile(Owner $owner)
    {
        $owner->profileImage = null;
        $owner->save();
        return back()->with("danger", "پروفایل حذف شد");
    }

    public static function getNumOfAuthOwner()
    {
        $ownerPenddingCounts = Owner::where('isAuth', 2)->count();
        return $ownerPenddingCounts;
    }

    public function ownersNissan()
    {
        $owners = Owner::whereHas('loads', function ($q) {
            $q->whereIn('origin_city_id', [967, 570, 758, 522, 360, 45]);
            $q->where('fleets', 'Like', '%fleet_id":' . 82 . ',%');
            $q->where('userType', 'owner');
        })
        ->paginate();

        $loadsToday = Load::where('userType', ROLE_OWNER)
            ->where('created_at', '>', date('Y-m-d', time()) . ' 00:00:00')
            ->withTrashed()
            ->count();
        $loadsTodayOwner = Load::where('userType', ROLE_OWNER)
            ->where('created_at', '>', date('Y-m-d', time()) . ' 00:00:00')
            ->where('isBot', 0)
            ->withTrashed()
            ->count();
        $ownerPenddingCounts = Owner::where('isAuth', 2)->count();
        $ownerRejectCounts = Owner::where('isAuth', 0)->count();
        $ownerAcceptCounts = Owner::where('isAuth', 1)->count();

        return view('admin.owner.index', compact([
            'owners',
            'ownerPenddingCounts',
            'ownerRejectCounts',
            'ownerAcceptCounts',
            'loadsToday',
            'loadsTodayOwner'
        ]));
    }
}
