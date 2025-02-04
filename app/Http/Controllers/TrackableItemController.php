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
        if ($request->has('parentId')) {
            $tracks = TrackableItems::where('parent_id', $request->parentId)->with('user')->paginate(100);
        } elseif ($request->has('status')) {
            $tracks = TrackableItems::with('user')->where('status', $request->status)->where('parent_id', 0)->paginate(40);
        } else {
            $tracks = TrackableItems::with('childrenRecursive', 'user')->where('parent_id', 0)
                ->orderByDesc('status')
                ->paginate(40);
        }
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

        if ($request->has('parent_id')) {
            $parentTrack = TrackableItems::findOrFail($request->parent_id);
            $track->parent_id = $request->parent_id;
            $track->user_id = Auth::id();
            $track->mobileNumber = $parentTrack->mobileNumber;
            $track->tracking_code = $parentTrack->tracking_code;
            $track->description = $parentTrack->description;
        } else {
            $track->parent_id = 0;
            $track->mobileNumber = $request->mobileNumber;
            $track->tracking_code = rand(10000, 99999);
            $track->description = $request->description;
        }

        $track->date = $request->date . " " . $request->time;
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
        $trackableItem->save();
        TrackableItems::where('parent_id', $trackableItem->id)->update(['status' => 0]);
        return back()->with('danger', 'تیکت مورد نظر بسته شد.');
    }
}
