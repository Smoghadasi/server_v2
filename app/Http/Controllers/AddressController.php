<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\CityOwner;
use App\Models\ProvinceCity;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PHPUnit\Exception;

class AddressController extends Controller
{

    // درخواست لیست استان ها
    public function requestStatesList()
    {
        return [
            'states' => State::all()
        ];
    }

    public function requestCitiesList(Request $request)
    {
        return [
            'selectedLoadingCitiesIds' => ProvinceCity::where('parent_id', $request->selectedLoadingCitiesIds)->get(),
            'selectedDischargeCitiesIds' => ProvinceCity::where('parent_id', $request->selectedDischargeCitiesIds)->get(),
        ];
    }

    public function requestCitiesListOfState($state_id)
    {

        return [
            'cities' => City::where('state_id', $state_id)->get()
        ];
    }

    public function requestAllCitiesList()
    {
        $cities = City::select('id', 'name', 'state', 'latitude', 'longitude', 'centerOfProvince')
            ->orderby('centerOfProvince', 'desc')
            ->get();

        return [
            'cities' => $cities
        ];
    }

    public function requestAllCitiesListOwner()
    {
        $cities = CityOwner::select('id', 'name', 'state', 'latitude', 'longitude', 'centerOfProvince')
            ->orderby('centerOfProvince', 'desc')
            ->get();

        return [
            'cities' => $cities
        ];
    }

    //جستجوی شهر
    public function searchCity(Request $request)
    {

        $word = $request->word;
        if (strlen($word) > 0) {

            $cities = City::where('name', 'like', $word . '%')->get();

            if (count($cities) > 0) {
                return [
                    'result' => SUCCESS,
                    'cities' => $cities
                ];
            }

            return [
                'result' => UN_SUCCESS,
                'message' => 'شهر مورد نظر پیدا نشد!'
            ];
        }
        return [
            'result' => UN_SUCCESS,
            'message' => 'شهر مورد نظر پیدا نشد!'
        ];
    }

    public static function requestCityId($cityName)
    {
        $city = City::where('name', $cityName)->first();
        if ($city)
            return $city->id;
        return 0;
    }

    public static function geCityName($city_id)
    {
        $city = ProvinceCity::where('id', $city_id)->first();
        return $city->name ?? 'انتخاب نشده';
    }

    public static function geStateName($state_id)
    {
        $state = ProvinceCity::where('parent_id', 0)->where('id', $state_id)->first();
        return $state->name ?? 'انتخاب نشده';
    }

    public static function geStateIdFromCityId($city_id)
    {
        try {
            $city = ProvinceCity::where('id', $city_id)->first();
            return $city->parent_id;
        } catch (\Exception $exception) {
            Log::emergency("------------------------------------------------------------------------------");
            Log::emergency("AddressController : geStateIdFromCityId");
            Log::emergency($exception->getMessage());
            Log::emergency("------------------------------------------------------------------------------");
        }

        return 0;
    }

    public static function geStateNameFromCityId($city_id)
    {
        try {
            $city = ProvinceCity::where('id', $city_id)->first();
            return $city->state;
        } catch (\Exception $exception) {
        }
        return 'پیدا نشد';
    }

    // دریافت موقعیت جغرافیایی
    public static function getLatLong($city_id)
    {
        try {
            $city = ProvinceCity::find($city_id);
            if (isset($city->id))
                return [
                    'latitude' => $city->latitude,
                    'longitude' => $city->longitude
                ];
        } catch (Exception $exception) {
        }
        return [
            'latitude' => 0,
            'longitude' => 0
        ];
    }

    // درخواست لیست استان ها و شهرها
    public function requestProvinceAndCitiesList()
    {
        try {
            $provinces = ProvinceCity::where('parent_id', 0)->select('id', 'name')->get();
            // $cities = ProvinceCity::where('parent_id', '!=', 0)->select('id', 'name')->get();

            return [
                'result' => true,
                'data' => [
                    'provinces' => $provinces,
                    // 'cities' => $cities
                ],
                'message' => null
            ];
        } catch (\Exception $exception) {
        }

        return [
            'result' => false,
            'data' => null,
            'message' => 'مشکلی در دریافت لیست استان ها و شهرها به وجود آمده دوباره تلاش کنید'
        ];
    }

    // شهرها و استان ها
    public function provincesAndCities()
    {
        $provinces = State::orderBy('priority', 'asc')->get();
        return view('admin.provincesAndCities', compact('provinces'));
    }

    // لیست شهرهای استان
    public function provinceCitiesList($province_id)
    {
        $province = State::find($province_id);
        $cities = City::where('state_id', $province_id)->orderby('centerOfProvince', 'desc')->get();

        return view('admin.provinceCitiesList', compact('province', 'cities'));
    }

    // ثبت شهر جدید
    public function addNewCity(Request $request, State $state)
    {
        try {
            $city = new City();
            $city->latitude = $request->latitude;
            $city->longitude = $request->longitude;
            $city->name = $request->name;
            $city->state = $state->name;
            $city->state_id = $state->id;
            $city->save();

            return back()->with('success', 'شهر ' . $city->name . ' به استان ' . $state->name . ' اضافه شد.');
        } catch (\Exception $exception) {
        }
        return back()->with('danger', 'خطا در ثبت شهر جدید');
    }

    public function updateCity(Request $request, string $id)
    {
        $city = City::findOrFail($id);
        $city->name = $request->name;
        $city->save();
        return back()->with('success', 'شهر ' . $city->name . ' ویرایش.');

    }

    // ثبت شهر جدید
    public function removeCity(City $city)
    {
        try {
            $city->delete();
            return back()->with('success', 'شهر مورد نظر حذف شد.');
        } catch (\Exception $exception) {
        }

        return back()->with('danger', 'خطا در حذف شهر جدید');
    }

    public function centerOfProvince(City $city)
    {
        try {

            City::where('state_id', $city->state_id)->update([
                'centerOfProvince' => false
            ]);
            $city->centerOfProvince = true;
            $city->save();

            return back()->with('success', 'مرکز استان انتخاب شد');
        } catch (\Exception $exception) {
        }
        return back()->with('danger', 'خطا در انتخاب مرکز استان');
    }
}
