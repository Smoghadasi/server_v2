<?php

use App\Http\Controllers\AccountingController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\SalaryController;
// use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthorizeController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\BearingController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\CityDistanceController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\FreeSubscriptionController;
use App\Http\Controllers\ContactUsController;
use App\Http\Controllers\ContractCollaborationController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\DataConvertController;
use App\Http\Controllers\DataConvertPlusController;
use App\Http\Controllers\DiscrepancyController;
use App\Http\Controllers\DriverActivityController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\FirstLoadController;
use App\Http\Controllers\FleetController as AdminFleetController;
use App\Http\Controllers\FleetlessNumberController;
use App\Http\Controllers\FreeCallDriverController;
use App\Http\Controllers\FreightInquiryController;
use App\Http\Controllers\GroupNotificationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LimitCallController;
use App\Http\Controllers\LoadController;
use App\Http\Controllers\LoginHistoryController;
use App\Http\Controllers\ManualNotificationController;
use App\Http\Controllers\MarketerController;
use App\Http\Controllers\OperatorContactingController;
use App\Http\Controllers\OperatorController;
use App\Http\Controllers\Owner\AuthController as OwnerAuthController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\PackingTypeController;
use App\Http\Controllers\ParameterController;
use App\Http\Controllers\PayController;
use App\Http\Controllers\PersonalizedNotificationController;
use App\Http\Controllers\ProvinceCityController;
use App\Http\Controllers\RadioController;
// use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportingController;
use App\Http\Controllers\RuleRegulationController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SliderController;
// use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SOSController;
use App\Http\Controllers\StoreCargoOperatorController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\TenderController;
use App\Http\Controllers\TrackableItemController;
use App\Http\Controllers\TransactionManualController;
use App\Http\Controllers\User\BlockPhoneNumberController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VacationController;
use App\Http\Controllers\VacationHourController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\WebNotificationController;
use App\Jobs\SendNotificationJob;
use App\Models\City;
// use App\Models\Driver;
use App\Models\Load;
use App\Models\State;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::get('/dashboardOpererator', function () {
    return view('dashboardOpererator');
})->name('dashboardOpererator');


Route::post('/check-mobile', [AuthController::class, 'checkMobile'])->name('check.mobile');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('user.resetPassword');

Route::post('check-user', [AuthController::class, 'checkUser'])->name('check.user');

Route::get('refereshcapcha', function () {
    return captcha_img('math');
});

Route::post('loginUser', [AuthorizeController::class, 'loginPost'])->name('authorize.login');

Route::post('checkActivationCode', [AuthController::class, 'checkActivationCode'])->name('checkActivationCode');

Route::get('/conf', function () {
    Artisan::call('optimize:clear');
});

Route::get('hourClear', function () {
    // DB::table('loads')
    //     ->where('created_at', '<', now()->subMinutes(60))
    //     ->update(['urgent' => 0]);
    $title = 'راننده عزیز، 🎉';
    $body  = "اعتبار برای شما فعال شد.\nهمین حالا می‌تونی با صاحب بار مورد نظرت تماس بگیری 📞";

    dispatch(new SendNotificationJob('f7AYrXxfRqSjd5k5VBGVjD:APA91bFJdMjIolJAgMKaZC5XPRy6RqNzVh_CBZsVgR3sOvW6q1acM7RJQIDW2emRTfU5GQTYvrZCckwDU0GW4DGOW6y1-3iNajR_2tqi68eEkf18Mgkx9uU', $title, $body));
});

Route::get('/cache', function () {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('config:cache');
});


Route::group(['middleware' => 'throttle:60,1'], function () {


    Route::get('/', function () {
        return redirect('/dashboard');
    });


    // Auth::routes(['register' => false]);

    Route::get('/home', function () {
        return view('welcome');
    });


    Route::middleware(["operator"])->group(function () {
        Route::get('dashboard', [HomeController::class, 'dashboard']);
    });


    Route::get('/marketerLogin', function () {
        return view('auth/marketerLogin');
    });


    // مسیریابی های اپراتور های سیستم
    Route::group(['prefix' => 'admin', 'operator'], function () {

        Route::get('history', [LoginHistoryController::class, 'index'])->name('login.history');

        // صفر کردن دیتا ها
        Route::get('zeroData', [DriverController::class, 'zeroData'])->middleware('operator')->name('driver.zeroData');

        Route::get('appVersions', [HomeController::class, 'appVersions']);
        Route::post('storeAppVersions', [HomeController::class, 'storeAppVersions']);
        // Route::get('driverActivityVersion/{version}', [HomeController::class, 'driverActivityVersion'])->name('driver.activity.version');

        // لیست رانندگان بر اساس فعالیت یک ماهه اخیر
        Route::get('driverActivityVersion/{version}', [DriverActivityController::class, 'index'])->name('driver.activity.version');


        Route::get('driverActive/{version}', [DriverController::class, 'driverActive'])->name('admin.driverActive.version');

        // equivalents
        Route::get('equivalents', [DataConvertController::class, 'equivalents'])
            ->middleware('operator')
            ->name('equivalent.index');

        Route::post('addWordToEquivalent', [DataConvertController::class, 'addWordToEquivalent'])->middleware('operator');
        Route::delete('removeEquivalentWord/{equivalent}', [DataConvertController::class, 'removeEquivalentWord'])->middleware('operator')->name('removeEquivalentWord');


        // استان ها و شهرها جدید
        Route::resource('provinceCity', ProvinceCityController::class)->middleware("operator");

        // شهرها و استان ها
        Route::get('provincesAndCities', [AddressController::class, 'provincesAndCities'])->middleware('operator');

        // لیست شهرهای استان
        Route::get('provinceCitiesList/{province_id}', [AddressController::class, 'provinceCitiesList'])->middleware('operator');

        // ثبت شهر جدید
        Route::post('addNewCity/{state}', [AddressController::class, 'addNewCity'])->middleware('operator');


        Route::put('updateCity/{city}', [AddressController::class, 'updateCity'])->middleware('operator')->name('city.update');

        // حذف شهر
        Route::get('removeCity/{city}', [AddressController::class, 'removeCity'])->middleware('admin');


        Route::get('centerOfProvince/{city}', [AddressController::class, 'centerOfProvince'])->middleware('admin');

        // centerOfProvinceCities
        Route::get('centerOfProvinceCities/{provinceCity}', [AddressController::class, 'centerOfProvinceCities'])
            ->middleware('admin')
            ->name('centerOfProvinceCities');

        /***************************************************************************************************/
        /***************************************************************************************************/

        Route::get('driversActivitiesCallDate', [DriverController::class, 'driversActivitiesCallDate'])->middleware('operator')->name('report.driversActivitiesCallDate');
        Route::get('driversActivitiesCallDate/{driver}', [DriverController::class, 'driversActivitiesCall'])->middleware('operator')->name('report.driversActivitiesCallDate.show');

        Route::post('searchDriversActivitiesCallDate', [DriverController::class, 'searchDriversActivitiesCallDate'])
            ->middleware('operator')
            ->name('search.driver.activitiesCallDate');

        Route::get('searchDriversActivitiesCallDate', function () {
            return redirect('admin/driversActivitiesCallDate');
        });

        Route::match(['post', 'get'], 'driversActivities/{date?}', [DriverController::class, 'driversActivities'])->middleware('operator');

        // تغییر وضعیت آپشن های سایت
        Route::get('changeSiteOption/{option}', [HomeController::class, 'changeSiteOption'])->middleware('operator');

        // خروج از بخش کاربری
        Route::get('logout', [UserController::class, 'logout'])->middleware('operator');

        // کاربران
        Route::resource('operators', OperatorController::class)->middleware('operator');

        // حقوق دریافتی
        Route::resource('salary', SalaryController::class)->middleware('operator');

        // قرارداد ها
        Route::resource('contract', ContractController::class)->middleware('operator');

        // لیست قرارداد ها
        Route::resource('collaboration', ContractCollaborationController::class)->middleware('operator');


        Route::get('operator/vacationDay/{user_id}', [VacationController::class, 'vacationDay'])
            ->middleware('operator')
            ->name('vacation.day');

        Route::get('operator/vacationHour/{user_id}', [VacationHourController::class, 'vacationHour'])
            ->middleware('operator')
            ->name('vacation.hour');

        // تغییر وضعیت اپراتور
        Route::get('changeOperatorStatus/{user}', [OperatorController::class, 'changeOperatorStatus'])->middleware('admin');;

        //  مرخصی روزانه
        Route::resource('vacations', VacationController::class);

        //  مرخصی ساعتی
        Route::resource('vacationHour', VacationHourController::class);

        // دسترسی ها
        Route::post('operatorAccess/{user}', [UserController::class, 'operatorAccess']);

        /*************************************************************************************/
        // مسیریابی های ناوگان
        /*************************************************************************************/

        // صفحه ناوگان
        Route::resource('fleet', AdminFleetController::class)->middleware("operator");

        /***********************************************************************************************/
        // باربری ها
        /*************************************************************************************/
        Route::get('bearing', [BearingController::class, 'bearing'])->middleware('operator');
        Route::post('bearing', [BearingController::class, 'searchBearing'])->middleware('operator');

        // نمایش لیست بارهای یک باربری
        Route::get('bearingLoads/{id}', [LoadController::class, 'bearingLoads'])->middleware('operator')->name('bearing.loads');

        // نمایش فرم افزودن باربری
        Route::get('addNewBearingForm', [BearingController::class, 'addNewBearingForm'])->middleware('admin');

        // افزودن باربری
        Route::post('addNewBearing', [BearingController::class, 'addNewBearing'])->middleware('admin');

        // تغییر باربری به فعال یا غیر فعال
        Route::get('changeBearingStatus/{bearing_id}', [BearingController::class, 'changeBearingStatus'])->middleware('operator');

        // فرم ویرایش اطلاعات باربری
        Route::get('editBearingInfoForm/{bearing_id}', [BearingController::class, 'editBearingInfoForm'])->middleware('operator');

        // ویرایش اطلاعات باربری
        Route::post('editBearingInfo', [BearingController::class, 'editBearingInfo'])->middleware('operator');

        // حذف باربری
        Route::get('removeTransportationCompany/{bearing}', [BearingController::class, 'removeTransportationCompany'])->middleware('admin');

        /*************************************************************************************/
        // مشتریان
        /*************************************************************************************/

        // نمایش لیست مشتریان
        Route::get('customers', [CustomerController::class, 'customers'])->middleware('operator');


        //جستجو
        Route::post('customers', [CustomerController::class, 'searchCustomers'])->middleware('operator');

        // لیست صاحبان بار
        Route::resource('owner', OwnerController::class)->middleware("operator");

        // علامت گذاری
        Route::resource('bookmark', BookmarkController::class)->middleware("operator");

        Route::resource('trackableItems', TrackableItemController::class)->middleware("operator");

        Route::resource('limitCall', LimitCallController::class)->middleware("operator");


        Route::get('ownerOperators', [OwnerController::class, 'ownerOperators'])
            ->name('owner.operators')
            ->middleware('operator');

        Route::get('ownersNissan', [OwnerController::class, 'ownersNissan'])
            ->name('ownersNissan')
            ->middleware('operator');


        Route::delete('removeProfile/{owner}', [OwnerController::class, 'removeProfile'])->name('owner.removeProfile');

        // تغییر وضعیت صاحبان بار
        Route::get('changeOwnerStatus/{owner}', [OwnerController::class, 'changeOwnerStatus'])->middleware('admin')->name('owner.change.status');

        Route::get('ownerSearch', [OwnerController::class, 'searchOwners'])->middleware('operator')->name('owner.search');


        // بارهای مشتریان
        Route::get('customerLoads/{customer_id}', [LoadController::class, 'customerLoads'])->middleware('operator')->name('customer.loads');

        // بارهای صاحبان بار
        Route::get('ownerLoads/{owner_id}', [LoadController::class, 'ownerLoads'])->middleware('operator')->name('owner.loads');

        // بار های ثبت شده توسط صاحبین بار
        Route::get('loadBackup', [LoadController::class, 'loadBackup'])->middleware('operator')->name('admin.loadBackup');

        // بار های ثبت شده صاحبان بار
        Route::get('loadOwner', [LoadController::class, 'loadOwner'])->middleware('operator')->name('admin.load.owner');


        // رانندگان نزدیک بار
        Route::get('nearLoadDrivers/{load_id}', [LoadController::class, 'nearLoadDrivers'])->middleware('operator')->name('admin.nearLoadDrivers');

        Route::get('driverVisitLoads/{load_id}', [LoadController::class, 'driverVisitLoads'])->middleware('operator')->name('admin.driverVisitLoads');

        Route::get('sendNearLoadDrivers/{load_id}/{type}', [LoadController::class, 'sendMessageNearLoadDrivers'])
            ->middleware('operator')
            ->name('admin.sendNearLoadDrivers');


        Route::get('sendNotifLoadVisit/{load_id}/{type}', [LoadController::class, 'sendNotifLoadVisit'])
            ->middleware('operator')
            ->name('admin.sendNotifLoadVisit');

        // بار های ثبت شده صاحبان بار (تلگرام)
        Route::get('loadOperators', [LoadController::class, 'loadOperators'])->middleware('operator')->name('admin.load.operator');

        // جستجو بر اساس درخواست کنندگان بار
        Route::get('searchLoadInquiry/{load_id}', [LoadController::class, 'searchLoadInquiry'])
            ->middleware('operator')
            ->name('load.searchLoadInquiry');

        // جستجو بر اساس تماس رانندگان
        Route::get('searchLoadDriverCall/{load_id}', [LoadController::class, 'searchLoadDriverCall'])
            ->middleware('operator')
            ->name('load.searchLoadDriverCall');

        Route::post('storeDriverCallLoad', [LoadController::class, 'storeDriverCall'])
            ->middleware('operator')
            ->name('load.driverCall.store');


        // (امروز)بار های ثبت شده صاحبان بار
        Route::get('loadOwnerToday', [LoadController::class, 'loadOwnerToday'])->middleware('operator')->name('loadToday.owner');

        // بار های ثبت شده توسط صاحبان بار

        Route::post('searchLoadBackupCustomer', [LoadController::class, 'searchLoadBackupCustomer'])->middleware('operator')->name('search.loadback.customer');
        Route::get('searchLoadBackupCustomer', function () {
            return redirect('admin/loadBackup');
        });

        // بار های ثبت شده توسط باربری
        Route::get('loadBackup-transportation', [LoadController::class, 'loadBackupTransportation'])->middleware('operator')->name('admin.loadBackupTransportation');

        Route::post('searchLoadBackupTransportation', [LoadController::class, 'searchLoadBackupTransportation'])->middleware('operator')->name('search.loadback.Transportation');
        Route::get('searchLoadBackupCustomer', function () {
            return redirect('admin/searchLoadBackupTransportation');
        });

        // جستجو همه جدول ها
        Route::get('searchAll', [HomeController::class, 'searchAll'])
            ->middleware('operator')
            ->name('admin.searchAll');

        // تغییر مشتری به فعال یا غیر فعال
        Route::get('changeCustomerStatus/{customer_id}', [CustomerController::class, 'changeCustomerStatus'])->middleware('operator');

        // تغییر باربری به فعال یا غیر فعال
        Route::get('changeBearingStatus/{earing_id}', [BearingController::class, 'changeBearingStatus'])->middleware('operator');

        Route::delete('removeCustomer/{customer}', [CustomerController::class, 'removeCustomer']);

        Route::patch('updateCustomer/{customer}', [CustomerController::class, 'updateCustomer']);

        /*************************************************************************************/
        // نوع بارها
        /*************************************************************************************/

        // نوع بارها
        Route::get('loadType', [LoadController::class, 'loadType'])->middleware('admin');

        // فرم افزودن نوع بار جدید
        Route::get('addNewLoadTypeForm', [LoadController::class, 'addNewLoadTypeForm'])->middleware('admin');

        // افزودن نوع بار جدید
        Route::post('addNewLoadType', [LoadController::class, 'addNewLoadType'])->middleware('admin');

        // فرم ویرایش ناوگان
        Route::get('editLoadTypeForm/{id}', [LoadController::class, 'editLoadTypeForm'])->middleware('admin');

        // ویرایش ناوگان
        Route::post('editLoadType', [LoadController::class, 'editLoadType'])->middleware('admin');

        // حذف ناوگان
        Route::get('deleteLoadType/{id}', [LoadController::class, 'deleteLoadType'])->middleware('admin');

        /*************************************************************************************/
        // بارها
        /*************************************************************************************/

        Route::post('createNewLoad', [LoadController::class, 'createNewLoadInWeb'])->middleware('operator');;

        // نمایش لیست بارها
        Route::get('loads', [LoadController::class, 'loads'])->middleware('operator');

        // نمایش گروه بندی بارها بر اساس وضعیت
        Route::get('displayLoadsCategoriesFromLoadStatus', [LoadController::class, 'displayLoadsCategoriesFromLoadStatus'])->middleware('operator');

        // لیست بارها
        Route::get('loads/{statusType}', [LoadController::class, 'loadsWithStatusType'])->middleware('operator');

        // جستجوی بارها
        Route::post('searchLoad', [LoadController::class, 'searchLoadInWeb'])->middleware('operator');

        // جستجوی بارها
        Route::get('searchLoadsForm', [LoadController::class, 'searchLoadsForm'])->middleware('operator')->name('search.load.form');
        Route::get('searchLoads', [LoadController::class, 'searchLoads'])->middleware('operator');

        Route::get('copyLoad/{type}', [LoadController::class, 'copyLoad'])->name('copyLoad')->middleware('operator');

        Route::put('copyLoad/{load}', [LoadController::class, 'updateCopyLoad'])->name('copyLoad.update')->middleware('operator');

        // نمایش اطلاعات بار
        Route::get('loadInfo/{load_id}', [LoadController::class, 'loadInfo'])->middleware('operator')->name('loadInfo');

        Route::get('sendNotifManuall/{load}', [LoadController::class, 'sendNotifManuall'])->middleware('operator')->name('sendNotifManuall');

        // فرم افزودن بار جدید
        Route::get('addNewLoadForm/{userType}', [LoadController::class, 'addNewLoadForm'])->middleware('operator');

        // لیست تایید بار
        Route::get('acceptCargo', [LoadController::class, 'acceptCargo'])->middleware('operator')->name('accept.cargo.index');
        Route::post('acceptCargo/{id}', [LoadController::class, 'acceptCargoStore'])->middleware('admin')->name('accept.cargo.store');

        // فرم ویرایش اطلاعات بار
        Route::get('editLoadInfoForm/{load_id}', [LoadController::class, 'editLoadInfoForm'])->middleware('operator');

        // ویرایش اطلاعات بار
        Route::post('editLoadInfo/{load}', [LoadController::class, 'editLoadInfoInWeb'])->name('admin.editLoadInfo')->middleware('operator');

        // حذف اطلاعات بار
        Route::get('removeLoadInfo/{load_id}', [LoadController::class, 'removeLoadInfo'])->middleware('operator');

        // حذف اطلاعات دسته ای بار
        Route::post('removeLoad', [LoadController::class, 'removeLoad'])->middleware('operator');

        Route::delete('remove/load/{load}', [LoadController::class, 'removeLoadItem'])->middleware('operator')->name('remove.load');

        Route::delete('deleteAll/load', [LoadController::class, 'deleteAll'])->middleware('operator')->name('load.delete.all');

        // اجزای مجدد مناقصه
        Route::get('repeatTender/{load_id}', [TenderController::class, 'repeatTender'])->middleware('operator');

        // ثبت قیمت برای باربری توسط ادمین
        Route::post('suggestionToLoadPriceByAdmin', [TenderController::class, 'suggestionToLoadPriceByAdmin'])->middleware('admin');

        // تغییر وضعیت بار به مرحله قبل
        Route::get('changeLoadStatusToPastStatus/{load_id}', [LoadController::class, 'changeLoadStatusToPastStatus'])->middleware('admin');

        // تایید بار در لیست بارها
        Route::get('acceptLoadFromLoadList/{load}', [LoadController::class, 'acceptLoadFromLoadList'])->middleware('operator');

        /*************************************************************************************/
        // آدرس ها، کشورها، استان ها، شهرها
        /*************************************************************************************/
        // نمایش کشور


        /*************************************************************************************/
        // راننده ها
        /*************************************************************************************/

        // نمایش لیست راننده ها
        Route::get('drivers', [DriverController::class, 'drivers'])->middleware('operator')->name('drivers');



        // Route::get('threeDaysDrivers', function () {

        //     $driver = Driver::whereNotNull('activeDate')
        //         // ->where('activeDate', '!=', '')
        //         ->whereRaw('DATEDIFF(activeDate, NOW()) = 3')
        //         ->count();
        // });
        // نمایش لیست رانندگان برای ادمین
        Route::get('adminDrivers', [DriverController::class, 'adminDrivers'])->middleware('operator')->name('adminDrivers');

        Route::get('drivers/{type}', [DriverController::class, 'driverSummery'])->middleware('operator')->name('driverSummery');

        // فرم ثبت راننده جدید
        Route::get('addNewDriverForm', [DriverController::class, 'addNewDriverForm'])->middleware('operator');

        Route::get('messages/{contactUs}', [ContactUsController::class, 'show'])
            ->name('messages.show')
            ->middleware('operator');

        // افزودن راننده جدید
        Route::post('addNewDriver', [DriverController::class, 'addNewDriver'])->middleware('operator');

        // نمایش اطلاعات راننده
        Route::get('driverInfo/{driver}', [DriverController::class, 'driverInfo'])->middleware('operator')->name('driver.detail');

        // تغییر راننده به فعال یا غیر فعال
        Route::get('changeDriverStatus/{driver_id}', [DriverController::class, 'changeDriverStatus'])->middleware('operator');

        // ویرایش راننده
        Route::get('editDriver/{driver}', [DriverController::class, 'editDriverForm'])->middleware('operator');
        Route::post('editDriver/{driver}', [DriverController::class, 'editDriver'])->middleware('operator');
        Route::put('updateAuthLevel/{driver}', [DriverController::class, 'updateAuthLevel'])->middleware('operator')->name('driver.updateAuthLevel');


        // جستجوی راننده
        Route::post('searchDrivers', [DriverController::class, 'searchDrivers'])->middleware('operator');


        Route::post('searchDriverCall', [DriverController::class, 'searchDriverCall'])->middleware('operator');
        Route::get('searchDriverCall', function () {
            return redirect('admin/contactingWithDrivers');
        });

        //حذف راننده
        Route::get('removeDriver/{driver}', [DriverController::class, 'removeDriver'])->middleware('admin');

        //حذف راننده
        Route::put('removeActiveDate/{driver}', [DriverController::class, 'removeActiveDate'])->middleware('operator')->name('removeActiveDate');

        // تمدید اعتبار رانندگان
        Route::post('creditDriverExtending/{driver}', [DriverController::class, 'creditDriverExtending']);

        /*************************************************************************************/
        // پیام ها
        /*************************************************************************************/
        // نمایش پیام ها
        Route::get('messages', [ContactUsController::class, 'messages'])->middleware('operator');

        // تغییر وضعیت پیامهای کاربران به خوانده شده
        Route::post('changeMessageStatus/{contactUs}', [ContactUsController::class, 'changeMessageStatus'])->middleware('operator');

        //
        Route::get('removeMessage/{contactUs}', [ContactUsController::class, 'removeMessage'])->middleware('admin');

        /*************************************************************************************/
        // نوع بسته بندی ها
        /*************************************************************************************/
        // نوع بسته بندی
        Route::get('packingType', [PackingTypeController::class, 'packingType'])->middleware('admin');

        // فرم ایجاد نوع بسته بندی جدید
        Route::get('addNewPackingTypeForm', [PackingTypeController::class, 'addNewPackingTypeForm'])->middleware('admin');

        // افزودن نوع بسته بندی جدید
        Route::post('addNewPackingType', [PackingTypeController::class, 'addNewPackingType'])->middleware('admin');

        // فرم ویرایش نوع بسته بندی
        Route::get('editPackingTypeForm/{id}', [PackingTypeController::class, 'editPackingTypeForm'])->middleware('admin');

        // ویرایش نوع بسته بندی
        Route::post('editPackingType', [PackingTypeController::class, 'editPackingType'])->middleware('admin');

        // حذف نوع بسته بندی
        Route::get('deletePackingType/{id}', [PackingTypeController::class, 'deletePackingType'])->middleware('admin');

        /**********************************************************************************************/
        // بازاریابها
        /**********************************************************************************************/

        // لیست بازاریابها
        Route::get('Marketers', [MarketerController::class, 'Marketers'])->middleware('operator');

        // فرم افزودن بازاریاب جدید
        Route::get('addNewMarketersForm', [MarketerController::class, 'addNewMarketersForm'])->middleware('operator');

        // افزودن بازاریاب جدید
        Route::post('addNewMarketer', [MarketerController::class, 'addNewMarketer'])->middleware('operator');

        //
        Route::get('addDriverForThisLoad/{load_id}', [DriverController::class, 'addDriverForThisLoad'])->middleware('operator');


        // تایید یا عدم تایید بار توسط ناظر
        Route::post('approveOrRejectLoad', [LoadController::class, 'approveOrRejectLoad'])->middleware('operator');

        // هشدار کلاه برداری! صاحب بار
        Route::get('scamAlert', [LoadController::class, 'scamAlert'])->name('admin.scamAlert')->middleware('operator');



        /**********************************************************************************************/
        // حسابداری
        /**********************************************************************************************/

        // حسابداری
        Route::get('accounting', [AccountingController::class, 'accounting'])->middleware('operator');


        /**********************************************************************************************/
        // امداد ها
        /**********************************************************************************************/

        // لیست درخواست های امداد
        Route::get('SOSList/{status}', [SOSController::class, 'SOSList'])->middleware('operator');

        // اطلاعات درخواست امداد
        Route::get('driverSOSInfo/{id}', [SOSController::class, 'driverSOSInfo'])->middleware('operator');

        // حذف امدادها
        Route::post('removeSOS', [SOSController::class, 'removeSOS'])->middleware('operator');

        /*******************************************************************************************/

        // لیست بارهای ثبت شده توسط اپراتورها
        Route::get('listOfLoadsByOperator', [LoadController::class, 'listOfLoadsByOperator'])->middleware('operator');

        /*******************************************************************************************/
        /*******************************************************************************************/

        // لیست انقادات و شکایات رانندگان
        Route::get('complaintsDriversList', [ComplaintController::class, 'complaintsDriversList'])->middleware('operator');

        // ذخیره پاسخ ادمین برای شکایت راننده
        Route::post('storeComplaintDriverAdminMessage/{complaintDriver}', [ComplaintController::class, 'storeComplaintDriverAdminMessage'])->middleware('operator');

        // لیست انقادات و شکایات باربری ها
        Route::get('complaintsTransportationCompanyList', [ComplaintController::class, 'complaintsTransportationCompanyList'])->middleware('operator');

        // ذخیره پاسخ ادمین برای شکایت باربری
        Route::post('storeComplaintTransportationCompanyAdminMessage/{complaintTransportationCompany}', [ComplaintController::class, 'storeComplaintTransportationCompanyAdminMessage'])->middleware('operator');

        // لیست انقادات و شکایات باربری ها
        Route::get('complaintsCustomerList', [ComplaintController::class, 'complaintsCustomerList'])->middleware('operator');

        // لیست انقادات و شکایات صاحبان بار
        Route::get('complaintsOwnerList', [ComplaintController::class, 'complaintsOwnerList'])->middleware('operator')->name('complaint.owner.list');

        // ذخیره پاسخ ادمین برای شکایت صاحبان بار
        Route::post('storeComplaintOwnerAdminMessage/{complaintOwner}', [ComplaintController::class, 'storeComplaintOwnerAdminMessage'])
            ->middleware('operator')
            ->name('admin.message.complaintOwner');

        // ذخیره پاسخ ادمین برای شکایت باربری
        Route::post('storeComplaintCustomerAdminMessage/{complaintCustomer}', [ComplaintController::class, 'storeComplaintCustomerAdminMessage'])->middleware('operator');

        /***************************************************************************************************/
        Route::get('profile', [UserController::class, 'adminProfile'])->middleware('operator')->name('user.edit');

        Route::resource('setting', SettingController::class)->middleware('operator');

        Route::resource('groupNotification', GroupNotificationController::class)->middleware('operator');

        Route::resource('manualNotification', ManualNotificationController::class)->middleware('operator');

        Route::post('sendManualNotification', [ManualNotificationController::class, 'sendManualNotification'])
            ->name('manualNotification.sendNotification')
            ->middleware('operator');

        // صورت مغایرت
        Route::resource('discrepancy', DiscrepancyController::class)->middleware('operator');

        // انبار بار
        // Route::resource('warehouse', WarehouseController::class)->middleware('operator');

        // رادیو
        Route::resource('radio', RadioController::class)->middleware('operator');

        // اشتراک های دستی
        Route::resource('transaction-manual', TransactionManualController::class)->middleware('operator');

        Route::get('transaction-search', [TransactionManualController::class, 'search'])
            ->middleware('operator')
            ->name('transaction-manual.search');


        Route::put('changeStatusTransaction/{transactionManual}', [TransactionManualController::class, 'changeStatus'])
            ->middleware('operator')
            ->name('transactionManual.change.status');

        Route::post('restPassword', [UserController::class, 'restPassword'])->middleware('operator')->name('user.resetPass');

        Route::post('resetPassByAdmin/{user}', [UserController::class, 'resetPasswordByAdmin'])->middleware('operator')->name('user.resetPassByAdmin');

        /**************************************************************************************************************/
        //  شماره تلفن های لیست ممنوعه
        Route::resource('blockedPhoneNumber', BlockPhoneNumberController::class);

        //  محاسبه شهرستان ها
        Route::resource('cityDistanceCalculate', CityDistanceController::class);

        //  slider
        Route::resource('slider', SliderController::class);


        /**************************************************************************************************************/
        // آی پی های مسدود
        Route::get('blockedIps', [UserController::class, 'blockedIps'])->middleware('operator');

        // مسدود کردن آی پی
        Route::get('blockUserIp/{user_id}/{userType}/{ip}', [UserController::class, 'blockUserIp'])->middleware('admin');

        Route::get('unBlockUserIp/{user_id}/{userType}', [UserController::class, 'unBlockUserIp'])->middleware('admin');


        /*******************************************************************************************************/
        // گزارش گیری
        /*******************************************************************************************************/

        // گزارش فعالیت اپراتورها
        Route::get('operatorsActivityReport', [ReportingController::class, 'operatorsActivityReport'])->middleware('operator');

        // گزارش فعالیت رانندگان نیسان در 24 ساعت گشته
        //        Route::get('daysActivityNissan', [ReportingController::class, 'daysActivityNissan'])->middleware('operator');

        // گزارش میزان ساعت کار اپراتور ها
        Route::match(['get', 'post'], 'operatorsWorkingHoursActivityReport', [ReportingController::class, 'operatorsWorkingHoursActivityReport'])->middleware('operator');

        // ذخیره نسبت ها
        Route::get('storeFleetRatioToDriverActivityReportData', [ReportingController::class, 'storeFleetRatioToDriverActivityReportData'])->middleware('operator');


        // گزارش جدول نسبت ناوگان (کل و فعال) به بار
        Route::match(['get', 'post'], 'fleetRatioToDriverActivityReport', [ReportingController::class, 'fleetRatioToDriverActivityReport'])->middleware('operator');


        // خلاصه گزارش روز
        Route::get('summaryOfDaysReport', [ReportingController::class, 'summaryOfDaysReport'])->middleware('operator');

        Route::get('support', [SupportController::class, 'index'])->name('admin.support')->middleware('operator');

        Route::get('supportDriver/{driver}', [SupportController::class, 'show'])
            ->name('admin.supportDriver.show')
            ->middleware('operator');


        Route::put('supportDriver/{support}', [SupportController::class, 'update'])->name('admin.indexDriver.update')->middleware('operator');

        // Route::resource('support', SupportController::class)->middleware('operator');


        // گزارش فعالیت رانندگان
        Route::get('driverActivityReport', [ReportingController::class, 'driverActivityReport'])->middleware('operator');


        // خلاصه گزارش ناوگان ها
        Route::get('fleetReportSummary', [ReportingController::class, 'fleetReportSummary'])->name('admin.report.fleetReportSummary')->middleware('operator');

        Route::get('getDriverActivityData', [ReportingController::class, 'getDriverActivityData'])
            ->name('admin.reporting.nonRepeate.data')
            ->middleware('operator');

        // گزارش فعالیت غیر تکراری
        Route::get('driverActivityNonRepeate', [ReportingController::class, 'driverActivityReportNonRepeat'])->name('admin.driverActivityNonRepeate')->middleware('operator');


        Route::get('searchDriverActivityReport', [ReportingController::class, 'searchDriverActivityReport'])->name('home.searchDriverActivityReport')->middleware('operator');

        // نمودار فعالیت ناوگان بر اساس تماس
        Route::get('driversContactCall', [ReportingController::class, 'driversContactCall'])->name('report.driversContactCall');

        // نمودار فعالیت رانندگان بر اساس تماس
        Route::get('driversCountCall', [ReportingController::class, 'driversCountCall'])->name('report.driversCountCall');

        // نتیجه فعالیت رانندگان بر اساس تماس
        Route::get('driversCountCallSearch', [ReportingController::class, 'driversCountCallSearch'])->name('report.driversCountCallSearch');

        Route::get('loadDriversCountCall/{callingDate}/{driverId}', [LoadController::class, 'loadDriversCountCall'])->name('report.loadDriversCountCall');

        Route::post('searchDriversCountCall', [ReportingController::class, 'searchDriversCountCall'])
            ->middleware('operator')
            ->name('search.driverCall.count');

        Route::get('searchDriversCountCall', function () {
            return redirect('admin/driversCountCall');
        });

        Route::get('driversInMonth', [ReportingController::class, 'driversInMonth'])->name('report.driversInMonth');

        // گزارش فعالیت باربری ها
        Route::get('transportationCompaniesActivityReport', [ReportingController::class, 'transportationCompaniesActivityReport'])->middleware('operator');

        // گزارش فعالیت باربری ها
        Route::get('cargoOwnersActivityReport', [ReportingController::class, 'cargoOwnersActivityReport'])->middleware('operator');

        // گزارشهای ترکیبی
        Route::get('combinedReports', [ReportingController::class, 'combinedReports'])->middleware('operator');

        // تایید بار بصورت هر شخص صاحب بار
        Route::get('acceptCustomer/{mobileNumber}', [CustomerController::class, 'acceptCustomer'])->middleware('operator')->name('acceptCustomer');
        Route::get('rejectCustomer/{mobileNumber}', [CustomerController::class, 'rejectCustomer'])->middleware('operator')->name('rejectCustomer');


        Route::get('firstLoad', [FirstLoadController::class, 'index'])->middleware('operator')->name('firstLoad.index');

        Route::put('firstLoad/{firstLoad}', [FirstLoadController::class, 'update'])->middleware('operator')->name('firstLoad.update');


        // لیست صاحب بارها به ترتیب بیشترین بار
        Route::get('getCargoOwnersList', [ReportingController::class, 'getCargoOwnersList'])->middleware('admin');

        // گزارش نصب رانندگان از 30 روز گذشته
        Route::get('driverInstallationInLast30Days', [ReportingController::class, 'driverInstallationInLast30Days'])->middleware('operator');

        // گزارش پرداخت ها
        Route::get('paymentReport/{userType}/{status}', [ReportingController::class, 'paymentReport'])->middleware('operator');

        Route::post('paymentReport/view-pdf', [ReportingController::class, 'viewPDF'])->name('view-pdf');

        Route::get('unSuccessPeyment', [ReportingController::class, 'unSuccessPeyment'])->middleware('operator')->name('unSuccessPeyment.driver');

        // گزارش بیشترین پرداخت رانندگان
        Route::get('mostPaidDriversReport', [ReportingController::class, 'mostPaidDriversReport'])->middleware('operator');

        // گزارش پرداخت براساس ناوگان
        Route::get('paymentByFleetReport', [ReportingController::class, 'paymentByFleetReport'])->middleware('operator');

        // گزارش پرداختی رانندگان
        Route::match(['get', 'post'], 'driversPaymentReport', [ReportingController::class, 'driversPaymentReport'])
            ->middleware('operator')
            ->name('drivers-payment-report');

        /*******************************************************************************************************************/
        // ثبت بار دسته ای
        // فرم ثبت بار (بررسی و ثبت)
        Route::get('storeCargoConvertForm', [DataConvertController::class, 'storeCargoConvertForm'])->middleware('operator');

        Route::get('finalApprovalAndStoreCargo', [DataConvertController::class, 'finalApprovalAndStoreCargo'])->middleware('operator');

        Route::get('smartStoreCargo', [DataConvertPlusController::class, 'smartStoreCargo'])->name('admin.smartStoreCargo')->middleware('operator');

        Route::get('removeCargoFromCargoList/{cargo}', [DataConvertController::class, 'removeCargoFromCargoList'])->middleware('operator');


        Route::post('updateCargoInfo/{cargo}', [DataConvertController::class, 'updateCargoInfo'])->middleware('operator');


        Route::post('storeCargoInformation', [DataConvertController::class, 'storeCargoInformation'])->middleware('operator');

        Route::post('dataConvert', [DataConvertController::class, 'dataConvert'])->middleware('operator');

        Route::post('storeMultiCargo/{cargo}', [DataConvertController::class, 'storeMultiCargo'])->middleware('operator');

        Route::post('storeMultiCargoSmart/{cargo}', [DataConvertPlusController::class, 'storeMultiCargoSmart'])->middleware('operator');

        // دیکشنری کلمات معادل در ثبت بار
        Route::get('dictionary', [DataConvertController::class, 'dictionary'])->middleware('operator');
        Route::post('addWordToDictionary', [DataConvertController::class, 'addWordToDictionary'])->middleware('operator');

        Route::delete('removeDictionaryWord/{dictionary}', [DataConvertController::class, 'removeDictionaryWord'])->middleware('operator')->name('removeDictionaryWord');

        // مدیریت کانال ها
        Route::get('channels', [DataConvertController::class, 'channels'])->middleware('operator');
        Route::get('removeChannel/{channel}', [DataConvertController::class, 'removeChannel'])->middleware('operator');
        Route::post('newChannel', [DataConvertController::class, 'newChannel'])->middleware('operator');
        Route::get('channelsData', [DataConvertController::class, 'channelsData'])->middleware('operator');

        // لیست بارهای رد شده
        Route::get('rejectedCargoFromCargoList', [DataConvertController::class, 'rejectedCargoFromCargoList']);

        // بار های تکراری
        Route::get('duplicateCargoFromCargoList', [DataConvertController::class, 'duplicateCargoFromCargoList'])->name('duplicateCargoFromCargoList');

        // بار های تکراری
        Route::resource('fleetlessNumber', FleetlessNumberController::class)->middleware('operator');

        // استعلام کرایه حمل
        Route::resource('freightInquiries', FreightInquiryController::class)->middleware('operator');

        // جستجو بار های رد شده
        Route::get('searchRejectCargo', [DataConvertController::class, 'searchRejectCargo'])->name('searchRejectCargo');

        Route::get('allRejectedCargoCount', [DataConvertController::class, 'allRejectedCargoCount'])->name('allRejectedCargoCount');
        Route::get('rejectCargoCount', [DataConvertController::class, 'rejectCargoCount'])->name('rejectCargoCount');

        // تعیین دسترسی اپراتور ها به بارها براساس ناوگان، شهر و...
        Route::post('operatorCargoListAccess/{user}', [DataConvertController::class, 'operatorCargoListAccess']);

        /*******************************************************************************************************************/
        // گزارش تماس با صاحبان بار و باربری ها
        Route::get('contactReportWithCargoOwners/{mobileNumber?}', [OperatorContactingController::class, 'contactReportWithCargoOwners'])->middleware('operator');


        // ثبت شماره جدید
        Route::post('soreNewMobileNumberOfCargoOwner', [OperatorContactingController::class, 'soreNewMobileNumberOfCargoOwner'])->middleware('operator');

        // ذخیره نام و نام خانوادگی
        Route::post('storeContactCargoOwnerNameAndLastname/{contactReportWithCargoOwner}', [OperatorContactingController::class, 'storeContactCargoOwnerNameAndLastname'])->middleware('operator');

        // ذخیره نتیجه تماس
        Route::post('storeContactReportWithCargoOwnerResult', [OperatorContactingController::class, 'storeContactReportWithCargoOwnerResult'])->middleware('operator');

        // حذف گزارش تماس با صاحب بار
        Route::get('deleteContactReportWithCargoOwners/{contactReportWithCargoOwner}', [OperatorContactingController::class, 'deleteContactReportWithCargoOwners'])->middleware('admin');

        /************************************************************************************************************/

        // تماس با رانندگان
        Route::get('contactingWithDrivers', [OperatorContactingController::class, 'contactingWithDrivers'])->middleware('operator');

        // تماس با رانندگان
        Route::get('freeCallDriver', [FreeCallDriverController::class, 'index'])->name('admin.freeCallDriver.index')->middleware('operator');

        Route::resource('freeSubscription', FreeSubscriptionController::class)->middleware('operator');

        // گزارش بر اساس ثبت بار دستی اپراتور
        Route::resource('storeCargoOperator', StoreCargoOperatorController::class)->middleware('operator');

        // ارسال نوتیفیکیشن شخصی سازی شده
        Route::resource('personalizedNotification', PersonalizedNotificationController::class)->middleware('operator');
        Route::get('requestForApprovalNotification/{personalizedNotification}', [PersonalizedNotificationController::class, 'requestForApproval'])
            ->middleware('operator')
            ->name('admin.requestForApproval');


        // اعلان ها
        Route::resource('web-notification', WebNotificationController::class)->middleware('operator');

        Route::post('searchFreeSubscription', [FreeSubscriptionController::class, 'search'])->name('search.free.subscription')->middleware('operator');

        // استفاده کننده به تفکیک شهرستان
        Route::get('usersByCity', [ReportingController::class, 'usersByCity'])->middleware('operator')->name('reporting.usersByCity');
        Route::post('searchUsersByCity', [ReportingController::class, 'searchUsersByCity'])->middleware('operator')->name('reporting.searchUsersByCity');
        Route::get('usersByCity/{provinceCity}', [ReportingController::class, 'usersByCustomCities'])->middleware('operator')->name('reporting.usersByCustomCities');

        // استفاده کننده به تفکیک استان
        Route::get('usersByProvince', [ReportingController::class, 'usersByProvince'])->middleware('operator')->name('reporting.usersByProvince');
        Route::post('searchUsersByProvince', [ReportingController::class, 'searchUsersByProvince'])->middleware('operator')->name('reporting.searchUsersByProvince');
        Route::get('usersByProvince/{provinceCity}', [ReportingController::class, 'usersByCustomProvinces'])->middleware('operator')->name('reporting.usersByCustomProvinces');

        // نتیجه تماس ها
        Route::get('contactingWithDriverResult/{driver}', [OperatorContactingController::class, 'contactingWithDriverResult'])->middleware('operator')->name('contactingWithDriverResult');


        // Route::resource('report', ReportController::class)->middleware('operator');

        Route::get('report/{type}', [ReportController::class, 'index'])
            ->name('report.index')
            ->middleware('operator');

        Route::put('report/{report}', [ReportController::class, 'update'])
            ->name('report.update')
            ->middleware('operator');

        // ذخیره نتیجه تماس با راننده
        Route::post('storeContactReportWithDriver', [OperatorContactingController::class, 'storeContactReportWithDriver'])->middleware('operator');

        /**********************************************************************************************************/
        // لیست ناوگان انتخابی اپراتور ها
        Route::get('operatorFleets', [AdminFleetController::class, 'operatorFleets'])->middleware('operator');

        // ذخیره ناوگان انتخاب شده
        Route::post('updateOperatorFleets', [AdminFleetController::class, 'updateOperatorFleets'])->middleware('operator');

        //  گزارش بار ها به تفکیک ناوگان
        Route::get('cargo-fleets', [ReportingController::class, 'cargoFleetsReport'])->middleware('operator')->name('report.cargo.fleets');

        Route::get('cargo-fleets-state/{fleet}', [ReportingController::class, 'searchCargoFleetsReport'])->middleware('operator')->name('report.cargo.fleets.search');

        Route::get('cargo-fleets-city/{fleet}/{origin_state}', [ReportingController::class, 'searchCargoFleetsReportCity'])
            ->middleware('operator')
            ->name('search.fleets.city');

        Route::get('loadByFleetCity/{fleet_id}/{originCity_id}', [LoadController::class, 'loadByFleetCity'])
            ->middleware('operator')
            ->name('load.fleet.city');

        // جستجو بار ها به تفکیک ناوگان
        Route::post('searchCargoFleets', [ReportingController::class, 'searchCargoFleets'])->middleware('operator')->name('search.report.cargo.fleets');


        /************************************************************************************************************/
        /************************************************************************************************************/

        // احراز هویت راننده توسط اپراتور
        Route::get('driversAuthenticationByOperator', [DriverController::class, 'driversAuthenticationByOperator'])->middleware('operator')->name('driver.auth.operator');

        // احراز هویت صاحبان بار
        Route::resource('ownerAuth', OwnerAuthController::class);

        // صاحبان بار تایید نشده
        Route::get('ownerReject', [OwnerAuthController::class, 'ownerReject'])->middleware('operator')->name('owner.reject');

        Route::get('ownerRejected', [OwnerAuthController::class, 'ownerRejected'])->middleware('operator')->name('owner.ownerRejected');

        Route::get('ownerAccept', [OwnerAuthController::class, 'ownerAccept'])->middleware('operator')->name('owner.accept');

        Route::put('updateAuthOwner/{owner}', [OwnerAuthController::class, 'updateAuthOwner'])->middleware('operator')->name('owner.updateAuthOwner');


        // حذف فایلهای ارسالی راننده توسط اپراتور
        Route::get('removeDriverFile/{fileType}/{driver}', [DriverController::class, 'removeDriverFile'])->middleware('operator');

        /*************************************************************************************************** */
        // خدمات
        Route::resource('services', ServiceController::class)->middleware('operator');

        // قوانین و مقررات
        Route::resource('ruleRegulation', RuleRegulationController::class)->middleware('operator');


        // درگاه پرداخت
        Route::resource('bank', BankController::class)->middleware("operator");
    });


    /* ******************************************************************************
     * ******************************************************************************
     * ***************************************************************************** */


    Route::get('/home', [HomeController::class, 'index'])->name('home');

    Route::get('cargoConvertLists', [DataConvertController::class, 'cargoConvertLists'])->middleware('operator')->name('delete.duplicate');

    Route::get('insertStates', function () {
        $para = new ParameterController();

        $para->setArrayOfStates();
        $array = $para->getArrayOfStates();

        for ($i = 1; $i <= count($array); $i++) {
            $state = new State();
            $state->name = $array[$i];
            $state->country_id = 1;
            $state->save();
        }
    });

    Route::get('insertCities/{state_id}', function ($state_id) {
        $para = new ParameterController();
        $para->setArrayOfCities($state_id);
        $array = $para->getArrayOfCities();
        for ($i = 1; $i <= count($array); $i++) {
            $city = new City();
            $city->name = $array[$i];
            $city->state_id = $state_id;
            $city->save();
        }
    });


    // شارژ کیف پول
    Route::get('increaseWalletCharge/{amount}/{user_id}/{userType}', function ($amount, $user_id, $userType) {
        $p = new PayController();
        return $p->bpPayRequest($amount, $user_id, $userType);
    });

    // شارژ کیف پول راننده
    Route::get('increaseDriverWalletCharge/{driver_id}', function ($driver_id) {
        $p = new PayController();
        return $p->bpPayRequest($p->expenseForDriver(), $driver_id, 'driver');
    });

    Route::get('increaseDriverWalletChargeGibar/{driver_id}', function ($driver_id) {
        $p = new PayController();
        //    return $p->bpPayRequest($p->expenseForDriver(), $driver_id, 'driver_gibar');
        return $p->bpPayRequest(100, $driver_id, 'driver_gibar');
    });

    Route::post('payCallBack', [PayController::class, 'bpVerifyRequest']);

    // پرداخت از طریق وب
    Route::post('chargeWallet', [PayController::class, 'chargeWallet']);

    // ادرس بازگشت به کیف پول
    Route::post('payCallBackWeb', [PayController::class, 'payCallBackWeb']);


    Route::get('registerCustomer', function () {
        return view('auth.registerCustomer');
    });

    /****************************************************************************************************************/
    // عملیات بانکی و پرداخت

    // شارژ کیف پول
    Route::get('chargeWallet/{amount}/{bearing_id}/{userType}', [PayController::class, 'pay']);
    Route::get('dashboard', [HomeController::class, 'dashboard']);

    // تایید عملیات
    Route::get('verify', [PayController::class, 'verify']);

    // پرداخت شارژ ماهیانه
    Route::get('payMonthlyCharge/{user_id}/{userType}', [PayController::class, 'payMonthlyCharge']);

    // تایید عملیات پرداخت شارژ ماهیانه
    Route::get('verifyMonthlyCharge', [PayController::class, 'verifyMonthlyCharge']);

    /****************************************************************************************************************/

    // درگاه پرداخت زرین پال
    Route::get('payDriver/{packageName}/{driver}', [PayController::class, 'payDriver']);
    Route::get('verifyDriverPay', [PayController::class, 'verifyDriverPay']);

    // درگاه پرداخت زیبال
    Route::get('payDriverZibal/{packageName}/{driver}', [PayController::class, 'payDriverZibal']);
    Route::get('verifyDriverPayZibal', [PayController::class, 'verifyDriverPayZibal']);

    // درگاه پرداخت سینا
    Route::get('payDriverSina/{packageName}/{driver}', [PayController::class, 'payDriverSina']);
    Route::post('verifyDriverPaySina', [PayController::class, 'verifyDriverPaySina']);

    /****************************************************************************************************************/

    Route::get('payCustomer/{packageName}/{customer}/{action?}', [PayController::class, 'payCustomer']);
    // تایید عملیات
    Route::get('verifyCustomerPay', [PayController::class, 'verifyCustomerPay']);

    /******************************************************************************************************************/

    // پرداخت هزینه کنترل ناوگان
    Route::get('fleetControlPay/{numOfFleetControl}/{userType}/{user_id}', [PayController::class, 'fleetControlPay']);
    Route::get('verifyFleetControlPay', [PayController::class, 'verifyFleetControlPay']);
    Route::get('dashboard', [HomeController::class, 'dashboard']);

    /******************************************************************************************************************/
});
