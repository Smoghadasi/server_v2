<?php

namespace App\Http\Controllers;

use App\Models\LimitCall;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LimitCallController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $limitCalls = LimitCall::with('operator')
            ->when($request->mobileNumber !== null, function ($query) use ($request) {
                $query->where('mobileNumber', 'LIKE', '%' . $request->mobileNumber . '%');
            })
            ->orderByDesc('created_at')
            ->paginate(20);
        return view('admin.limitCalls.index', compact('limitCalls'));
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
        $mobileNumber = ParameterController::convertNumbers($request->mobileNumber);
        // return $request;

        if (LimitCall::where('mobileNumber', $mobileNumber)->count() > 0) {
            return back()->with('danger', 'این شماره موبایل قبلا ثبت شده است.');
        }
        LimitCall::create([
            'mobileNumber' => $request->mobileNumber,
            'type' => $request->type,
            'value' => $request->value,
            'operator_id' => Auth::id(),
        ]);
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
    public function update(Request $request, LimitCall $limitCall)
    {
        $mobileNumber = ParameterController::convertNumbers($request->mobileNumber);
        if (LimitCall::where('mobileNumber', $mobileNumber)->where('id', '!=', $limitCall->id)->count() > 0) {
            return back()->with('danger', 'این شماره موبایل قبلا ثبت شده است.');
        }

        $limitCall->update([
            'mobileNumber' => $request->mobileNumber,
            'type' => $request->type,
            'value' => $request->value,
        ]);
        return back()->with('success', 'با موفقیت ثبت شد.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(LimitCall $limitCall)
    {
        $limitCall->delete();
        return back()->with('danger', 'با موفقیت حذف شد.');
    }
}
