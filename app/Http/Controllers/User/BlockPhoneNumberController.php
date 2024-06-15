<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\BlockPhoneNumber;
use Illuminate\Http\Request;

class BlockPhoneNumberController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($blockedPhoneNumbers = [], $showSearchResult = false)
    {
        if (!$showSearchResult)
            $blockedPhoneNumbers = BlockPhoneNumber::orderByDesc('created_at')->paginate(20);
        return view('admin.blockedPhoneNumbers', compact('blockedPhoneNumbers'));
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
        if (BlockPhoneNumber::where('phoneNumber', $request->phoneNumber)->count())
            return back()->with('danger', 'شماره تلفن ' . $request->phoneNumber . ' قبلا به لیست ممنوعه اضافه شده است.');

        if (strlen($request->phoneNumber) != 11)
            return back()->with('danger', 'شماره تلفن باید 11 رقم باشد.');

        $blockedPhoneNumber = new BlockPhoneNumber();
        $blockedPhoneNumber->phoneNumber = $request->phoneNumber;
        $blockedPhoneNumber->nationalCode = $request->nationalCode;
        $blockedPhoneNumber->name = $request->name;
        $blockedPhoneNumber->description = $request->description;
        $blockedPhoneNumber->save();

        return back()->with('success', 'شماره تلفن ' . $request->phoneNumber . ' به لیست ممنوعه اضافه شد.');
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
    public function destroy($phoneNumber)
    {
        BlockPhoneNumber::where('phoneNumber', $phoneNumber)->delete();
        return back()->with('success', 'شماره تلفن ' . $phoneNumber . ' از لیست ممنوعه حذف شد.');
    }

    public function searchBlockedPhoneNumber(Request $request)
    {
        $condition = [];
        if (isset($request->mobileNumber) && strlen($request->mobileNumber))
            $condition[] = ['phoneNumber', 'like', '%' . $request->mobileNumber . '%'];

        if (isset($request->nationalCode) && strlen($request->nationalCode))
            $condition[] = ['nationalCode', 'like', '%' . $request->nationalCode . '%'];

        if (isset($request->name) && strlen($request->name))
            $condition[] = ['name', 'like', '%' . $request->name . '%'];

        $blockedPhoneNumbers = BlockPhoneNumber::orderByDesc('created_at')
            ->where($condition)
            ->paginate(20);

        if (count($blockedPhoneNumbers))
            return $this->index($blockedPhoneNumbers, true);

        return back()->with('danger', 'آیتم پیدا نشد!');
    }
}
