<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Owner;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class OwnerController extends Controller
{
    public function register(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string',
            'lastName' => 'required|string',
            'mobileNumber' => 'required|string|unique:owners,mobileNumber',
            'nationalCode' => 'required|string',
        ]);

        $user = Owner::create([
            'name' => $fields['name'],
            'lastName' => $fields['lastName'],
            'mobileNumber' => $fields['mobileNumber'],
            'nationalCode' => $fields['nationalCode'],
        ]);


        $token = $user->createToken('myapptoken')->plainTextToken;

        $response = [
            'data' => $user,
            'token' => $token
        ];

        return response()->json($response, 201);
    }
}
