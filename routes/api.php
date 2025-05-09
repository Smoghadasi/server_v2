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
// use App\Models\ClearText;
use App\Models\Customer;
use App\Models\Dictionary;
use App\Models\Driver;
use App\Models\Load;
use Illuminate\Http\Request;
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
    Route::get('ipAddress/{driver_id}', [DriverController::class, 'ipAddress']);

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

    // درخواست اطلاعات پروفایل رانننده
    Route::get('driver/requestProfileInfo/{driver_id}', [DriverController::class, 'requestProfileInfo']);

    // درخواست لیست بارهای راننده
    Route::get('driver/requestDriverLoadsList/{driver_id}', [LoadController::class, 'requestDriverLoadsList']);

    // درخواست تغییر ناوگان راننده
    Route::post('driver/requestChangeFleet', [DriverController::class, 'requestChangeFleet']);

    // تایید حمل بار توسط راننده
    Route::post('driver/confirmLoad', [DriverController::class, 'confirmLoad']);

    // درخواست لیست باریهای جدید برای راننده
    Route::get('driver/requestNewLoadsForDriver/{driver_id}', [LoadController::class, 'requestNewLoadsForDriver']);

    // درخواست نوتیفیکیشن بارهای جدید
    Route::post('driver/requestNewLoadsNotification/{driver_id}', [DriverController::class, 'requestNewLoadsNotification']);

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

    // لیست مسیرهای راننده و لیست تمام شهرها
    Route::get('driver/requestPathsListAndAllCitiesList/{driver_id}', [DriverController::class, 'requestPathsListAndAllCitiesList']);

    // درخواست اطلاعات پروفایل راننده
    Route::get('driver/requestDriverProfileInfo/{driver_id}', [DriverController::class, 'requestDriverProfileInfo']);

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
    Route::post('driver/changeNotificationFunction', [NotificationController::class, 'changeNotificationFunction']);
    Route::post('bearing/changeNotificationFunction', [NotificationController::class, 'changeNotificationFunction']);
    Route::post('customer/changeNotificationFunction', [NotificationController::class, 'changeNotificationFunction']);

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

    // درخواست اطلاعات راننده انتخاب شده
    Route::post('bearing/requestDriverInfo', [DriverController::class, 'requestDriverInfo']);

    // گزارش عدم توافق با راننده
    Route::post('bearing/reportDriver', [DriverController::class, 'reportDriver']);

    // درخواست محاسبه هزنیه انتخاب راننده
    Route::get('bearing/requestSelectDriverCost/{load_id}', [LoadController::class, 'requestSelectDriverCost']);

    // چک کردن باربری انتخاب شده برای بار
    Route::post('bearing/checkSelectedBearingOfLoad', [LoadController::class, 'checkSelectedBearingOfLoad']);

    // درخواست اطلاعات باربری
    Route::get('bearing/requestBearingInfo/{id}', [BearingController::class, 'requestBearingInfo']);

    // درخواست لیست قیمت های استعلام بار
    Route::get('bearing/requestInquiriesOfLoad/{load_id}', [LoadController::class, 'requestInquiriesOfLoad']);

    // تغییر وضعیت بار
    Route::post('bearing/changeLoadStatus', [LoadController::class, 'changeLoadStatus']);

    // Pay for displaying the load information
    Route::post('bearing/payForDisplayingTheLoadInformation', [LoadController::class, 'payForDisplayingTheLoadInformation']);

    // درخواست راننده برای باربری
    Route::post('bearing/requestDriverForLoad', [LoadController::class, 'requestDriverForLoad']);

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

    // ارسال پیام
    Route::post('customer/sendMessage', [ContactUsController::class, 'sendMessage']);
    Route::post('bearing/sendMessage', [ContactUsController::class, 'sendMessage']);
    Route::post('driver/sendMessage', [ContactUsController::class, 'sendMessage']);
    Route::post('web/sendMessage', [ContactUsController::class, 'sendMessageInWeb']);

    // ارسال امتیاز و نظر از طرف صاحب بار برای باربری
    Route::post('customer/sendScoreAndCommentToLoadFromCustomer', [ContactUsController::class, 'sendScoreAndCommentToLoadFromCustomer']);

    /**********************************************************************************************/
    // بسته بندی ها
    /**********************************************************************************************/
    //لیست بسته بندی ها
    Route::get('customer/requestPackingTypes', [PackingTypeController::class, 'requestPackingTypes']);

    /***********************************************************************************************/
    // ذخیره توکن فایر ببس
    Route::post('bearing/saveMyFireBaseToken', [BearingController::class, 'saveMyFireBaseToken']);
    Route::post('customer/saveMyFireBaseToken', [CustomerController::class, 'saveMyFireBaseToken']);


    Route::group(['prefix' => 'customer'], function () {
        // درخواست ثبت نام صاحب بار
        Route::post('registerCustomer', [RegisterController::class, 'registerCustomer']);

        // توقف مناقصه به صورت دستی
        Route::post('stopTenderManually/{load}', [TenderController::class, 'stopTenderManually']);

        // انتقاد یا شکایت صاحب بار
        Route::post('storeComplaintCustomer/{customer}', [ComplaintController::class, 'storeComplaintCustomer']);

        // پیگیری انتقاد یا شکایت صاحب بار
        Route::post('getComplaintCustomerResult/{id}', [ComplaintController::class, 'getComplaintCustomerResult']);


        // اضافه کردن ناوگان به بار توسط صاحب بار
        Route::post('addFleetToLoadByCustomer', [LoadController::class, 'addFleetToLoadByCustomer']);

        // حذف ناوگان از بار
        Route::delete('removeFleetOfLoadByCustomer/{fleetLoad}', [LoadController::class, 'removeFleetOfLoadByCustomer']);

        // بررسی وضعیت شارژ اکانت صاحب بار
        Route::get('checkCustomerAccountChargeStatus/{customer}/{action}', [CustomerController::class, 'checkCustomerAccountChargeStatus']);

        // درخواست اطلاعات مشتری
        Route::get('requestCustomerInfo/{customer}', [CustomerController::class, 'requestCustomerInfo']);

    });

    Route::group(['prefix' => 'transportationCompany'], function () {

        Route::get('requestSuggestionPrice/{transportationCompany_id}/{load_id}', [TenderController::class, 'requestTransportationCompanySuggestionPrice']);

        // درخواست لیست بارهای جدید برای باربری
        Route::get('requestNewLoads/{transportationCompany_id}', [LoadController::class, 'requestNewLoads']);

        // اضافه کردن ناوگان به بار توسط باربری
        Route::post('addFleetToLoadByTransportationCompany', [LoadController::class, 'addFleetToLoadByTransportationCompany']);

        // حذف ناوگان انتخاب شده از لیست توسط باربری
        Route::get('removeFleetToLoadByTransportationCompany/{fleet_load_id}', [LoadController::class, 'removeFleetToLoadByTransportationCompany']);

        // درخواست اطلاعات رانندگان درحال حمل بار
        Route::get('requestDriversInfoOfCargo/{driver}', [LoadController::class, 'requestDriversInfoOfCargo']);

        // بررسی اعتبار حساب کاربری شرکت باربری
        Route::get('checkUserAccountCredit/{id}', [BearingController::class, 'checkUserAccountCredit']);

        // درخواست لیست رانندگان بار
        Route::get('requestLoadDriversList/{load}', [LoadController::class, 'requestLoadDriversList']);

        // پرداخت شارژ ماهیانه از کیف پول
        Route::post('payMonthlyChargeFromWallet', [BearingController::class, 'payMonthlyChargeFromWallet']);


        // انتقاد یا شکایت راننده از صاحب بار یا باربری
        Route::post('storeComplaintTransportationCompany/{transportationCompany}', [ComplaintController::class, 'storeComplaintTransportationCompany']);

        // پیگیری انتقاد یا شکایت راننده از صاحب بار یا باربری
        Route::post('getComplaintTransportationCompanyResult/{transportationCompany}', [ComplaintController::class, 'getComplaintTransportationCompanyResult']);
    });


    Route::group(['prefix' => 'driver'], function () {

        Route::get('radio', [RadioController::class, 'index']);

        // بررسی ثبت درخواست حمل توسط راننده
        Route::get('checkDriverInquiry/{driver_id}/{load_id}', [LoadController::class, 'checkDriverInquiry']);

        // نزدیکترین بار به راننده
        Route::post('searchTheNearestCargo/{driver}', [LoadController::class, 'searchTheNearestCargo']);

        // درخواست لیست بارهای جدید برای راننده ها
        Route::get('requestNewLoadsForDrivers/{driver}', [LoadController::class, 'requestNewLoadsForDrivers']);

        // جستجوی بار توسط راننده
        Route::post('searchLoadForDriver/{driver}', [LoadController::class, 'searchLoadForDriver']);

        // بررسی وضعیت شارژ راننده برای تماس
        Route::get('checkDriverStatusForCalling/{driver}/{phoneNumber?}/{load_id?}/{latitude?}/{longitude?}', [DriverController::class, 'checkDriverStatusForCalling']);

        Route::get('getPackagesInfo', [DriverController::class, 'getPackagesInfo']);

        Route::get('getLoadListFromDate/{driver}/{day}/{fleetId?}', [LoadController::class, 'getLoadListFromDate']);

        // بارهای موجود در مقصد
        Route::post('LoadsInDestinationCity/{driver}/{city}', [LoadController::class, 'LoadsInDestinationCity']);

        // انتقاد یا شکایت راننده از صاحب بار یا باربری
        Route::post('storeComplaintDriver/{driver}', [ComplaintController::class, 'storeComplaintDriver']);

        // پیگیری انتقاد یا شکایت راننده از صاحب بار یا باربری
        Route::post('getComplaintDriverResult/{driver}', [ComplaintController::class, 'getComplaintDriverResult']);

        // لیست شکایات و انتقادات راننده
        Route::get('getComplaintDriver/{driver}', [ComplaintController::class, 'getComplaintDriver']);

        // بررسی وضعیت شارژ راننده برای قبول بار
        Route::get('checkDriverStatusForAcceptLoad/{driver}', [DriverController::class, 'checkDriverStatusForAcceptLoad']);

        // دریافت اطلاعات بار برای راننده
        Route::get('getLoadInfo/{load_id}/{driver_id}', [LoadController::class, 'getLoadInfoForDriver']);

        // دریافت جزئیات بار برای راننده
        Route::get('loadDetail/{load_id}/{driver_id}', [LoadController::class, 'loadDetail']);

        Route::post('report', [ApiReportController::class, 'store']);


        Route::group(['prefix' => 'v2'], function () {
            // درخواست لیست بارهای جدید (ورژن 2)
            Route::get('requestNewLoads/{driver}', [LoadController::class, 'requestNewLoadsForDriversV2']);
        });

        // دریافت لیست بارها برای راننده به صورت صفحه بندی شده
        // از نسخه 52 به بعد این امکان را دارند
        Route::get('getNewLoadForDriver/{driver}/{lastLoadId}', [LoadController::class, 'getNewLoadForDriver']);

        // تاریخچه تماس راننده
        Route::get('callHistory/{driver}', [LoadController::class, 'callHistory']);

        // دریافت اطلاعات پروفایل راننده
        Route::get('getDriverProfileInfo/{driver}', [DriverController::class, 'getDriverProfileInfo']);

        // بروز رسانی اطلاعات راننده
        Route::put('updateDriverProfileInfo/{driver}', [DriverController::class, 'updateProfileInfo']);


        Route::put('updateLocation/{driver}', [DriverController::class, 'updateLocation']);

        //
        Route::get('driverAppVersion/{driver}/{version}', [DriverController::class, 'driverAppVersion']);


        Route::get('requestNewLoads/{driver}', [LoadController::class, 'requestNewLoadsForDriversV2']);

        Route::get('driverMessages/{mobileNumber}', [ContactUsController::class, 'driverMessages']);

        // ذخیره توکن رانندگان
        Route::patch('saveMyFireBaseToken/{driver}', [DriverController::class, 'saveMyFireBaseToken']);

    });

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


    // برنامه جدید صاحب بار
    Route::group(['prefix' => 'owner'], function () {
        Route::post('register', [OwnerController::class, 'register']);
        // اعتبارسنجی کد فعال سازی برای باربری و صاحبان بار
        Route::post('verifyActivationCodeForCustomerBearing', [LoginController::class, 'verifyActivationCodeForCustomerBearing']);

        // احراز هویت صاحب بار
        Route::put('authOwner/{owner}', [OwnerController::class, 'authOwner'])->name('auth.owner');

        // احراز هویت باربری
        Route::put('authBearing/{owner}', [OwnerController::class, 'authBearing'])->name('auth.bearing');

        // تغییر عکس پروفایل کاربری
        Route::patch('profileImage/{owner}', [OwnerController::class, 'profileImage'])->name('auth.profileImage');

        // ثبت بار جدید
        Route::post('createNewLoad', [LoadController::class, 'createNewLoad']);

        // ثبت بار بصورت ارایه
        Route::post('createNewLoads', [LoadController::class, 'createNewLoads']);

        Route::get('sendNotifLoad/{load}', [LoadController::class, 'sendNotifLoad']);

        // درخواست اطلاعات صاحبان بار
        Route::get('profile/{owner}', [OwnerController::class, 'profile']);

        // حذف بار توسط صاحب بار
        Route::delete('removeOwnerLoad/{load}/{owner}', [LoadController::class, 'removeOwnerLoad']);

        // تکرار بار
        Route::get('repeatOwnerLoad/{load}', [LoadController::class, 'repeatOwnerLoad']);

        // درخواست اطلاعات بار
        Route::get('requestLoadInfo/{id}', [LoadController::class, 'requestLoadInfo']);

        // ویرایش اطلاعات بار
        Route::patch('editLoadInfo/{load}/{api}', [LoadController::class, 'editLoadInfo']);

        // درخواست لیست بارهای صاحبان بار
        Route::get('requestCustomerLoadsLists/{id}', [LoadController::class, 'requestCustomerLoadsLists']);

        // درخواست لیست بارهای بایگانی صاحبان بار
        Route::get('requestCustomerLoadsTrashed/{id}', [LoadController::class, 'requestCustomerLoadsTrashed']);

        // انتقاد یا شکایت صاحب بار
        Route::post('storeComplaintOwner/{owner}', [ComplaintController::class, 'storeComplaintOwner']);

        // پیگیری انتقاد یا شکایت صاحب بار
        Route::post('getComplaintOwnerResult/{owner}', [ComplaintController::class, 'getComplaintOwnerResult']);

        Route::get('provinceCities', [ProvinceCityController::class, 'index']);

        Route::post('report', [ApiReportController::class, 'store']);

        // انتخاب راننده برای بار توسط صاحب بار
        Route::post('selectDriverForLoadByOwner', [LoadController::class, 'selectDriverForLoadByOwner']);

        // فعال یا غیرفعال نوتیفیکیشن
        Route::put('changeNotificationFunction', [NotificationController::class, 'changeNotificationFunction']);

        // ذخیره توکن فایر ببس
        Route::patch('saveMyFireBaseToken/{owner}', [OwnerController::class, 'saveMyFireBaseToken']);

        // درخواست نوتیفیکیشن صاحبان بار
        Route::post('sendCustomNotificationOwner', [NotificationController::class, 'sendCustomNotificationOwner']);

    });
});


// کال بک بیمه
Route::post('InsuranceCallBack', function (Request $request) {
});

// کال بک احراز هویت کدملی
Route::get('DidarCallBack', function () {
    return response()->json([
        'result' => true,
    ]);
});

Route::post('extractData', [DataConvertController::class, 'extractData']);

Route::post('botData', function (Request $request) {

    // try {
    //     $data = convertFaNumberToEn($request->data);
    //     preg_match('/09\d{2}/', $data, $matches);

    //     $cargoConvertListCount = CargoConvertList::where([
    //         ['cargo', $data],
    //         ['created_at', '>', date('Y-m-d h:i:s', strtotime('-180 minute', time()))]
    //     ])->count();
    //     if ($cargoConvertListCount == 0 && isset($matches[0])) {
    //         $cargoConvertList = new CargoConvertList();
    //         $cargoConvertList->cargo = $data;
    //         $cargoConvertList->save();
    //     }
    //     return 'OK';
    // } catch (Exception $exception) {
    //     \Illuminate\Support\Facades\Log::emergency("------------------- botData ERROR ---------------------");
    //     \Illuminate\Support\Facades\Log::emergency($exception->getMessage());
    //     \Illuminate\Support\Facades\Log::emergency("------------------- End botData ERROR ---------------------");
    // }

    // return 'ERROR';
});


Route::post('botData1', function (Request $request) {
    // \Illuminate\Support\Facades\Log::emergency($request->all());

    try {
        foreach ($request->data as $value) {
            $data = convertFaNumberToEn($value);
            preg_match('/09\d{2}/', $data, $matches);

            $cargoConvertListCount = CargoConvertList::where([
                ['cargo', $data],
                // ['message_id', $request->message_id],
                ['created_at', '>', date('Y-m-d h:i:s', strtotime('-180 minute', time()))]
            ])->count();
            if ($cargoConvertListCount == 0 && isset($matches[0])) {
                $cargoConvertList = new CargoConvertList();
                $cargoConvertList->cargo = $data;
                $cargoConvertList->save();
            }
        }

        return 'OK';
    } catch (Exception $exception) {
        \Illuminate\Support\Facades\Log::emergency("------------------- botData ERROR ---------------------");
        \Illuminate\Support\Facades\Log::emergency($exception->getMessage());
        \Illuminate\Support\Facades\Log::emergency("------------------- End botData ERROR ---------------------");
    }
});

Route::get('dictionary', function () {
    return Dictionary::select('original_word_id', 'type', 'equivalentWord')->get();
});
