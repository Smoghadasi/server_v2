<?php

namespace App\Http\Controllers;

use App\Models\Bearing;
use App\Models\ContactReportWithCargoOwner;
use App\Models\ContactReportWithCargoOwnerResult;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\DriverCall;
use App\Models\ResultOfContactingWithDriver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OperatorContactingController extends Controller
{
    /**************************************************************************************************************/
    // ثبت گزارش تماس با صاحبان بار و باربری ها برای جذب و پشتیبانی
    public function contactReportWithCargoOwners($mobileNumber = null)
    {
        if ($mobileNumber != null) {
            $contactReportWithCargoOwner = ContactReportWithCargoOwner::where('mobileNumber', $mobileNumber)->first();
            return view('admin.contact.contactReportWithCargoOwnersResult', compact('contactReportWithCargoOwner'));
        }

        $countOfCals = ContactReportWithCargoOwnerResult::where([
            ['operator_id', auth()->id()],
            ['created_at', '>', date('Y-m-d', time()) . ' 00:00:00']
        ])->count();

        $contactReportWithCargoOwners = ContactReportWithCargoOwner::orderby('id', 'desc')->paginate(20);

        return view('admin.contact.contactReportWithCargoOwners', compact('contactReportWithCargoOwners', 'countOfCals'));
    }

    // ذخیره شماره تلفن صاحب بار
    public function soreNewMobileNumberOfCargoOwner(Request $request)
    {

        try {
            $contactReportWithCargoOwner = ContactReportWithCargoOwner::where('mobileNumber', $request->mobileNumber)->first();
            if (!isset($contactReportWithCargoOwner->id)) {
                $contactReportWithCargoOwner = new ContactReportWithCargoOwner();
                $contactReportWithCargoOwner->operator_id = auth()->id();
                $contactReportWithCargoOwner->mobileNumber = $request->mobileNumber;

                $nameAndLastName = '';
                $user = Customer::where('mobileNumber', $request->mobileNumber)->first();
                if (!isset($user->id))
                    $user = Bearing::where('mobileNumber', $request->mobileNumber)->first();
                if (isset($user->name) && isset($user->lastName))
                    $nameAndLastName = $user->name . ' ' . $user->lastName;

                $contactReportWithCargoOwner->nameAndLastName = $nameAndLastName;
                $contactReportWithCargoOwner->save();
            }

            return redirect('admin/contactReportWithCargoOwners/' . $contactReportWithCargoOwner->mobileNumber);

        } catch (\Exception $exception) {
            Log::emergency("-------------------------- ذخیره شماره تلفن صاحب بار ------------------------------");
            Log::emergency($exception->getMessage());
            Log::emergency("-----------------------------------------------------------------------------");
        }

        return back()->with('danger', 'خطایی رخ داده دوباره تلاش کنید');
    }

    // ذخیره نام و نام خانوادگی صاحب بار
    public function storeContactCargoOwnerNameAndLastname(ContactReportWithCargoOwner $contactReportWithCargoOwner, Request $request)
    {
        $contactReportWithCargoOwner->nameAndLastName = $request->nameAndLastName;
        $contactReportWithCargoOwner->save();

        return back()->with('success', 'نام و نام خانوادگی ثبت شد');
    }

    // ذخیره نتیجه تماس
    public function storeContactReportWithCargoOwnerResult(Request $request)
    {
        $contactReportWithCargoOwnerResult = new ContactReportWithCargoOwnerResult();
        $contactReportWithCargoOwnerResult->result = $request->result;
        $contactReportWithCargoOwnerResult->contact_report_with_cargo_owner_id = $request->contactReportWithCargoOwnerId;
        $contactReportWithCargoOwnerResult->operator_id = auth()->id();
        $contactReportWithCargoOwnerResult->save();

        return back()->with('success', 'نتیجه مورد نظر ثبت شد.');
    }

    // حذف تماس
    public function deleteContactReportWithCargoOwners(ContactReportWithCargoOwner $contactReportWithCargoOwner)
    {
        $contactReportWithCargoOwner->delete();
        return back()->with('success', 'حذف انجام شد.');
    }

    /*********************************************************************************************************/

    // تماس بار رانندگان
    public function contactingWithDrivers()
    {

        $resultOfContactingWithDriver = ResultOfContactingWithDriver::where([
            ['operator_id', auth()->id()],
            ['created_at', '>', date('Y-m-d', time()) . ' 00:00:00']
        ])->count();

        $drivers = Driver::select('id', 'name', 'lastName', 'mobileNumber', 'fleet_id', 'version', 'created_at')
            ->orderBy('id', 'desc')
            ->paginate(20);
        return view('admin.contact.contactingWithDrivers', compact('drivers', 'resultOfContactingWithDriver'));
    }

    // نتیجه تماس ها
    public function contactingWithDriverResult(Driver $driver)
    {
        return view('admin.contact.contactingWithDriverResult', compact('driver'));
    }

    // ذخیره نتیجه تماس با راننده
    public function storeContactReportWithDriver(Request $request)
    {
        $resultOfContactingWithDriver = new ResultOfContactingWithDriver();
        $resultOfContactingWithDriver->result = $request->result;
        $resultOfContactingWithDriver->operator_id = auth()->id();
        $resultOfContactingWithDriver->driver_id = $request->driver_id;
        $resultOfContactingWithDriver->save();

        return back()->with('success', 'نتیجه تماس ثبت شد');
    }
}
