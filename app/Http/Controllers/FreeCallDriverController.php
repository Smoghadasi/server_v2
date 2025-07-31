<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\Request;
use App\Models\FreeCallDriver;

class FreeCallDriverController extends Controller
{
    public function index()
    {
        $freeCallDrivers = FreeCallDriver::orderByDesc('created_at')->paginate(20);
        return view('admin.freeCallDriver.index', compact('freeCallDrivers'));
    }
}
