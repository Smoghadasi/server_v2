<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\OperatorOwnerAuthMessage;
use App\Models\Owner;
use App\Models\OwnerMobile;
use App\Models\ProvinceCity;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $owners = Owner::where('isAuth', 2)->orderBy('auth_at', 'asc')->paginate(10);
        $ownerPenddingCounts = Owner::where('isAuth', 2)->count();
        $ownerRejectCounts = Owner::where('isAuth', 0)->count();
        $ownerAcceptCounts = Owner::where('isAuth', 1)->count();
        $ownerRejectedCounts = Owner::where('isRejected', 1)->count();

        return view('admin.auth.owner.index', compact(
            'owners',
            'ownerPenddingCounts',
            'ownerRejectCounts',
            'ownerAcceptCounts',
            'ownerRejectedCounts'
        ));
    }

    public function ownerReject()
    {
        $owners = Owner::where('isAuth', 0)->paginate(10);
        $ownerPenddingCounts = Owner::where('isAuth', 2)->count();
        $ownerRejectCounts = Owner::where('isAuth', 0)->count();
        $ownerAcceptCounts = Owner::where('isAuth', 1)->count();
        $ownerRejectedCounts = Owner::where('isRejected', 1)->count();

        return view('admin.auth.owner.reject', compact(
            'owners',
            'ownerPenddingCounts',
            'ownerRejectCounts',
            'ownerAcceptCounts',
            'ownerRejectedCounts'
        ));
    }

    public function ownerRejected()
    {
        $owners = Owner::where('isRejected', 1)
            ->orderByDesc('updated_at')
            ->paginate(10);

        $ownerPenddingCounts = Owner::where('isAuth', 2)->count();
        $ownerRejectCounts = Owner::where('isAuth', 0)->count();
        $ownerAcceptCounts = Owner::where('isAuth', 1)->count();
        $ownerRejectedCounts = Owner::where('isRejected', 1)->count();

        return view('admin.auth.owner.reject', compact(
            'owners',
            'ownerPenddingCounts',
            'ownerRejectCounts',
            'ownerAcceptCounts',
            'ownerRejectedCounts'
        ));
    }

    public function ownerAccept()
    {
        $owners = Owner::where('isAuth', 1)
            ->orderByDesc('updated_at')
            ->paginate(10);

        $ownerAcceptCounts = Owner::where('isAuth', 1)->count();
        $ownerPenddingCounts = Owner::where('isAuth', 2)->count();
        $ownerRejectCounts = Owner::where('isAuth', 0)->count();
        $ownerRejectedCounts = Owner::where('isRejected', 1)->count();

        return view('admin.auth.owner.accept', compact(
            'owners',
            'ownerPenddingCounts',
            'ownerRejectCounts',
            'ownerAcceptCounts',
            'ownerRejectedCounts'
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
        $ownerAuth = Owner::with('operatorMessages', 'ownerMobiles')->whereId($id)->first();
        // return $ownerAuth;
        $provinces = ProvinceCity::where('parent_id', '=', 0)->get();

        return view('admin.auth.owner.edit', compact('ownerAuth', 'provinces'));
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Owner $ownerAuth)
    {
        if ($ownerAuth->isAccepted == 0 && $request->isAccepted == 1) {
            $ownerAuth->sku = $this->generateSKU();
            $ownerAuth->save();

            try {
                // ارسال پیامک برای صاحب بار تایید شده
                $sms = new Owner();
                $sms->acceptOwnerSmsIr($ownerAuth->mobileNumber, $ownerAuth->name, $ownerAuth->lastName);
            } catch (\Throwable $th) {
                //throw $th;
            }
        }

        // فیلدهای اصلی OwnerAuth
        $ownerAuth->name         = $request->input('name');
        $ownerAuth->lastName     = $request->input('lastName');
        $ownerAuth->nationalCode = $request->input('nationalCode');
        $ownerAuth->mobileNumber = $request->input('mobileNumber');
        $ownerAuth->isOwner      = $request->input('isOwner');
        $ownerAuth->isAccepted   = $request->input('isAccepted');
        $ownerAuth->isLimitLoad  = $request->input('isLimitLoad');
        $ownerAuth->address      = $request->input('address');
        $ownerAuth->postalCode   = $request->input('postalCode');
        $ownerAuth->province_id  = $request->input('province_id');
        $ownerAuth->description  = $request->input('description');
        $ownerAuth->companyName  = $request->input('companyName');
        $ownerAuth->companyID    = $request->input('companyID');
        $ownerAuth->notification = $request->input('notification');
        $ownerAuth->sms          = $request->input('sms');

        // ذخیره تغییرات
        $ownerAuth->save();

        if ($request->file('sanaImage'))
            $ownerAuth->sanaImage = $this->storePicOfOwner($request->file('sanaImage'), "sanaImage", $ownerAuth);

        if ($request->file('nationalCardImage'))
            $ownerAuth->nationalCardImage = $this->storePicOfOwner($request->file('nationalCardImage'), "nationalCardImage", $ownerAuth);

        if ($request->file('nationalFaceImage'))
            $ownerAuth->nationalFaceImage = $this->storePicOfOwner($request->file('nationalFaceImage'), "nationalFaceImage", $ownerAuth);

        $ownerAuth->save();

        // حذف شماره‌های قبلی
        $ownerAuth->ownerMobiles()->delete();
        // ثبت شماره‌های جدید
        if ($request->mobileNumbers) {
            foreach ($request->mobileNumbers as $number) {
                if (!empty($number)) {
                    $ownerAuth->ownerMobiles()->create([
                        'mobileNumber' => $number,
                    ]);
                }
            }
        }

        return back()->with("success", "با موفقیت ویرایش شد");
    }

    // ذخیره عکس کابر
    private function storePicOfOwner($picture, $type, $owner)
    {
        $picName = $type . '_' . time() . $owner->id . ".jpg";
        if (strlen($picture)) {
            $fileType = $picture->guessClientExtension();
            if ($picture->isValid() && ($fileType == 'jpg' || $fileType == 'jpeg' || $fileType == 'gif' || $fileType == 'png' || $fileType == 'bmp')) {
                $picName = $type . '_' . time() . $owner->id . "." . $fileType;
                $picture->move('images/owners/', $picName);
            }
        }
        return 'images/owners/' . $picName;
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
            $owner->isRejected = 0;
            if (SMS_PANEL == 'SMSIR') {
                $owner->acceptCustomerSmsIr($owner->mobileNumber);
            } else {
                $owner->acceptCustomerSms($owner->mobileNumber);
            }
        } else {
            $owner->isRejected = 1;
            if (SMS_PANEL == 'SMSIR') {
                $owner->rejectCustomerSmsIr($owner->mobileNumber);
            } else {
                $owner->rejectCustomerSms($owner->mobileNumber);
            }

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
