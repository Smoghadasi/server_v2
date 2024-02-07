<?php

namespace App\Http\Controllers;

use App\Models\ContactUs;
use App\Models\Driver;
use App\Models\Load;
use Illuminate\Http\Request;

class ContactUsController extends Controller
{


    // ارسال پیام
    public function sendMessage(Request $request)
    {
        $name = $request->name;
        $lastName = $request->lastName;

        if ($request->role == ROLE_DRIVER) {
            $driver = Driver::where('mobileNumber', $request->mobileNumber)->first();
            if (isset($driver->name)) {
                $name = $driver->name;
                $lastName = $driver->lastName;
            }
        }


        $contactUs = new ContactUs();
        $contactUs->title = $request->title;
        $contactUs->message = $request->message;
        $contactUs->name = $name;
        $contactUs->lastName = $lastName;
        $contactUs->mobileNumber = $request->mobileNumber;
        $contactUs->role = $request->role;
        $contactUs->email = $request->email;
        $contactUs->save();

        if ($contactUs) {
            return ['result' => SUCCESS];
        }

        return ['result' => UN_SUCCESS];
    }

    // ارسال پیام
    public function sendMessageInWeb(Request $request)
    {
        $mobileNumber = $request->mobileNumber;

        $title = isset($request->title) ? $request->title : null;
        $email = isset($request->email) ? $request->email : null;
        $lastName = isset($request->lastName) ? $request->lastName : null;
        $message = $request->message;
        $name = $request->name;


        $contactUs = new ContactUs();
        $contactUs->title = $title;
        $contactUs->message = $message;
        $contactUs->name = $name;
        $contactUs->mobileNumber = $mobileNumber;
        $contactUs->email = $email;
        $contactUs->lastName = $lastName;
        $contactUs->save();

        if ($contactUs) {
            return ['result' => SUCCESS];
        }

        return ['result' => UN_SUCCESS];
    }

    // نمایش پیام ها
    public function messages()
    {
        $messages = ContactUs::orderby('id', 'desc')->paginate(20);
        return view('admin/messages', compact('messages'));
    }

    // نمایش پیام ها رانندگان
    public function driverMessages($mobileNumber)
    {
        $messages = ContactUs::where('role', 'driver')
            ->where('mobileNumber', $mobileNumber)
            ->get();
        if ($messages->isEmpty())
            return response()->json('Empty', 404);
        else
            return response()->json($messages, 200);
    }

    // ارسال امتیاز و نظر از طرف صاحب بار برای باربری
    public function sendScoreAndCommentToLoadFromCustomer(Request $request)
    {
        $load_id = $request->load_id;
        $score = $request->score;
        $comment = $request->comment;

        Load::where('id', $load_id)
            ->update([
                'score' => $score,
                'comment' => $comment
            ]);

        return [
            'result' => 1,
            'message' => 'امتیاز شما ثبت شد'
        ];
    }

    // تغییر وضعیت به خوانده شده
    public function changeMessageStatus(ContactUs $contactUs, Request $request)
    {
        $contactUs->status = true;
        $contactUs->result = strlen($request->result) ? $request->result : "نتیجه ای ثبت نشده!";
        $contactUs->save();

        return back()->with('success', 'وضعیت پیام مورد نظر به خوانده شده تغییر و نتیجه پیگیری ثبت شد.');
    }


    public function removeMessage(ContactUs $contactUs)
    {
        $contactUs->delete();

        return back()->with('success', ' پیام مورد نظر حذف شد.');
    }
}
