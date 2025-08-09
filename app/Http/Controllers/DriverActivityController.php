<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DriverActivityController extends Controller
{
    public function index($version = null)
    {
        $oneMonthAgo = Carbon::now()->subMonth();

        $drivers = Driver::where('version', $version)
            ->whereHas('driverActivities', function ($q) use ($oneMonthAgo) {
                $q->where('created_at', '>=', $oneMonthAgo);
            })
            ->groupBy('drivers.id')
            ->select(['id', 'name', 'lastName', 'authLevel', 'fleet_id', 'nationalCode', 'created_at', 'version', 'mobileNumber', 'status'])
            ->paginate(10);

        // حالا روی collection داخل paginator، makeHidden بزن
        $drivers->getCollection()->transform(function ($driver) {
            return $driver->makeHidden(['countOfPais', 'countOfCalls', 'operatorMessage', 'blockedIp', 'transactionCount', 'ratingDriver', 'fleetTitle']);
        });

        // return $drivers;

        return view('admin.driverActivity.version', compact('drivers', 'version'));
    }
}
