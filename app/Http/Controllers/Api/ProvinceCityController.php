<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProvinceCity;
use Exception;
use Illuminate\Http\Request;

class ProvinceCityController extends Controller
{
    public function index()
    {
        try {
            $provinceCities = ProvinceCity::where('parent_id', '!=', 0)->get();
            return response()->json($provinceCities, 200);
        } catch (Exception $exception) {
            //
        }
    }
}
