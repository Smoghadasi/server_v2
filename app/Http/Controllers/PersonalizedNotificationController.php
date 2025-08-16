<?php

namespace App\Http\Controllers;

use App\Jobs\SendPushNotificationPersonalizeJob;
use App\Models\Driver;
use App\Models\Owner;
use App\Models\PersonalizedNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PersonalizedNotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $personalizedNotifications = PersonalizedNotification::orderByDesc('created_at')->paginate(30);
        return view('admin.personalizeNotification.index', compact('personalizedNotifications'));
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
        $personalizedNotification = new PersonalizedNotification();
        $personalizedNotification->type = $request->type;
        $personalizedNotification->version = $request->version;
        $personalizedNotification->title = $request->title;
        $personalizedNotification->body = $request->body;
        $personalizedNotification->user_id = Auth::id();
        $personalizedNotification->status = 2;
        $personalizedNotification->save();
        return back()->with('success', 'آیتم مورد نظر ثبت شد.');
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
    public function update(Request $request, PersonalizedNotification $personalizedNotification)
    {
        $personalizedNotification->type = $request->type;
        $personalizedNotification->version = $request->version;
        $personalizedNotification->title = $request->title;
        $personalizedNotification->body = $request->body;
        $personalizedNotification->save();
        return back()->with('success', 'آیتم مورد نظر ویرایش شد.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(PersonalizedNotification $personalizedNotification)
    {
        $personalizedNotification->delete();
        return back()->with('danger', 'آیتم مورد نظر حذف شد.');
    }

    public function requestForApproval(PersonalizedNotification $personalizedNotification)
    {
        $title = $personalizedNotification->title;
        $body  = $personalizedNotification->body;

        if ($personalizedNotification->type == 'driver') {
            $oneMonthAgo = Carbon::now()->subMonth();

            $tokens = Driver::whereNotNull('FCM_token')
                ->whereHas('driverActivities', function ($q) use ($oneMonthAgo) {
                    $q->where('created_at', '>=', $oneMonthAgo);
                })
                ->where('version', $personalizedNotification->version)
                ->pluck('FCM_token')
                ->toArray();

            $chunks = array_chunk($tokens, 100); // چون FCM حداکثر 250 تا پشتیبانی می‌کنه

            foreach ($chunks as $chunk) {
                dispatch(new SendPushNotificationPersonalizeJob($chunk, $title, $body));
            }
            $personalizedNotification->status = 1;
            $personalizedNotification->save();
            return back()->with('success', 'اعلان مورد نظر ارسال شد.');
        }
        if ($personalizedNotification->type == 'owner') {
            $tokens = Owner::whereNotNull('FCM_token')
                ->where('version', $personalizedNotification->version)
                // ->whereIn('id', ['81'])
                ->pluck('FCM_token')
                ->toArray();

            $chunks = array_chunk($tokens, 250); // چون FCM حداکثر 250 تا پشتیبانی می‌کنه
            foreach ($chunks as $chunk) {
                dispatch(new SendPushNotificationPersonalizeJob($chunk, $title, $body));
            }
            $personalizedNotification->status = 1;
            $personalizedNotification->save();
            return back()->with('success', 'اعلان مورد نظر ارسال شد.');
        }
    }
}
