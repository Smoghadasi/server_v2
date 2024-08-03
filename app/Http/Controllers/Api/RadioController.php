<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Radio;
use Illuminate\Http\Request;

class RadioController extends Controller
{
    public function index()
    {
        $radios = Radio::all();
        return response()->json($radios, 200);
    }
}
