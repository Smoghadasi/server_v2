<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\DriverActivity;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DriverActivityController extends Controller
{
    public function index($version = null)
    {

        $oneMonthAgo = Carbon::now()->subMonth();

        $driverActivities = DriverActivity::where('created_at', '>=', $oneMonthAgo)
            ->whereHas('driver', function ($q) use ($version) {
                $q->where('version', $version);
            })
            ->selectRaw('driver_id, MAX(created_at) as last_activity_at')
            ->groupBy('driver_id')
            ->with(['driver' => function ($q) {
                $q->select(['id', 'name', 'lastName', 'authLevel', 'fleet_id', 'nationalCode', 'created_at', 'version', 'mobileNumber', 'status']);
            }])
            ->paginate(10);

        // return $driverActivities;


        return view('admin.driverActivity.version', compact('driverActivities', 'version'));
    }
}
