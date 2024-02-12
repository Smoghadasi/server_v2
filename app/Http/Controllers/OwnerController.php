<?php

namespace App\Http\Controllers;

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
        $ownerPenddingCounts = Owner::where('isAuth', 2)->count();
        $ownerRejectCounts = Owner::where('isAuth', 0)->count();
        $ownerAcceptCounts = Owner::where('isAuth', 1)->count();

        $owners = Owner::orderByDesc('created_at')->paginate(10);
        return view('admin.owner.index', compact('owners', 'ownerPenddingCounts', 'ownerRejectCounts', 'ownerAcceptCounts'));
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
    public function show(Owner $owner)
    {
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
    public function destroy($id)
    {
        //
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
        $owners = Owner::where('nationalCode', 'LIKE', "%$request->searchWord%")
            ->orWhere('mobileNumber', 'LIKE', "%$request->searchWord%")
            ->orderby('id', 'desc')
            ->paginate(5);
        return view('admin.owner.index', compact('owners'));
    }
}
