<?php

namespace App\Http\Controllers;

use App\Models\BlockPhoneNumber;
use App\Models\FirstLoad;
use Illuminate\Http\Request;

class FirstLoadController extends Controller
{
    public function index()
    {
        $firstLoads = FirstLoad::orderBy('status', 'asc')
            ->where('status', 0)
            ->paginate(20);
        return view('admin.firstLoad.index', compact('firstLoads'));
    }

    public function update(Request $request, FirstLoad $firstLoad)
    {
        if ($request->status == 'accept') {
            $firstLoad->status = 1;
        }
        if ($request->status == 'block') {
            $firstLoad->status = 0;
            $blockNumber = new BlockPhoneNumber();
            $blockNumber->phoneNumber = $firstLoad->mobileNumberForCoordination;
            $blockNumber->name = 'بار اولیه';
            $blockNumber->description = "کلاهبرداری";
            $blockNumber->type = "both";
            $blockNumber->save();
        }
        $firstLoad->save();
        return back()->with("success", "وضعیت با موفقیت تغییر کرد");

    }
}
