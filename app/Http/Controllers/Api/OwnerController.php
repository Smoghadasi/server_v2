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


        // $token = $user->createToken('myapptoken')->plainTextToken;

        $response = [
            'data' => $user->id,
            'message' => 'با موفقیت ذخیره شد',
        ];

        return response()->json($response, 201);
    }

    public function authOwner(Request $request, Owner $owner)
    {
        try {
            $validator = Validator::make($request->all(), [
                'address' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors());
            } else {
                $owner->address = $request->address;
                $owner->postalCode = $request->postalCode;
                if ($request->hasfile('nationalCardImage')) {
                    $file = $request->file('nationalCardImage');
                    $extenstion = $file->getClientOriginalExtension();
                    $filename = time() . '.' . $extenstion;
                    if (file_exists($owner->nationalCardImage))
                        unlink($owner->nationalCardImage);
                    $file->move('images/owners/nationalCardImage', $filename);
                    $owner->nationalCardImage = 'images/owners/nationalCardImage/' . $filename;
                }
                if ($request->hasfile('nationalFaceImage')) {
                    $file = $request->file('nationalFaceImage');
                    $extenstion = $file->getClientOriginalExtension();
                    $filename = time() . '.' . $extenstion;
                    if (file_exists($owner->nationalFaceImage))
                        unlink($owner->nationalFaceImage);
                    $file->move('images/owners/nationalFaceImage', $filename);
                    $owner->nationalFaceImage = 'images/owners/nationalFaceImage/' . $filename;
                }
                if ($request->hasfile('sanaImage')) {
                    $file = $request->file('sanaImage');
                    $extenstion = $file->getClientOriginalExtension();
                    $filename = time() . '.' . $extenstion;
                    if (file_exists($owner->sanaImage))
                        unlink($owner->sanaImage);
                    $file->move('images/owners/sanaImage', $filename);
                    $owner->sanaImage = 'images/owners/sanaImage/' . $filename;
                }
                $owner->isAuth = 2;
                $owner->isOwner = 1;

                $owner->save();
                $response = [
                    'message' => 'با موفقیت ذخیره شد',
                ];
            }
            return response()->json($response, 201);
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }


    public function authBearing(Request $request, Owner $owner)
    {
        try {
            $validator = Validator::make($request->all(), [
                'companyName' => 'required',
                'companyID' => 'required|min:9|max:12|unique:owners,companyID',
                'address' => 'required|string',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors());
            } else {
                $owner->companyName = $request->companyName;
                $owner->companyID = $request->companyID;
                $owner->address = $request->address;
                $owner->postalCode = $request->postalCode;
                if ($request->hasfile('activityLicense')) {
                    $file = $request->file('activityLicense');
                    $extenstion = $file->getClientOriginalExtension();
                    $filename = time() . '.' . $extenstion;
                    if (file_exists($owner->activityLicense))
                        unlink($owner->activityLicense);
                    $file->move('images/owners/activityLicense', $filename);
                    $owner->activityLicense = 'images/owners/activityLicense/' . $filename;
                }
                $owner->isAuth = 2;
                $owner->isOwner = 2;
                $owner->save();
            }
            return response()->json(['message' => 'با موفقیت ذخیره شد'], 201);
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    public function profileImage(Request $request, Owner $owner)
    {
        try {
            if ($request->hasfile('profileImage')) {
                $file = $request->file('profileImage');
                $extenstion = $file->getClientOriginalExtension();
                $filename = time() . '.' . $extenstion;
                if (file_exists($owner->profileImage))
                    unlink($owner->profileImage);
                $file->move('images/owners/profileImage', $filename);
                $owner->profileImage = 'images/owners/profileImage/' . $filename;
                $owner->save();
                return response()->json(['message' => 'با موفقیت ذخیره شد'], 201);
            }
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    // درخواست اطلاعات باربری
    public function profile(Owner $owner)
    {
        return [
            'result' => SUCCESS,
            'data' => $owner
        ];
    }
}
