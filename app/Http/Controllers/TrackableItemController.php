<?php

namespace App\Http\Controllers;

use App\Models\TrackableItems;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackableItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // تاریخ امروز به فرمت دیتابیس شما
        $today = gregorianDateToPersian(date('Y/m/d', time()), '/');
        // فیلتر تب‌ها (گذشته، امروز، آینده)
        $query = TrackableItems::with('user')->where('parent_id', 0);

        switch ($request->status) {
            case 'past':
                $query->where('date', '<', $today)
                    ->where('status', 1)
                    ->orderBy('date', 'desc');
                break;
            case 'archive':
                $query->where('status', 0)
                    ->orderBy('updated_at', 'desc');
                break;
            case 'active':
                $query->where('status', 1)
                    ->orderBy('date', 'desc');
                break;
            case 'today':
                $query->where('date', $today)
                    ->where('status', 1)
                    ->orderByDesc('created_at');
                break;
            case 'all':
                $query->where('status', 1)
                    ->orderByDesc('created_at');
                break;

            case 'future':
                $query->where('date', '>', $today)
                    ->where('status', 1)
                    ->orderBy('date', 'asc');
                break;

            default:
                $query = TrackableItems::with('user')
                    ->where('parent_id', 0)
                    // ->where('status', 1)
                    ->orderByDesc('created_at');
                break;
        }

        $tracks = $query->when($request->mobileNumber !== null, function ($query) use ($request) {
            return $query->where('mobileNumber', $request->mobileNumber);
        })->paginate(20);


        return view('admin.trackable.index', compact('tracks'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.trackable.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $track = new TrackableItems();

        $track->mobileNumber = $request->mobileNumber;
        $track->description = $request->description;
        $track->date = $request->date;
        $track->dateTime = $request->dateTime;
        $track->tracking_code = rand(10000, 99999);
        $track->parent_id = 0;
        $track->user_id = Auth::id();
        $track->status = 1;
        $track->save();

        return back()->with('success', 'آیتم مورد نظر ثبت شد.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(TrackableItems $trackableItem)
    {
        return view('admin.trackable.show', compact('trackableItem'));
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
     * @param  int  $trackableItem
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TrackableItems $trackableItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, TrackableItems $trackableItem)
    {
        $trackableItem->status = 0;
        $trackableItem->result = $request->result;
        $trackableItem->confirm_user_id = Auth::id();
        $trackableItem->save();

        TrackableItems::where('parent_id', $trackableItem->id)->update(['status' => 0]);
        return back()->with('danger', 'تیکت مورد نظر بسته شد.');
    }
}
