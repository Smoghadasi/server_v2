<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\Api\OwnerController;
use App\Http\Controllers\Api\ProvinceCityController;
use App\Http\Controllers\Api\RadioController;
use App\Http\Controllers\Api\ReportController as ApiReportController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\BearingController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\ContactUsController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DataConvertController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\FleetController;
use App\Http\Controllers\LoadController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PackingTypeController;
use App\Models\CargoConvertList;
use App\Http\Controllers\PayController;
use App\Http\Controllers\SOSController;
use App\Http\Controllers\TenderController;
use App\Models\AppVersion;
use App\Models\Bearing;
use App\Models\BlockPhoneNumber;
// use App\Models\ClearText;
use App\Models\Customer;
use App\Models\Dictionary;
use App\Models\Driver;
use App\Models\Equivalent;
use App\Models\FleetlessNumbers;
// use App\Models\Fleet;
use App\Models\Load;
use App\Models\ProvinceCity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::group(['middleware' => 'throttle:60,1'], function () {
    // Route::middleware('auth:api')->get('/user', function (Request $request) {
    //     return $request->user();
    // });

    // درخواست کد فعال سازی
    Route::post('requestActivationCode', [LoginController::class, 'requestActivationCode']);

    // درخواست کد فعال سازی برای صاحبین بار
    Route::post('requestActivationCodeOwner', [LoginController::class, 'requestActivationCodeOwner']);


    // اعتبارسنجی کد فعال سازی برای مشتری
    Route::post('verifyActivationCodeForCustomer', [LoginController::class, 'verifyActivationCodeForCustomer']);

    // اعتبارسنجی کد فعال سازی برای باربری
    Route::post('verifyActivationCodeForBearing', [LoginController::class, 'verifyActivationCodeForBearing']);

    // درخواست کد فعال سازی برای راننده
    Route::post('requestActivationCodeForDriver', [LoginController::class, 'requestActivationCodeForDriver']);

    // اعتبار سنجی کد فعال سازی برای راننده
    Route::post('verifyActivationCodeForDriver', [LoginController::class, 'verifyActivationCodeForDriver']);

    // درخواست لیست استان ها
    Route::post('requestStatesList', [AddressController::class, 'requestStatesList']);
    Route::get('requestStatesList', [AddressController::class, 'requestStatesList']);

    // درخواست لیست شهرهای یک استان خاص
    Route::post('requestCitiesList', [AddressController::class, 'requestCitiesList']);
    Route::get('requestCitiesListOfState/{state_id}', [AddressController::class, 'requestCitiesListOfState']);
    // Route::get('ipAddress/{driver_id}', [DriverController::class, 'ipAddress']);

    Route::post('storeDistanceCalculate', [LoadController::class, 'storeDistanceCalculate']);


    // درخواست ثبت نام باربری
    Route::post('registerBearing', [RegisterController::class, 'registerBearing']);

    Route::get('services', [\App\Http\Controllers\Api\ServiceController::class, 'index'])->name('api.service.index');

    /******************************************************************************************/
    //  مسیرهای مروبوط به بار
    /******************************************************************************************/

    // Route::post('customer/createNewLoad1', [LoadController::class, 'createNewLoad1']);

    // انتخاب راننده برای بار توسط باربری
    Route::post('bearing/selectDriverForLoad', [LoadController::class, 'selectDriverForLoad']);

    // درخواست لیست نوع بارها
    Route::get('customer/requestLoadTypeList', [LoadController::class, 'requestLoadTypeList']);

    Route::get('customer/requestLoadStatus/{load_id}', [LoadController::class, 'requestLoadStatus']);
    Route::get('bearing/requestLoadStatus/{load_id}', [LoadController::class, 'requestLoadStatus']);

    // دریافت قیمت ثبت شده برای حمل بار
    Route::get('bearing/requestLoadPrice/{load_id}', function ($load_id) {
        $load = Load::where('id', $load_id)->select('price')->first();
        if ($load)
            return ['price' => $load->price];
        return ['price' => 0];
    });

    // دریافت قیمت ثبت شده برای حمل بار
    Route::get('bearing/requestLoadPrice/{load_id}', function ($load_id) {
        $load = Load::where('id', $load_id)->select('price')->first();
        if ($load)
            return ['price' => $load->price];
        return ['price' => 0];
    });

    // دریافت کارمزد برای حمل بار
    Route::get('bearing/requestLoadWage/{load_id}', function ($load_id) {
        $load = Load::where('id', $load_id)->select('price', 'numOfTrucks', 'marketing_price')->first();
        if ($load) {

            $cost = ((($load->price) / 100) * 3) + $load->marketing_price;
            if ($load->numOfTrucks > 0)
                $cost = ((($load->price * $load->numOfTrucks) / 100) * 3) + $load->marketing_price;
            return ['cost' => $cost];
        }
        return ['cost' => 0];
    });

    /****************************************************************************************/
    /****************************************************************************************/

    Route::get('bearing/requestLoadsBearing/{bearing_id}', [LoadController::class, 'requestLoadsBearing']);

    // جستجوی شهر
    Route::get('customer/searchCity', [AddressController::class, 'searchCity']);

    // درخواست تمام شهر ها
    Route::get('customer/requestAllCitiesList', [AddressController::class, 'requestAllCitiesList']);
    Route::get('bearing/requestAllCitiesList', [AddressController::class, 'requestAllCitiesList']);
    Route::get('owner/requestAllCitiesList', [AddressController::class, 'requestAllCitiesListOwner']);


    /********************************************************************************************/
    // مشتری
    /********************************************************************************************/


    // ذخیره پیشنهاد
    Route::post('bearing/suggestionPrice', [TenderController::class, 'suggestionPrice']);

    //لیست پیشنهاد های یک بار
    Route::get('bearing/requestSuggestionsOfATender/{load_id}', [TenderController::class, 'requestSuggestionsOfATender']);

    //لیست پیشنهاد های یک بار
    Route::get('customer/requestSuggestionsOfATender/{load_id}', [TenderController::class, 'requestSuggestionsOfATender']);

    // اعلام پایان مناقصه
    Route::post('customer/stopTender', [TenderController::class, 'stopTender']);

    // انتخاب باربری برای بار توسط صاحب بار
    Route::post('customer/selectBearingForLoad', [LoadController::class, 'selectBearingForLoad']);

    // درخواست ثبت نام باربری
    Route::post('customer/registerCustomer', [RegisterController::class, 'registerCustomer']);

    // درخواست لیست ناوگان ها
    Route::get('customer/requestAllFleetsList', [FleetController::class, 'requestAllFleetsList']);

    Route::get('customer/requestAllFleetsLists', [FleetController::class, 'requestAllFleetsLists']);

    // درخواست لیست باربری های که قیمت کمتر را ثبت کرده اند
    Route::get('customer/requestTopBearingListInTender/{load_id}', [TenderController::class, 'requestTopBearingListInTender']);

    /********************************************************************************************/
    // ناوگان
    /********************************************************************************************/

    // درخواست لیست ناوگان های اصلی
    Route::get('customer/requestMainFleetsList', [FleetController::class, 'requestMainFleetsList']);
    Route::get('driver/requestMainFleetsList', [FleetController::class, 'requestMainFleetsList']);

    // درخواست لیست ناوگان های اصلی
    Route::get('customer/requestSubFleetsList/{parent_id}', [FleetController::class, 'requestSubFleetsList']);
    Route::get('driver/requestSubFleetsList/{parent_id}', [FleetController::class, 'requestSubFleetsList']);

    Route::get('driver/requestAllSubFleetsList', [FleetController::class, 'requestAllSubFleetsList']);

    /********************************************************************************************/
    // راننده
    /********************************************************************************************/



    // درخواست تغییر ناوگان راننده
    Route::post('driver/requestChangeFleet', [DriverController::class, 'requestChangeFleet']);

    // تایید حمل بار توسط راننده
    Route::post('driver/confirmLoad', [DriverController::class, 'confirmLoad']);



    // افزودن مسیر
    Route::post('driver/addPath', [DriverController::class, 'addPath']);

    // اعلام تحویل بار
    Route::post('driver/loadDelivery', [LoadController::class, 'loadDelivery']);

    // ثبت نام راننده
    Route::post('driver/registerDriver', [DriverController::class, 'registerDriver']);

    // ثبت قیمت جدید در استعلام بار
    Route::post('driver/storeInquiryToLoad', [LoadController::class, 'storeInquiryToLoad']);

    // درخواست لیست قیمت های استعلام بار راننده
    Route::get('driver/requestInquiriesOfLoad/{load_id}', [LoadController::class, 'requestInquiriesOfLoad']);

    // درخواست لیست درخواست رانندگان و تماس ها
    Route::get('driver/requestInquiriesOfLoadCall/{load_id}', [LoadController::class, 'requestInquiriesOfLoadCall']);

    // درخواست لیست قیمت های استعلام بار برای باربری
    Route::get('bearing/requestInquiriesOfLoad/{load_id}', [LoadController::class, 'requestInquiriesOfLoad']);


    // حذف مسیر پیشفرض راننده
    Route::post('driver/removePath', [DriverController::class, 'removePath']);

    // تغییر وضعیت بار به درحال بارگیری
    Route::post('driver/changeStatusToOnLoading', [LoadController::class, 'changeStatusToOnLoading']);

    // تغییر وضعیت به درحال حمل
    Route::post('driver/changeTheStatusToCarriage', [LoadController::class, 'changeTheStatusToCarriage']);

    // تغییر وضعیت به درحال حمل
    Route::post('driver/changeTheStatusToDischarge', [LoadController::class, 'changeTheStatusToDischarge']);

    // تغییر نوع ناوگان توصط راننده
    Route::post('driver/changeFleet', [FleetController::class, 'changeFleet']);

    // جستجوی بار
    Route::post('driver/searchLoad', [LoadController::class, 'searchLoad']);

    Route::post('score', [LoadController::class, 'score']);


    // مبلغ شهریه
    Route::get('driver/expenseForDriver', function () {
        $pay = new PayController();
        return $pay->expenseForDriver();
    });


    // درخواست امداد راننده
    Route::post('driver/requestSOS', [SOSController::class, 'requestSOS']);

    // ارسال شماره تلفن برای ورود (باربری، صاحب بار، راننده، بازاریاب)
    Route::post('validateActivationCode', [LoginController::class, 'validateActivationCode']);


    /********************************************************************************************/
    // نوتیفیکیشن
    /********************************************************************************************/

    // غیرفعال کردن نوتیفیکیشن
    // Route::post('driver/changeNotificationFunction', [NotificationController::class, 'changeNotificationFunction']);
    // Route::post('bearing/changeNotificationFunction', [NotificationController::class, 'changeNotificationFunction']);
    // Route::post('customer/changeNotificationFunction', [NotificationController::class, 'changeNotificationFunction']);

    // ارسال نوتیفیکیشن برای تمامی رانندگان
    Route::post('driver/sendCustomMessage', [NotificationController::class, 'sendCustomMessage']);


    /********************************************************************************************/
    // درخواست وضعیت کاربران
    /********************************************************************************************/

    Route::get('driver/requestStatus/{id}', function ($id) {
        $driver = Driver::where('id', $id)->first();
        if ($driver)
            return ['result' => $driver->status];
        return ['result' => 0];
    });
    Route::get('bearing/requestStatus/{id}', function ($id) {
        $bearing = Bearing::where('id', $id)->first();
        if ($bearing)
            return ['result' => $bearing->status];
        return ['result' => 0];
    });
    Route::get('customer/requestStatus/{id}', function ($id) {
        $customer = Customer::where('id', $id)->first();
        if ($customer)
            return ['result' => $customer->status];
        return ['result' => 0];
    });

    /********************************************************************************************/
    // ثبت ورژن
    /********************************************************************************************/


    Route::get('driver/getVersion', function () {
        return [
            'version' => AppVersion::orderBy('id', 'desc')->first()->driverVersion,
            'link' => 'https://cafebazaar.ir/app/com.iran_tarabar.driver'
        ];
    });
    Route::get('bearing/getVersion', function () {
        return [
            'version' => AppVersion::orderBy('id', 'desc')->first()->transportationCompanyVersion,
            'link' => 'https://cafebazaar.ir/app/ir.iran_tarabar.transportationCompany'
        ];
    });
    Route::get('customer/getVersion', function () {
        return [
            'version' => AppVersion::orderBy('id', 'desc')->first()->cargoOwnerVersion,
            'link' => 'https://cafebazaar.ir/app/ir.iran_tarabar.user'
        ];
    });

    /********************************************************************************************/
    // باربری
    /********************************************************************************************/

    // درخواست لیست بارها برای باربری
    Route::get('bearing/requestNewLoads/{bearing_id}', [LoadController::class, 'requestNewLoads']);


    // گزارش عدم توافق با راننده
    Route::post('bearing/reportDriver', [DriverController::class, 'reportDriver']);

    // درخواست محاسبه هزنیه انتخاب راننده
    Route::get('bearing/requestSelectDriverCost/{load_id}', [LoadController::class, 'requestSelectDriverCost']);

    // چک کردن باربری انتخاب شده برای بار
    Route::post('bearing/checkSelectedBearingOfLoad', [LoadController::class, 'checkSelectedBearingOfLoad']);


    // درخواست لیست قیمت های استعلام بار
    Route::get('bearing/requestInquiriesOfLoad/{load_id}', [LoadController::class, 'requestInquiriesOfLoad']);

    // تغییر وضعیت بار
    Route::post('bearing/changeLoadStatus', [LoadController::class, 'changeLoadStatus']);

    // Pay for displaying the load information
    Route::post('bearing/payForDisplayingTheLoadInformation', [LoadController::class, 'payForDisplayingTheLoadInformation']);


    // تغییر نوع فوری
    Route::post('bearing/changeUrgentType', [LoadController::class, 'changeUrgentType']);

    // درخواست مبلغ موحودی کیف پول باربری
    Route::get('bearing/requestWalletCharge/{bearing_id}', function ($bearing_id) {
        $bearing = Bearing::where('id', $bearing_id)->select('wallet')->first();
        return ['wallet' => $bearing->wallet];
    });

    /********************************************************************************************/
    // ارتباط با ما
    /********************************************************************************************/


    // ارسال امتیاز و نظر از طرف صاحب بار برای باربری
    Route::post('customer/sendScoreAndCommentToLoadFromCustomer', [ContactUsController::class, 'sendScoreAndCommentToLoadFromCustomer']);

    /**********************************************************************************************/
    // بسته بندی ها
    /**********************************************************************************************/
    //لیست بسته بندی ها
    Route::get('customer/requestPackingTypes', [PackingTypeController::class, 'requestPackingTypes']);

    /***********************************************************************************************/
    // ذخیره توکن فایر ببس
    // Route::post('bearing/saveMyFireBaseToken', [BearingController::class, 'saveMyFireBaseToken']);
    // Route::post('customer/saveMyFireBaseToken', [CustomerController::class, 'saveMyFireBaseToken']);


    // وب سرویس های عمومی
    Route::group(['prefix' => 'public'], function () {

        // درخواست لیست استان ها و شهرها
        Route::get('requestProvinceAndCitiesList', [AddressController::class, 'requestProvinceAndCitiesList']);
    });


    // روت های مشترک بین کاربران
    Route::group(['prefix' => 'common'], function () {

        // درخواست لیست رانندگان بار جهت کنترل ناوگان
        Route::get('requestLoadDriversListForFleetControl/{load}/{userType}/{mobileNumber}', [LoadController::class, 'requestLoadDriversListForFleetControl']);
    });

    Route::get('appReport', function () {

        return [
            'result' => true,
            'data' => [
                'drivers' => Driver::count() + 11561,
                'transportationCompanies' => Bearing::count() + 642,
                'users' => Customer::count() + 990
            ],
            'message' => null
        ];
    });

    Route::get('getTel', function () {
        return response()->json([
            'result' => true,
            'tel' => TELL
        ]);
    });
  });


// کال بک بیمه
Route::post('InsuranceCallBack', function (Request $request) {});

// کال بک احراز هویت کدملی
Route::get('DidarCallBack', function () {
    return response()->json([
        'result' => true,
    ]);
});

Route::post('extractData', [DataConvertController::class, 'extractData']);


Route::get('dictionary', function () {
    return Dictionary::select('original_word_id', 'type', 'equivalentWord')->get();
});
