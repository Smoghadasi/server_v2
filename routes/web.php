<?php

use App\Http\Controllers\AccountingController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BearingController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\FreeSubscriptionController;
use App\Http\Controllers\ContactUsController;
use App\Http\Controllers\DataConvertController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\FleetController as AdminFleetController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoadController;
use App\Http\Controllers\OperatorContactingController;
use App\Http\Controllers\ParameterController;
use App\Http\Controllers\PayController;
use App\Http\Controllers\ReportingController;
use App\Http\Controllers\ServiceController;
// use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SOSController;
use App\Http\Controllers\UserController;

use App\Models\City;
use App\Models\Load;
use App\Models\State;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
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


Route::post('/check-mobile', [AuthController::class, 'checkMobile'])->name('check.mobile');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('user.resetPassword');

Route::post('check-user', [AuthController::class, 'checkUser'])->name('check.user');


Route::get('/conf', function () {
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
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


        // صفر کردن دیتا ها
        Route::get('zeroData', [DriverController::class, 'zeroData'])->middleware('operator')->name('driver.zeroData');

        Route::get('appVersions', [HomeController::class, 'appVersions']);
        Route::post('storeAppVersions', [HomeController::class, 'storeAppVersions']);

        // شهرها و استان ها
        Route::get('provincesAndCities', [AddressController::class, 'provincesAndCities'])->middleware('operator');

        // لیست شهرهای استان
        Route::get('provinceCitiesList/{province_id}', [AddressController::class, 'provinceCitiesList'])->middleware('operator');

        // ثبت شهر جدید
        Route::post('addNewCity/{state}', [AddressController::class, 'addNewCity'])->middleware('operator');

        // حذف شهر
        Route::get('removeCity/{city}', [AddressController::class, 'removeCity'])->middleware('admin');


        Route::get('centerOfProvince/{city}', [AddressController::class, 'centerOfProvince'])->middleware('admin');

        /***************************************************************************************************/
        /***************************************************************************************************/

        Route::match(['post', 'get'], 'driversActivities/{date?}', [DriverController::class, 'driversActivities'])->middleware('operator');

        // تغییر وضعیت آپشن های سایت
        Route::get('changeSiteOption/{option}', [HomeController::class, 'changeSiteOption'])->middleware('operator');

        // خروج از بخش کاربری
        Route::get('logout', [UserController::class, 'logout'])->middleware('operator');

        // نمایش لیست اپراتور ها
        Route::get('operators', [UserController::class, 'operators'])->middleware('admin');

        // فرم افزودن اپراتور جدید
        Route::get('addNewOperatorForm', [UserController::class, 'addNewOperatorForm'])->middleware('admin');

        // افزودن اپراتور جدید
        Route::post('addNewOperator', [RegisterController::class, 'addNewOperator'])->middleware('admin');

        // اطلاعات اپراتور
        Route::get('operatorInfo/{user_id}', [UserController::class, 'operatorInfo'])->middleware('admin');;

        // تغییر وضعیت اپراتور
        Route::get('changeOperatorStatus/{user}', [UserController::class, 'changeOperatorStatus'])->middleware('admin');;

        // حذف اپراتور
        Route::get('removeOperator/{user}', [UserController::class, 'removeOperator'])->middleware('admin');

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
        Route::get('bearingLoads/{id}', [LoadController::class, 'bearingLoads'])->middleware('operator');

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

        // بارهای مشتریان
        Route::get('customerLoads/{customer_id}', [LoadController::class, 'customerLoads'])->middleware('operator');

        // بار های ثبت شده
        Route::get('loadBackup', [LoadController::class, 'loadBackup'])->middleware('operator')->name('admin.loadBackup');

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
        Route::get('searchLoadsForm', [LoadController::class, 'searchLoadsForm'])->middleware('operator');
        Route::post('searchLoads', [LoadController::class, 'searchLoads'])->middleware('operator');

        // نمایش اطلاعات بار
        Route::get('loadInfo/{load_id}', [LoadController::class, 'loadInfo'])->middleware('operator');

        // فرم افزودن بار جدید
        Route::get('addNewLoadForm/{userType}', [LoadController::class, 'addNewLoadForm'])->middleware('operator');

        // لیست تایید بار
        Route::get('acceptCargo', [LoadController::class, 'acceptCargo'])->middleware('operator')->name('accept.cargo.index');
        Route::post('acceptCargo/{id}', [LoadController::class, 'acceptCargoStore'])->middleware('admin')->name('accept.cargo.store');

        // فرم ویرایش اطلاعات بار
        Route::get('editLoadInfoForm/{load_id}', [LoadController::class, 'editLoadInfoForm'])->middleware('operator');

        // ویرایش اطلاعات بار
        Route::post('editLoadInfo/{load}', [LoadController::class, 'editLoadInfoInWeb'])->middleware('operator');

        // حذف اطلاعات بار
        Route::get('removeLoadInfo/{load_id}', [LoadController::class, 'removeLoadInfo'])->middleware('operator');

        // حذف اطلاعات دسته ای بار
        Route::post('removeLoad', [LoadController::class, 'removeLoad'])->middleware('operator');

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
        // فرم ثبت راننده جدید
        Route::get('addNewDriverForm', [DriverController::class, 'addNewDriverForm'])->middleware('operator');

        // افزودن راننده جدید
        Route::post('addNewDriver', [DriverController::class, 'addNewDriver'])->middleware('operator');

        // نمایش اطلاعات راننده
        Route::get('driverInfo/{driver}', [DriverController::class, 'driverInfo'])->middleware('operator');

        // تغییر راننده به فعال یا غیر فعال
        Route::get('changeDriverStatus/{driver_id}', [DriverController::class, 'changeDriverStatus'])->middleware('operator');

        // ویرایش راننده
        Route::get('editDriver/{driver}', [DriverController::class, 'editDriverForm'])->middleware('operator');
        Route::post('editDriver/{driver}', [DriverController::class, 'editDriver'])->middleware('operator');
        Route::put('updateAuthLevel/{driver}', [DriverController::class, 'updateAuthLevel'])->middleware('operator')->name('driver.updateAuthLevel');


        // جستجوی راننده
        Route::post('searchDrivers', [DriverController::class, 'searchDrivers'])->middleware('operator');
        Route::get('searchDrivers', function () {
            return redirect('admin/drivers');
        });

        Route::post('searchDriverCall', [DriverController::class, 'searchDriverCall'])->middleware('operator');
        Route::get('searchDriverCall', function () {
            return redirect('admin/contactingWithDrivers');
        });

        //حذف راننده
        Route::get('removeDriver/{driver}', [DriverController::class, 'removeDriver'])->middleware('admin');

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

        // ذخیره پاسخ ادمین برای شکایت باربری
        Route::post('storeComplaintCustomerAdminMessage/{complaintCustomer}', [ComplaintController::class, 'storeComplaintCustomerAdminMessage'])->middleware('operator');

        /***************************************************************************************************/
        Route::get('profile', [UserController::class, 'adminProfile'])->middleware('operator')->name('user.edit');

        Route::post('restPassword/{user}', [UserController::class, 'restPassword'])->middleware('operator');

        /**************************************************************************************************************/
        //  شماره تلفن های لیست ممنوعه

        Route::get('blockedPhoneNumbers', [UserController::class, 'blockedPhoneNumbers'])->middleware('operator');
        Route::post('blockPhoneNumber', [UserController::class, 'blockPhoneNumber'])->middleware('operator');
        Route::get('unblockPhoneNumber/{phoneNumber}', [UserController::class, 'unblockPhoneNumber'])->middleware('operator');

        /**************************************************************************************************************/
        // آی پی های مسدود
        Route::get('blockedIps', [UserController::class, 'blockedIps'])->middleware('admin');

        // مسدود کردن آی پی
        Route::get('blockUserIp/{user_id}/{userType}/{ip}', [UserController::class, 'blockUserIp'])->middleware('admin');

        Route::get('unBlockUserIp/{user_id}/{userType}', [UserController::class, 'unBlockUserIp'])->middleware('admin');


        /*******************************************************************************************************/
        // گزارش گیری
        /*******************************************************************************************************/

        // گزارش فعالیت اپراتورها
        Route::get('operatorsActivityReport', [ReportingController::class, 'operatorsActivityReport'])->middleware('admin');

        // گزارش میزان ساعت کار اپراتور ها
        Route::match(['get', 'post'], 'operatorsWorkingHoursActivityReport', [ReportingController::class, 'operatorsWorkingHoursActivityReport'])->middleware('operator');

        // ذخیره نسبت ها
        Route::get('storeFleetRatioToDriverActivityReportData', [ReportingController::class, 'storeFleetRatioToDriverActivityReportData'])->middleware('operator');


        // گزارش جدول نسبت ناوگان (کل و فعال) به بار
        Route::match(['get', 'post'], 'fleetRatioToDriverActivityReport', [ReportingController::class, 'fleetRatioToDriverActivityReport'])->middleware('admin');


        // خلاصه گزارش روز
        Route::get('summaryOfDaysReport', [ReportingController::class, 'summaryOfDaysReport'])->middleware('admin');

        // گزارش فعالیت رانندگان
        Route::get('driverActivityReport', [ReportingController::class, 'driverActivityReport'])->middleware('operator');

        // نمودار فعالیت رانندگان بر اساس تماس
        Route::get('driversContactCall', [ReportingController::class, 'driversContactCall'])->name('report.driversContactCall');

        // گزارش فعالیت باربری ها
        Route::get('transportationCompaniesActivityReport', [ReportingController::class, 'transportationCompaniesActivityReport'])->middleware('admin');

        // گزارش فعالیت باربری ها
        Route::get('cargoOwnersActivityReport', [ReportingController::class, 'cargoOwnersActivityReport'])->middleware('admin');

        // گزارشهای ترکیبی
        Route::get('combinedReports', [ReportingController::class, 'combinedReports'])->middleware('admin');

        // لیست صاحب بارها به ترتیب بیشترین بار
        Route::get('getCargoOwnersList', [ReportingController::class, 'getCargoOwnersList'])->middleware('admin');

        // گزارش نصب رانندگان از 30 روز گذشته
        Route::get('driverInstallationInLast30Days', [ReportingController::class, 'driverInstallationInLast30Days'])->middleware('admin');

        // گزارش پرداخت ها
        Route::get('paymentReport/{userType}/{status}', [ReportingController::class, 'paymentReport'])->middleware('operator');

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

        Route::get('removeCargoFromCargoList/{cargo}', [DataConvertController::class, 'removeCargoFromCargoList'])->middleware('operator');


        Route::post('updateCargoInfo/{cargo}', [DataConvertController::class, 'updateCargoInfo'])->middleware('operator');


        Route::post('storeCargoInformation', [DataConvertController::class, 'storeCargoInformation'])->middleware('operator');

        Route::post('dataConvert', [DataConvertController::class, 'dataConvert'])->middleware('operator');

        Route::post('storeMultiCargo/{cargo}', [DataConvertController::class, 'storeMultiCargo'])->middleware('operator');

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

        Route::resource('freeSubscription', FreeSubscriptionController::class)->middleware('operator');

        // نتیجه تماس ها
        Route::get('contactingWithDriverResult/{driver}', [OperatorContactingController::class, 'contactingWithDriverResult'])->middleware('operator');

        // ذخیره نتیجه تماس با راننده
        Route::post('storeContactReportWithDriver', [OperatorContactingController::class, 'storeContactReportWithDriver'])->middleware('operator');

        /**********************************************************************************************************/
        // لیست ناوگان انتخابی اپراتور ها
        Route::get('operatorFleets', [AdminFleetController::class, 'operatorFleets'])->middleware('operator');

        // ذخیره ناوگان انتخاب شده
        Route::post('updateOperatorFleets', [AdminFleetController::class, 'updateOperatorFleets'])->middleware('operator');

        //  گزارش بار ها به تفکیک ناوگان
        Route::get('cargo-fleets', [ReportingController::class, 'cargoFleetsReport'])->middleware('operator')->name('report.cargo.fleets');


        /************************************************************************************************************/
        /************************************************************************************************************/

        // احراز هویت راننده توسط اپراتور
        Route::get('driversAuthenticationByOperator', [DriverController::class, 'driversAuthenticationByOperator'])->middleware('operator');

        // حذف فایلهای ارسالی راننده توسط اپراتور
        Route::get('removeDriverFile/{fileType}/{driver}', [DriverController::class, 'removeDriverFile'])->middleware('operator');

        /*************************************************************************************************** */
        // خدمات
        Route::resource('services', ServiceController::class)->middleware('operator');
    });

    // مسیریابی های مربوط به صاحبان بار، رانندگان ، باربری ها و بازارابها
    Route::group(['prefix' => 'user'], function () {

        Route::get('status', function () {
            $message = 'کاربر گرامی لطفا تا برقراری تماس از طرف کارشناسان ما منتظر بمانید. باتشکر گروه پشتیبانی کیانسه';
            $alert = 'alert-warning';

            return view('users.alert', compact('message', 'alert'));
        });

        // نمایش بارها
        Route::get('/', [UserController::class, 'displayUsersDashboard']);

        Route::get('/myLoads', function () {
            $bearing_id = \auth('bearing')->id();
            $loads = Load::where('bearing_id', $bearing_id)
                ->orderBy('id', 'desc')
                ->get();
            return view('users.loads', compact('bearing_id', 'loads'));
        })->middleware('userStatus');

        Route::get('newLoads', [LoadController::class, 'requestNewLoadsForBearingInWeb'])->middleware('userStatus');


        // فرم ورود
        Route::get('login', function () {
            return view('auth.sendMobileNumberOfUserToLogin');
        });

        // ارسال شماره تلفن برای ورود (باربری، صاحب بار، راننده، بازاریاب)
        Route::post('sendActivationCode', [LoginController::class, 'sendActivationCode']);

        // ارسال کد فعلا سازی
        Route::post('validateActivationCode', [LoginController::class, 'validateActivationCode']);

        // فرم افزودن بار جدید
        //    Route::get('addNewLoadForm', function () {
        //        $cities = City::all();
        //        $fleets = Fleet::where('parent_id', [>', 0)->orderby('parent_id', [asc'])->get();
        //        $packingTypes = PackingType::get();
        //        $message = [];
        //        return view('users.addNewLoadForm', compact('message', [cities', [fleets', [packingTypes'));
        //    })->middleware('userStatus');;
        Route::get('addNewLoadForm/{storeFor?}', [LoadController::class, 'addNewLoadForm'])->middleware('userStatus');

        Route::get('createNewLoadForm/{storeFor?}', [LoadController::class, 'createNewLoadForm'])->middleware('userStatus');

        // ثبت بار در وب
        Route::post('createNewLoad', [LoadController::class, 'createNewLoadInWeb'])->middleware('userStatus');


        // نمایش اطلاعات بار
        Route::get('loadInfo/{load_id}', [LoadController::class, 'loadInfoForUser'])->middleware('userStatus');

        // کیف پول
        Route::get('wallet', [UserController::class, 'wallet'])->middleware('userStatus');

        // افزودن باربری جدید
        Route::post('addNewBearing', [BearingController::class, 'addNewBearingOnWeb']);

        // خروج
        Route::get('logout', function () {
            if (\auth('bearing')->check())
                \auth('bearing')->logout();
            if (\auth('customer')->check())
                \auth('customer')->logout();
            if (\auth('driver')->check())
                \auth('driver')->logout();
            if (\auth('marketer')->check())
                \auth('marketer')->logout();

            return redirect(url('user'));
        });

        // نمایش پروفایل باربری، راننده، مشتری و بازاریاب
        Route::get('profile', [UserController::class, 'profile'])->middleware('userStatus');

        // درخواست راننده
        Route::get('requestDriver/{load_id}', [DriverController::class, 'requestDriverForm'])->middleware('userStatus');


        Route::post('requestDriver', [LoadController::class, 'requestDriver'])->middleware('userStatus');


        // ثبت نام مشتری
        Route::post('registerCustomer', [RegisterController::class, 'registerCustomerInWeb']);

        Route::get('registerCustomer', function () {
            $mobileNumber = session()->get('mobileNumber');
            return view('auth.registerCustomer', compact('mobileNumber'));
        });

        // ثبت نام باربری
        Route::post('registerBearing', [RegisterController::class, 'registerBearingInWeb']);

        Route::get('registerBearing', function () {
            $mobileNumber = session()->get('mobileNumber');
            $cities = City::select('id', ['name', 'state'])->get();
            return view('auth.registerBearing', compact('mobileNumber', 'cities'));
        });


        // ثبت نام رننده
        //    Route::post('registerBearing', [RegisterController::class, 'registerDriverInWeb');

        Route::get('registerDriver', function () {
            $mobileNumber = session()->get('mobileNumber');
            return view('auth.registerDriver', compact('mobileNumber'));
        });

        /************************************************************************************************************/
        // ثبت قیمت در مناقصه
        Route::post('suggestionPrice', [TenderController::class, 'suggestionPriceInWeb'])->middleware('userStatus');

        // انتخاب باربری برای بار توسط صاحب بار
        Route::post('selectBearingForLoad', [LoadController::class, 'selectBearingForLoadInWeb'])->middleware('userStatus');

        /******************************************************************************************************************/
        // دریافت لیست بارهای مشتری
        Route::get('getCustomerLoadsList', [LoadController::class, 'getCustomerLoadsList']);

        /**************************************************************************************************************/
        Route::get('userCriticismOrComplaints', [ComplaintController::class, 'userCriticismOrComplaints']);

        // انتقاد یا شکایت صاحب بار
        Route::post('storeComplaintCustomerInWeb', [ComplaintController::class, 'storeComplaintCustomerInWeb']);

        Route::post('storeComplaintTransportationCompanyInWeb', [ComplaintController::class, 'storeComplaintTransportationCompanyInWeb']);
    });

    /* ******************************************************************************
     * ******************************************************************************
     * ***************************************************************************** */


    Route::get('/home', [HomeController::class, 'index'])->name('home');


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

    Route::get('payDriver/{packageName}/{driver}', [PayController::class, 'payDriver']);
    // تایید عملیات
    Route::get('verifyDriverPay', [PayController::class, 'verifyDriverPay']);

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
