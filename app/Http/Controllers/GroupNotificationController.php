<?php

namespace App\Http\Controllers;

use App\Models\Fleet;
use App\Models\GroupNotification;
use App\Models\ManualNotificationRecipient;
use App\Models\ProvinceCity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class GroupNotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $groups = GroupNotification::withCount(['manualNotificationRecipients'])->get();
        return view('admin.manualNotification.group.index', compact('groups'));
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
        $group = new GroupNotification();
        $group->title = $request->title;
        $group->groupType = $request->groupType;
        $group->description = $request->description;
        $group->save();

        return back()->with('success', 'گروه مورد نظر ثبت شد');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(GroupNotification $groupNotification)
    {
        $manualNotifications = ManualNotificationRecipient::with(['userable' => function ($query) {
            $query->select(['id', 'name', 'lastName', 'mobileNumber']);
        }])
            ->where('group_id', $groupNotification->id)
            ->paginate(20);
        $provinces = Cache::remember('province', now()->addMinutes(60), function () {
            return ProvinceCity::where('parent_id', 0)->get();
        });

        $fleets = Cache::remember('fleets', now()->addMinutes(60), function () {
            return Fleet::where('parent_id', '>', 0)->orderBy('parent_id', 'asc')->get();
        });
        return view('admin.manualNotification.index', compact('manualNotifications', 'groupNotification', 'provinces', 'fleets'));
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
    public function update(Request $request, GroupNotification $groupNotification)
    {
        $groupNotification->title = $request->title;
        $groupNotification->description = $request->description;
        $groupNotification->save();
        return back()->with('success', 'گروه مورد نظر ویرایش شد');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(GroupNotification $groupNotification)
    {
        ManualNotificationRecipient::where('group_id', $groupNotification->id)->delete();
        $groupNotification->delete();
        return back()->with('danger', 'گروه مورد نظر حذف شد');
    }
}
