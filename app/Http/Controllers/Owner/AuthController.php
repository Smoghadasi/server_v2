<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\OperatorOwnerAuthMessage;
use App\Models\Owner;
use Auth;
use Exception;
use Illuminate\Http\Request;
use Log;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $owners = Owner::where('isAuth', 2)->paginate(10);
        $ownerPenddingCounts = Owner::where('isAuth', 2)->count();
        $ownerRejectCounts = Owner::where('isAuth', 0)->count();
        $ownerAcceptCounts = Owner::where('isAuth', 1)->count();

        return view('admin.auth.owner.index', compact(
            'owners',
            'ownerPenddingCounts',
            'ownerRejectCounts',
            'ownerAcceptCounts'
        ));
    }

    public function ownerReject()
    {
        $owners = Owner::where('isAuth', 0)->paginate(10);
        $ownerPenddingCounts = Owner::where('isAuth', 2)->count();
        $ownerRejectCounts = Owner::where('isAuth', 0)->count();
        $ownerAcceptCounts = Owner::where('isAuth', 1)->count();

        return view('admin.auth.owner.reject', compact(
            'owners',
            'ownerPenddingCounts',
            'ownerRejectCounts',
            'ownerAcceptCounts'
        ));
    }

    public function ownerAccept()
    {
        $owners = Owner::where('isAuth', 1)->paginate(10);
        $ownerAcceptCounts = Owner::where('isAuth', 1)->count();
        $ownerPenddingCounts = Owner::where('isAuth', 2)->count();
        $ownerRejectCounts = Owner::where('isAuth', 0)->count();
        return view('admin.auth.owner.accept', compact(
            'owners',
            'ownerPenddingCounts',
            'ownerRejectCounts',
            'ownerAcceptCounts'
        ));
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
    public function edit(string $id)
    {
        $ownerAuth = Owner::with('operatorMessages')->where('id', $id)->first();
        return view('admin.auth.owner.edit', compact('ownerAuth'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Owner $ownerAuth)
    {
        $input = $request->all();
        $ownerAuth->fill($input)->save();
        return back()->with("success", "با موفقیت ویرایش شد");
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

    public function updateAuthOwner(Request $request, Owner $owner)
    {
        $owner->isAuth = $request->status;
        if ($request->status == ACCEPT) {
            $owner->acceptCustomerSms($owner->mobileNumber);
        } else {
            if (file_exists($owner->nationalCardImage))
                unlink($owner->nationalCardImage);

            if (file_exists($owner->nationalFaceImage))
                unlink($owner->nationalFaceImage);

            if (file_exists($owner->profileImage))
                unlink($owner->profileImage);

            if (file_exists($owner->sanaImage))
                unlink($owner->sanaImage);

            if (file_exists($owner->activityLicense))
                unlink($owner->activityLicense);

            $owner->nationalCardImage = null;
            $owner->nationalFaceImage = null;
            $owner->profileImage = null;
            $owner->sanaImage = null;
            $owner->activityLicense = null;
        }
        $owner->save();
        try {
            $operatorAuthOwner = new OperatorOwnerAuthMessage();
            $operatorAuthOwner->owner_id = $owner->id;
            $operatorAuthOwner->user_id = Auth::id();
            $operatorAuthOwner->message = $request->input('operatorMessage');
            $operatorAuthOwner->save();
        } catch (Exception $exception) {
            Log::emergency($exception->getMessage());
        }
        return redirect()->route('ownerAuth.index')->with('success', 'وضعیت با موفقیت ثبت شد');
    }
}
