<?php

namespace App\Http\Controllers;

use App\Models\Marketer;
use Illuminate\Http\Request;

class MarketerController extends Controller
{
    // لیست بازاریابها
    public function Marketers()
    {

        $marketers = Marketer::get();
        return view('admin/Marketers', compact('marketers'));
    }

    // فرم افزودن بازاریاب
    public function addNewMarketersForm($message = '')
    {
        return view('admin/addNewMarketersForm', compact('message'));
    }

    // افزودن بازاریاب جدید
    public function addNewMarketer(Request $request)
    {
        $marketer = new Marketer();
        $marketer->name = $request->name;
        $marketer->lastName = $request->lastName;
        $marketer->nationalCode = $request->nationalCode;
        $marketer->mobileNumber = $request->mobileNumber;
        $marketer->phoneNumber = $request->phoneNumber;
        $marketer->emergencyPhoneNumber = $request->emergencyPhoneNumber;
        $marketer->fatherName = $request->fatherName;
        $marketer->marketerCode = 1000 + Marketer::count();
        $marketer->address = $request->address;
        $marketer->pic = $this->savePicOfFleet($request->pic);
        $marketer->save();

        return $this->addNewMarketersForm('بازاریاب جدید ثبت شد');

    }

    // ذخیره عکس
    private function savePicOfFleet($picture)
    {
        $picName = 'user.png';
        if (strlen($picture)) {
            $fileType = $picture->guessClientExtension();
            if ($picture->isValid() && ($fileType == 'jpg' || $fileType == 'jpeg' || $fileType == 'gif' || $fileType == 'png' || $fileType == 'bmp')) {
                $picName = sha1(time()) . "." . $fileType;
                $picture->move('pictures/marketers', $picName);
            }
        }
        return 'pictures/marketers/' . $picName;
    }

        // چک کردن وجود کد بازاریاب
    public static function checkMarketerCodeIsExist($marketerCode)
    {
        return Marketer::where('marketerCode', $marketerCode)->count();
    }

}
