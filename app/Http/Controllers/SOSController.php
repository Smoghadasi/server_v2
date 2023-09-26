<?php

namespace App\Http\Controllers;

use App\Models\SOS;
use Illuminate\Http\Request;

class SOSController extends Controller
{
    // درخواست امداد راننده
    public function requestSOS(Request $request)
    {
        $driver_id = $request->driver_id;
        $latitude = $request->latitude;
        $longitude = $request->longitude;

        $sos = new SOS();
        $sos->driver_id = $driver_id;
        $sos->latitude = $latitude;
        $sos->longitude = $longitude;
        $sos->requestDate = DateController::getDate();
        $sos->save();

        if ($sos)
            return ['result' => 1];

        return ['result' => 0];

    }

    // لیست درخواست های امداد
    public function SOSList($status)
    {

        $sosLists = SOS::where('s_o_s.status', $status)
            ->join('drivers', 'drivers.id', 's_o_s.driver_id')
            ->select('s_o_s.id', 's_o_s.requestDate', 'drivers.name', 'drivers.lastName', 'drivers.mobileNumber')
            ->orderby('s_o_s.id', 'desc')
            ->paginate(50);

        return view('admin.driversSOS', compact('sosLists'));
    }

    // اطلاعات درخواست امداد
    public function driverSOSInfo($id)
    {
        $sosInfo = SOS::find($id);
        return view('admin.driverSOSInfo', compact('sosInfo'));
    }

    public function removeSOS(Request $request)
    {
        if (isset($request->sos_id))
            SOS::whereIn('id', $request->sos_id)->delete();
        return redirect()->back();
    }
}
