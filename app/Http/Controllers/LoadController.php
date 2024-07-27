<?php

namespace App\Http\Controllers;

use App\Event\PostCargoNotificationEvent;
use App\Event\PostCargoSmsEvent;
use App\Http\Requests\NewLoadRequest;
use App\Jobs\SendSmsJob;
use App\Models\Bearing;
use App\Models\BlockedIp;
use App\Models\CityDistanceCalculate;
use App\Models\BlockPhoneNumber;
use App\Models\CargoReportByFleet;
use App\Models\City;
use App\Models\Customer;
use App\Models\DateOfCargoDeclaration;
use App\Models\Driver;
use App\Models\DriverActivity;
use App\Models\DriverCall;
use App\Models\DriverLoad;
use App\Models\DriverVisitLoad;
use App\Models\Fleet;
use App\Models\FleetLoad;
use App\Models\FleetOperator;
use App\Models\Inquiry;
use App\Models\Load;
use App\Models\LoadBackup;
use App\Models\LoadStatus;
use App\Models\LoadType;
use App\Models\Owner;
use App\Models\PackingType;
use App\Models\ProvinceCity;
use App\Models\Score;
use App\Models\Setting;
use App\Models\Tender;
use App\Models\TenderStart;
use App\Models\User;
use App\Models\UserActivityReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PHPUnit\Exception;
use Illuminate\Support\Facades\Validator;

class LoadController extends Controller
{
    // نمایش لیست نوع بارها
    public function loadType($message = '')
    {
        $loadTypeParents = LoadType::where('parent_id', 0)->get();
        $loadTypes = LoadType::where('parent_id', '>', 0)->orderby('parent_id', 'asc')->get();
        return view('admin.loadType', compact('loadTypes', 'loadTypeParents', 'message'));
    }

    // درخواست عنوان نوع بار
    public static function getLoadTypeTitle($id)
    {
        $loadType = LoadType::where('id', $id)->first();
        return $loadType->title;
    }

    // فرم افزودن نوع بار
    public function addNewLoadTypeForm($message = '')
    {
        $loadTypes = LoadType::where('parent_id', 0)->get();
        return view('admin.addNewLoadTypeForm', compact('loadTypes', 'message'));
    }

    // افزودن بار جدید
    public function addNewLoadType(Request $request)
    {
        $rules = ['title' => 'required'];
        $message = ['required' => 'عنوان نوع بار می بایست وارد شود'];
        $this->validate($request, $rules, $message);

        $loadType = new LoadType();
        $loadType->title = $request->title;
        $loadType->parent_id = $request->parent_id;
        $loadType->save();

        return $this->addNewLoadTypeForm('نوع بار جدید با عنوان ' . $request->title . ' افزوده شد. ');
    }

    // نمایش فرم ویرایش نوع بار
    public function editLoadTypeForm($id)
    {
        $loadType = LoadType::where('id', $id)->first();
        $loadTypeParents = LoadType::where('parent_id', 0)->get();
        return view('admin.editLoadTypeForm', compact('loadType', 'loadTypeParents'));
    }

    // ویرایش نوع بار
    public function editLoadType(Request $request)
    {
        $rules = ['title' => 'required'];
        $message = ['required' => 'عنوان نوع بار می بایست وارد شود'];
        $this->validate($request, $rules, $message);

        $id = $request->id;

        $loadType = LoadType::find($id);
        $loadType->title = $request->title;
        $loadType->parent_id = $request->parent_id;
        $loadType->save();

        $loadType = LoadType::where('id', $id)->first();
        $loadTypeParents = LoadType::where('parent_id', 0)->get();
        $message = 'ویرایش انجام شد';
        return view('admin.editLoadTypeForm', compact('loadType', 'loadTypeParents', 'message'));
    }

    // حذف نوع بار
    public function deleteLoadType($id)
    {
        LoadType::where('id', $id)->delete();
        return $this->loadType('نوع بار مورد نظر حذف شد');
    }

    // ثبت بار در وب
    public function createNewLoadInWeb(Request $request)
    {
        $request->weight = $this->convertNumbers(str_replace(',', '', $request->weight), false);
        $request->width = $this->convertNumbers(str_replace(',', '', $request->width), false);
        $request->length = $this->convertNumbers(str_replace(',', '', $request->length), false);
        $request->height = $this->convertNumbers(str_replace(',', '', $request->height), false);
        $request->insuranceAmount = $this->convertNumbers(str_replace(',', '', $request->insuranceAmount), false);
        $request->numOfFleets = $this->convertNumbers(str_replace(',', '', $request->numOfFleets), false);
        $request->loadingDate = $this->convertNumbers($request->loadingDate, false);

        if (\auth('customer')->check())
            $request->user_id = \auth('customer')->id();
        else if (\auth('bearing')->check())
            $request->user_id = \auth('bearing')->id();


        if (isset($request->suggestedPrice) && strlen($request->suggestedPrice))
            $request->suggestedPrice = $this->convertNumbers(str_replace(',', '', $request->suggestedPrice), false);
        else
            $request->suggestedPrice = 0;

        $request->tenderTimeDuration = 0;

        $request->fleetList = json_decode($request->fleetList, true);
        $result = $this->createNewLoad($request);

        if ($result['result'] == SUCCESS)
            if (auth()->check())
                return redirect(url('admin/loadInfo/' . $result['load_id']));
            else
                return redirect(url('user/loadInfo/' . $result['load_id']));

        $message = $result['message'];

        if ($result['result'] == SUCCESS)
            return back()->with('success', "بار مورد نظر ثبت شد");

        return back()->with('danger', $message[1]);

        return $this->addNewLoadForm($userType, $message);
    }

    // ثبت بار توسط ادمین
    public function createNewLoadByAdmin(Request $request)
    {
        $request->weight = $this->convertNumbers(str_replace(',', '', $request->weight), false);
        $request->width = $this->convertNumbers(str_replace(',', '', $request->width), false);
        $request->length = $this->convertNumbers(str_replace(',', '', $request->length), false);
        $request->height = $this->convertNumbers(str_replace(',', '', $request->height), false);
        $request->insuranceAmount = $this->convertNumbers(str_replace(',', '', $request->insuranceAmount), false);
        $request->suggestedPrice = $this->convertNumbers(str_replace(',', '', $request->suggestedPrice), false);
        $request->proposedPriceForDriver = $this->convertNumbers(str_replace(',', '', $request->proposedPriceForDriver), false);
        $request->tenderTimeDuration = $this->convertNumbers(str_replace(',', '', $request->tenderTimeDuration), false);
        $request->tenderTimeDuration = 0;

        $message[1] = '';

        $title = $request->title;
        $weight = $request->weight;
        $width = (strlen($request->width) == 0) ? 0 : $request->width;
        $length = (strlen($request->length) == 0) ? 0 : $request->length;
        $height = (strlen($request->height) == 0) ? 0 : $request->height;
        $loadingAddress = $request->loadingAddress;
        $dischargeAddress = $request->dischargeAddress;
        $loadingDate = $request->loadingDate;
        $insuranceAmount = $request->insuranceAmount;
        $suggestedPrice = (strlen($request->suggestedPrice) == 0) ? 0 : $request->suggestedPrice;
        $tenderTimeDuration = $request->tenderTimeDuration;
        $dischargeTime = $request->dischargeTime;
        $fleet_id = $request->fleet_id;
        $load_type_id = $request->load_type_id;
        $packing_type_id = $request->packing_type_id;
        $loadMode = $request->loadMode;
        $description = $request->description;
        $image = $request->file('pic');
        $numOfTrucks = $request->numOfTrucks;
        $loadingMinute = $request->loadingMinute;
        $loadingHour = $request->loadingHour;
        $loadPic = $this->savePicOfUsers($image);

        $load = new Load();
        $load->title = $title;
        $load->weight = $weight;
        $load->width = $width;
        $load->length = $length;
        $load->height = $height;
        $load->loadingAddress = $loadingAddress;
        $load->dischargeAddress = $dischargeAddress;
        $load->senderMobileNumber = $request->senderMobileNumber;
        $load->loadingDate = $loadingDate;
        $load->insuranceAmount = $insuranceAmount;
        $load->suggestedPrice = $suggestedPrice;
        $load->dischargeTime = $dischargeTime;
        $load->fleet_id = $fleet_id;
        $load->load_type_id = $load_type_id;
        $load->tenderTimeDuration = $tenderTimeDuration;
        $load->packing_type_id = $packing_type_id;
        $load->loadPic = $loadPic;

        if (isset($request->bearing_id)) {
            $load->user_id = $request->bearing_id;
            $load->bearing_id = $request->bearing_id;
            $load->status = ON_SELECT_DRIVER;
            $load->userType = 'bearing';
        } elseif (isset($request->customer_id)) {
            $load->user_id = $request->customer_id;
            $load->userType = 'customer';
        }
        $load->loadMode = $loadMode;
        $load->description = $description;
        $load->numOfTrucks = $numOfTrucks;
        $load->loadingMinute = $loadingMinute;
        $load->loadingHour = $loadingHour;
        $load->origin_city_id = $request->origin_city_id;
        $load->destination_city_id = $request->destination_city_id;
        $load->origin_state_id = AddressController::geStateIdFromCityId($request->origin_city_id);
        $load->proposedPriceForDriver = $request->proposedPriceForDriver;

        if ($loadMode == 'innerCity') {
            $load->origin_latitude = $request->origin_latitude;
            $load->origin_longitude = $request->origin_longitude;
            $load->destination_latitude = $request->destination_latitude;
            $load->destination_longitude = $request->destination_longitude;
        }
        $load->save();

        if ($load) {
            $this->sendLoadInquiryNotificationToDrivers($load->id, $request->proposedPriceForDriver);
            return redirect(url('admin/loadInfo/' . $load->id));
        }
    }

    public function createNewLoad(NewLoadRequest $request)
    {
        // Log::emergency($request);
        try {

            if (\auth()->check()) {
                if (UserActivityReport::where([
                    ['created_at', '>', date('Y-m-d H:i:s', strtotime('-5 minute', time()))],
                    ['user_id', \auth()->id()]
                ])->count() == 0)

                    UserActivityReport::create(['user_id' => \auth()->id()]);
            }
        } catch (Exception $e) {
            Log::emergency("-------------------------- UserActivityReport ----------------------------------------");
            Log::emergency($e->getMessage());
            Log::emergency("------------------------------------------------------------------");
        }

        try {
            $senderMobileNumber = isset($request->mobileNumberForCoordination) ? $request->mobileNumberForCoordination : $request->senderMobileNumber;
            if (BlockPhoneNumber::where('phoneNumber', $senderMobileNumber)->count()) {
                $message[1] = 'شماره تلفن وارد شده در لیست ممنوعه می باشد، و امکان ثبت بار با شماره تلفن ' . $senderMobileNumber .
                    ' امکان پذیر نمی باشد. لطفا برای دلیل آن با ایران ترابر تماس بگیرید';
                return [
                    'result' => UN_SUCCESS,
                    'message' => $message
                ];
            }

            if (BlockedIp::where('ip', request()->ip())->count()) {
                $message[1] = 'عدم ثبت بار به دلیل مسدود شدن IP';
                return [
                    'result' => UN_SUCCESS,
                    'message' => $message
                ];
            }


            // ثبت ip کاربر
            try {
                if ($request->userType == ROLE_OWNER) {
                    $owner = Owner::where('mobileNumber', $senderMobileNumber)->first();

                    if (isset($owner->id)) {
                        $owner->ip = request()->ip();
                        $owner->save();
                    }
                }
            } catch (Exception $e) {
                Log::emergency("=========================== Error Store Ip ================================");
                Log::emergency($e->getMessage());
                Log::emergency("========================= End Error Store Ip ==============================");
            }
        } catch (\Exception $exception) {
        }


        try {
            $message[1] = '';
            $loadPic = null;

            if (isset($request->marketing_price))
                $request->marketing_price = $request->marketing_price;
            else
                $request->marketing_price = 0;

            if (!isset($request->tenderTimeDuration))
                $request->tenderTimeDuration = 15;

            if ($request->image != "noImage") {
                $loadPic = "pictures/loads/" . sha1(time() . $request->user_id) . ".jpg";
                file_put_contents($loadPic, base64_decode($request->image));
            }

            DB::beginTransaction();

            $load = new Load();
            $load->title = strlen($request->title) > 0 ? $request->title : "بدون عنوان";
            $load->weight = $request->weight;
            $load->width = $this->convertNumbers($request->width, false);
            $load->length = $this->convertNumbers($request->length, false);
            $load->height = $this->convertNumbers($request->height, false);
            $load->loadingAddress = $request->loadingAddress;
            $load->dischargeAddress = $request->dischargeAddress;
            $load->senderMobileNumber = $request->senderMobileNumber;
            $load->receiverMobileNumber = $request->receiverMobileNumber;
            $load->insuranceAmount = strlen($request->insuranceAmount) ? $request->insuranceAmount : 0;
            $load->suggestedPrice = $request->suggestedPrice;
            $load->marketing_price = $request->marketing_price;
            $load->emergencyPhone = $request->emergencyPhone;
            $load->dischargeTime = $request->dischargeTime;
            $load->fleet_id = $request->fleet_id;
            $load->load_type_id = $request->load_type_id;
            $load->tenderTimeDuration = $request->tenderTimeDuration;
            $load->packing_type_id = $request->packing_type_id;
            $load->loadPic = $loadPic;
            $load->user_id = $request->user_id;
            $load->userType = $request->userType;
            $load->loadMode = $request->loadMode;
            $load->loadingHour = $request->loadingHour;
            $load->loadingMinute = $request->loadingMinute;
            $load->numOfTrucks = $request->numOfTrucks;
            $load->originLatitude = $request->originLatitude;
            $load->originLongitude = $request->originLongitude;
            $load->destinationLatitude = $request->destinationLatitude;
            $load->destinationLongitude = $request->destinationLongitude;
            $load->date = gregorianDateToPersian(date('Y/m/d', time()), '/');
            $load->dateTime = now()->format('H:i:s');

            if (isset($request->origin_city_id) && isset($request->destination_city_id)) {

                $load->origin_city_id = $request->origin_city_id;
                $load->destination_city_id = $request->destination_city_id;

                $load->fromCity = $this->getCityName($request->origin_city_id);
                $load->toCity = $this->getCityName($request->destination_city_id);
            } else {

                $originCity = $this->getCountyFromFullAddress($request->loadingAddress);
                $destinationCity = $this->getCountyFromFullAddress($request->dischargeAddress);

                if (isset($originCity->id)) {
                    $load->origin_city_id = $originCity->id;
                    $load->fromCity = $originCity->state . ', ' . $originCity->name;
                } else
                    $load->fromCity = $request->loadingAddress;

                if (isset($destinationCity->id)) {
                    $load->destination_city_id = $destinationCity->id;
                    $load->toCity = $destinationCity->state . ', ' . $destinationCity->name;
                } else
                    $load->toCity = $request->dischargeAddress;


                $load->latitude = $load->originLatitude;
                $load->longitude = $load->originLongitude;
            }
            try {
                $city = ProvinceCity::find($request->origin_city_id);
                if (isset($city->id)) {
                    $load->latitude = $city->latitude;
                    $load->longitude = $city->longitude;
                }
            } catch (\Exception $exception) {
            }

            $load->loadingDate = $request->loadingDate;
            $load->time = time();


            $load->weightPerTruck = isset($request->weightPerTruck) && $request->weightPerTruck > 0 ? $this->convertNumbers($request->weightPerTruck, false) : 0;

            $load->bulk = isset($request->bulk) ? $request->bulk : 2;
            $load->dangerousProducts = isset($request->dangerousProducts) ? $request->dangerousProducts : false;

            $load->origin_state_id = AddressController::geStateIdFromCityId($request->origin_city_id);
            $load->description = $request->description;
            if ($load->suggestedPrice == 0 && $request->storeFor == ROLE_DRIVER)
                $load->priceBased = 'توافقی';
            else
                $load->priceBased = $request->priceBased;

            if ($request->userType == ROLE_TRANSPORTATION_COMPANY) {
                $load->bearing_id = $request->user_id;
                $load->proposedPriceForDriver = $request->suggestedPrice;
            }

            $load->operator_id = 0;
            // ذخیره توسط اپراتور
            try {
                if (isset(\auth()->user()->role) && (\auth()->user()->role == ROLE_OPERATOR || \auth()->user()->role == ROLE_ADMIN)) {
                    $load->operator_id = \auth()->id();
                    $load->proposedPriceForDriver = $request->suggestedPrice;
                    $load->status = ON_SELECT_DRIVER;
                    $load->userType = ROLE_TRANSPORTATION_COMPANY;
                }
            } catch (Exception $e) {
            }

            if (isset($request->proposedPriceForDriver))
                $load->proposedPriceForDriver = $this->convertNumbers($request->proposedPriceForDriver, false);

            if ($request->loadMode == 'innerCity') {
                $load->origin_latitude = $request->origin_latitude;
                $load->origin_longitude = $request->origin_longitude;
                $load->destination_latitude = $request->destination_latitude;
                $load->destination_longitude = $request->destination_longitude;
            }

            if (isset($request->mobileNumberForCoordination)) {
                $load->mobileNumberForCoordination = convertFaNumberToEn($request->mobileNumberForCoordination);
            } else if (isset($request->senderMobileNumber)) {
                $load->mobileNumberForCoordination = convertFaNumberToEn($request->senderMobileNumber);
                $load->mobileNumberForCoordination = convertFaNumberToEn($request->senderMobileNumber);
            }


            $load->status = 4;
            $load->storeFor = $request->storeFor;

            // if (isset($request->storeFor)) {


            //     if ($request->storeFor == ROLE_DRIVER) {
            //         $load->status = ON_SELECT_DRIVER;
            //         //                    $load->userType = ROLE_CARGo_OWNER;
            //     } else if ($request->storeFor == ROLE_TRANSPORTATION_COMPANY) {
            //     }
            // }

            $load->deliveryTime = isset($request->deliveryTime) && $request->deliveryTime > 0 ? $request->deliveryTime : 24;

            if ($load->operator_id == NO_OPERATOR)
                $load->urgent = true;

            $load->save();

            if (isset($request->dateOfCargoDeclaration)) {

                $dateOfCargoDeclarations = explode(",", str_replace(" ", "", str_replace("[", "", str_replace("]", "", $request->dateOfCargoDeclaration))));

                for ($dateOfCargoDeclarationIndex = 0; $dateOfCargoDeclarationIndex < count($dateOfCargoDeclarations); $dateOfCargoDeclarationIndex++) {
                    if (strlen($dateOfCargoDeclarations[$dateOfCargoDeclarationIndex]) > 0) {
                        $dateOfCargoDeclaration = new DateOfCargoDeclaration();
                        $dateOfCargoDeclaration->load_id = $load->id;
                        $dateOfCargoDeclaration->declarationDate = $dateOfCargoDeclarations[$dateOfCargoDeclarationIndex];
                        $dateOfCargoDeclaration->save();
                    }
                }
            }

            if (isset($load->id) && isset($request->fleetList)) {

                if ($request->userType == ROLE_TRANSPORTATION_COMPANY) {
                    try {
                        $tender = new Tender();
                        $tender->load_id = $load->id;
                        $tender->bearing_id = $request->user_id;
                        $tender->suggestedPrice = $request->suggestedPrice;
                        $tender->status = 0;
                        $tender->save();
                    } catch (\Exception $e) {
                        Log::emergency($e->getMessage());
                    }
                } else if ($request->userType == "customer") {
                    try {
                        $customer = Customer::find($request->user_id);
                        if (isset($customer->freeLoads)) {
                            $customer->freeLoads--;
                            $customer->save();
                        }
                    } catch (\Exception $e) {
                        Log::emergency("Error Save Load for customer: " . $e->getMessage());
                    }
                }

                foreach ($request->fleetList as $item) {

                    $fleetLoad = new FleetLoad();
                    $fleetLoad->load_id = $load->id;
                    $fleetLoad->fleet_id = $item['fleet_id'];
                    $fleetLoad->numOfFleets = $item['numOfFleets'];
                    $fleetLoad->userType = $load->userType;
                    if ($request->userType == ROLE_TRANSPORTATION_COMPANY) {
                        $load->proposedPriceForDriver = $request->suggestedPrice;
                        $transportationCompany = Bearing::find($request->user_id);
                        $transportationCompany->countOfLoadsAfterValidityDate -= 1;
                        $transportationCompany->save();
                    }
                    $fleetLoad->save();
                }

                try {

                    $load->fleets = FleetLoad::join('fleets', 'fleets.id', 'fleet_loads.fleet_id')
                        ->where('fleet_loads.load_id', $load->id)
                        ->select('fleet_id', 'userType', 'suggestedPrice', 'numOfFleets', 'pic', 'title')
                        ->get();

                    $fleets = json_decode($load->fleets, true);
                    $loadDuplicates = Load::where('userType', 'operator')
                        ->where('mobileNumberForCoordination', $load->mobileNumberForCoordination)
                        ->where('origin_city_id', $request->origin_city_id)
                        ->where('destination_city_id', $request->destination_city_id)
                        ->where('fleets', 'LIKE', '%' . $fleets[0]['fleet_id'] . '%')
                        ->get();
                    // return $loadDuplicates;

                    if (count($loadDuplicates) > 0) {
                        foreach ($loadDuplicates as $loadDuplicate) {
                            $loadDuplicate->delete();
                        }
                    }

                    $load->save();
                } catch (\Exception $exception) {
                    Log::emergency("---------------------------------------------------------");
                    Log::emergency($exception->getMessage());
                    Log::emergency("---------------------------------------------------------");
                }

                try {
                    $backup = new LoadBackup();
                    $backup->id = $load->id;
                    $backup->title = $load->title;
                    $backup->weight = $load->weight;
                    $backup->width = $load->width;
                    $backup->length = $load->length;
                    $backup->height = $load->height;
                    $backup->loadingAddress = $load->loadingAddress;
                    $backup->dischargeAddress = $load->dischargeAddress;
                    $backup->senderMobileNumber = $load->senderMobileNumber;
                    $backup->receiverMobileNumber = $load->receiverMobileNumber;
                    $backup->insuranceAmount = $load->insuranceAmount;
                    $backup->suggestedPrice = $load->suggestedPrice;
                    $backup->marketing_price = $load->marketing_price;
                    $backup->emergencyPhone = $load->emergencyPhone;
                    $backup->dischargeTime = $load->dischargeTime;
                    $backup->fleet_id = $load->fleet_id;
                    $backup->load_type_id = $load->load_type_id;
                    $backup->tenderTimeDuration = $load->tenderTimeDuration;
                    $backup->packing_type_id = $load->packing_type_id;
                    $backup->loadPic = $load->loadPic;
                    $backup->user_id = $load->user_id;
                    $backup->loadMode = $load->loadMode;
                    $backup->loadingHour = $load->loadingHour;
                    $backup->loadingMinute = $load->loadingMinute;
                    $backup->numOfTrucks = $load->numOfTrucks;
                    $backup->origin_city_id = $load->origin_city_id;
                    $backup->destination_city_id = $load->destination_city_id;
                    $backup->fromCity = $load->fromCity;
                    $backup->toCity = $load->toCity;
                    $backup->loadingDate = $load->loadingDate;
                    $backup->time = $load->time;
                    $backup->latitude = $load->latitude;
                    $backup->longitude = $load->longitude;
                    $backup->weightPerTruck = $load->weightPerTruck;
                    $backup->bulk = $load->bulk;
                    $backup->dangerousProducts = $load->dangerousProducts;
                    $backup->origin_state_id = $load->origin_state_id;
                    $backup->description = $load->description;
                    $backup->priceBased = $load->priceBased;
                    $backup->bearing_id = $load->bearing_id;
                    $backup->proposedPriceForDriver = $load->proposedPriceForDriver;
                    $backup->operator_id = $load->operator_id;
                    $backup->userType = $load->userType;
                    $backup->origin_longitude = $load->origin_longitude;
                    $backup->destination_longitude = $load->destination_longitude;
                    $backup->mobileNumberForCoordination = $load->mobileNumberForCoordination;
                    $backup->storeFor = $load->storeFor;
                    $backup->status = $load->status;
                    $backup->fleets = $load->fleets;
                    $backup->deliveryTime = $load->deliveryTime;
                    $backup->save();
                } catch (\Exception $e) {
                    Log::emergency("========================= Load Backup ==================================");
                    Log::emergency($e->getMessage());
                    Log::emergency("==============================================================");
                }

                DB::commit();
                return [
                    'result' => SUCCESS,
                    'load_id' => $load->id
                ];
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::emergency("----------------------ثبت بار جدید-----------------------");
            Log::emergency($exception);
            Log::emergency("---------------------------------------------------------");
        }

        $message[1] = 'خطا! لطفا دوباره تلاش کنید';
        return [
            'result' => UN_SUCCESS,
            'message' => $message
        ];
    }

    public function createNewLoads(NewLoadRequest $request)
    {
        try {

            if (\auth()->check()) {
                if (UserActivityReport::where([
                    ['created_at', '>', date('Y-m-d H:i:s', strtotime('-5 minute', time()))],
                    ['user_id', \auth()->id()]
                ])->count() == 0)

                    UserActivityReport::create(['user_id' => \auth()->id()]);
            }
        } catch (Exception $e) {
            Log::emergency("-------------------------- UserActivityReport ----------------------------------------");
            Log::emergency($e->getMessage());
            Log::emergency("------------------------------------------------------------------");
        }

        try {
            $senderMobileNumber = isset($request->mobileNumberForCoordination) ? $request->mobileNumberForCoordination : $request->senderMobileNumber;
            if (BlockPhoneNumber::where('phoneNumber', $senderMobileNumber)->count()) {
                $message[1] = 'شماره تلفن وارد شده در لیست ممنوعه می باشد، و امکان ثبت بار با شماره تلفن ' . $senderMobileNumber .
                    ' امکان پذیر نمی باشد. لطفا برای دلیل آن با ایران ترابر تماس بگیرید';
                return [
                    'result' => UN_SUCCESS,
                    'message' => $message
                ];
            }

            if (BlockedIp::where('ip', request()->ip())->count()) {
                $message[1] = 'عدم ثبت بار به دلیل مسدود شدن IP';
                return [
                    'result' => UN_SUCCESS,
                    'message' => $message
                ];
            }


            // ثبت ip کاربر
            try {
                if ($request->userType == ROLE_OWNER) {
                    $owner = Owner::where('mobileNumber', $senderMobileNumber)->first();

                    if (isset($owner->id)) {
                        $owner->ip = request()->ip();
                        $owner->save();
                    }
                }
            } catch (Exception $e) {
                Log::emergency("=========================== Error Store Ip ================================");
                Log::emergency($e->getMessage());
                Log::emergency("========================= End Error Store Ip ==============================");
            }
        } catch (\Exception $exception) {
        }
        foreach ($request->destinationCities as $key => $destination_city) {
            try {
                $message[1] = '';
                $loadPic = null;

                if (isset($request->marketing_price))
                    $request->marketing_price = $request->marketing_price;
                else
                    $request->marketing_price = 0;

                if (!isset($request->tenderTimeDuration))
                    $request->tenderTimeDuration = 15;

                if ($request->image != "noImage") {
                    $loadPic = "pictures/loads/" . sha1(time() . $request->user_id) . ".jpg";
                    file_put_contents($loadPic, base64_decode($request->image));
                }

                DB::beginTransaction();

                $load = new Load();
                $load->title = strlen($request->title) > 0 ? $request->title : "بدون عنوان";
                $load->weight = $request->weight;
                $load->width = $this->convertNumbers($request->width, false);
                $load->length = $this->convertNumbers($request->length, false);
                $load->height = $this->convertNumbers($request->height, false);
                // $load->loadingAddress = $request->loadingAddress;
                // $load->dischargeAddress = $request->dischargeAddress;
                $load->senderMobileNumber = $request->senderMobileNumber;
                $load->receiverMobileNumber = $request->receiverMobileNumber;
                $load->insuranceAmount = strlen($request->insuranceAmount) ? $request->insuranceAmount : 0;
                $load->suggestedPrice = $request->suggestedPrice;
                $load->marketing_price = $request->marketing_price;
                $load->emergencyPhone = $request->emergencyPhone;
                $load->dischargeTime = $request->dischargeTime;
                $load->fleet_id = $request->fleet_id;
                $load->load_type_id = $request->load_type_id;
                $load->tenderTimeDuration = $request->tenderTimeDuration;
                $load->packing_type_id = $request->packing_type_id;
                $load->loadPic = $loadPic;
                $load->user_id = $request->user_id;
                $load->userType = $request->userType;
                $load->loadMode = $request->loadMode;
                $load->loadingHour = $request->loadingHour;
                $load->loadingMinute = $request->loadingMinute;
                $load->numOfTrucks = $request->numOfTrucks;
                $load->date = gregorianDateToPersian(date('Y/m/d', time()), '/');
                $load->dateTime = now()->format('H:i:s');

                $load->originLatitude = $request->originLatitude;
                $load->originLongitude = $request->originLongitude;

                $load->destinationLatitude = $request->destinationLatitudes[$key];
                $load->destinationLongitude = $request->destinationLongitudes[$key];

                $load->origin_city_id = $request->origin_city_id;
                $load->destination_city_id = $destination_city;

                $load->fromCity = $this->getCityName($request->origin_city_id);
                $load->toCity = $this->getCityName($destination_city);
                try {
                    $city = ProvinceCity::find($request->origin_city_id);
                    if (isset($city->id)) {
                        $load->latitude = $city->latitude;
                        $load->longitude = $city->longitude;
                    }
                } catch (\Exception $exception) {
                }


                $load->loadingDate = $request->loadingDate;
                $load->time = time();


                $load->weightPerTruck = isset($request->weightPerTruck) && $request->weightPerTruck > 0 ? $this->convertNumbers($request->weightPerTruck, false) : 0;

                $load->bulk = isset($request->bulk) ? $request->bulk : 2;
                $load->dangerousProducts = isset($request->dangerousProducts) ? $request->dangerousProducts : false;

                $load->origin_state_id = AddressController::geStateIdFromCityId($request->origin_city_id);
                $load->description = $request->description;
                if ($load->suggestedPrice == 0 && $request->storeFor == ROLE_DRIVER)
                    $load->priceBased = 'توافقی';
                else
                    $load->priceBased = $request->priceBased;

                if ($request->userType == ROLE_TRANSPORTATION_COMPANY) {
                    $load->bearing_id = $request->user_id;
                    $load->proposedPriceForDriver = $request->suggestedPrice;
                }

                $load->operator_id = 0;

                if (isset($request->proposedPriceForDriver))
                    $load->proposedPriceForDriver = $this->convertNumbers($request->proposedPriceForDriver, false);

                if ($request->loadMode == 'innerCity') {
                    $load->origin_latitude = $request->origin_latitude;
                    $load->origin_longitude = $request->origin_longitude;
                    $load->destination_latitude = $request->destination_latitude;
                    $load->destination_longitude = $request->destination_longitude;
                }

                if (isset($request->mobileNumberForCoordination)) {
                    $load->mobileNumberForCoordination = convertFaNumberToEn($request->mobileNumberForCoordination);
                } else if (isset($request->senderMobileNumber)) {
                    $load->mobileNumberForCoordination = convertFaNumberToEn($request->senderMobileNumber);
                    $load->mobileNumberForCoordination = convertFaNumberToEn($request->senderMobileNumber);
                }


                $load->status = 4;
                $load->storeFor = $request->storeFor;

                // if (isset($request->storeFor)) {


                //     if ($request->storeFor == ROLE_DRIVER) {
                //         $load->status = ON_SELECT_DRIVER;
                //         //                    $load->userType = ROLE_CARGo_OWNER;
                //     } else if ($request->storeFor == ROLE_TRANSPORTATION_COMPANY) {
                //     }
                // }

                $load->deliveryTime = isset($request->deliveryTime) && $request->deliveryTime > 0 ? $request->deliveryTime : 24;

                if ($load->operator_id == NO_OPERATOR)
                    $load->urgent = true;

                $load->save();




                if (isset($request->dateOfCargoDeclaration)) {

                    $dateOfCargoDeclarations = explode(",", str_replace(" ", "", str_replace("[", "", str_replace("]", "", $request->dateOfCargoDeclaration))));

                    for ($dateOfCargoDeclarationIndex = 0; $dateOfCargoDeclarationIndex < count($dateOfCargoDeclarations); $dateOfCargoDeclarationIndex++) {
                        if (strlen($dateOfCargoDeclarations[$dateOfCargoDeclarationIndex]) > 0) {
                            $dateOfCargoDeclaration = new DateOfCargoDeclaration();
                            $dateOfCargoDeclaration->load_id = $load->id;
                            $dateOfCargoDeclaration->declarationDate = $dateOfCargoDeclarations[$dateOfCargoDeclarationIndex];
                            $dateOfCargoDeclaration->save();
                        }
                    }
                }

                if (isset($load->id) && isset($request->fleetList)) {

                    // if ($request->userType == ROLE_TRANSPORTATION_COMPANY) {
                    //     try {
                    //         $tender = new Tender();
                    //         $tender->load_id = $load->id;
                    //         $tender->bearing_id = $request->user_id;
                    //         $tender->suggestedPrice = $request->suggestedPrice;
                    //         $tender->status = 0;
                    //         $tender->save();
                    //     } catch (\Exception $e) {
                    //         Log::emergency($e->getMessage());
                    //     }
                    // } else if ($request->userType == "customer") {
                    //     try {
                    //         $customer = Customer::find($request->user_id);
                    //         if (isset($customer->freeLoads)) {
                    //             $customer->freeLoads--;
                    //             $customer->save();
                    //         }
                    //     } catch (\Exception $e) {
                    //         Log::emergency("Error Save Load for customer: " . $e->getMessage());
                    //     }
                    // }

                    foreach ($request->fleetList as $item) {

                        $fleetLoad = new FleetLoad();
                        $fleetLoad->load_id = $load->id;
                        $fleetLoad->fleet_id = $item['fleet_id'];
                        $fleetLoad->numOfFleets = $item['numOfFleets'];
                        $fleetLoad->userType = $load->userType;
                        $fleetLoad->save();

                        try {
                            $persian_date = gregorianDateToPersian(date('Y/m/d', time()), '/');

                            $cargoReport = CargoReportByFleet::where('fleet_id', $fleetLoad->fleet_id)
                                ->where('date', $persian_date)
                                ->first();

                            if (isset($cargoReport->id)) {
                                $cargoReport->count_owner += 1;
                                $cargoReport->save();
                            } else {
                                $cargoReportNew = new CargoReportByFleet;
                                $cargoReportNew->fleet_id = $fleetLoad->fleet_id;
                                $cargoReportNew->count_owner = 1;
                                $cargoReportNew->date = $persian_date;
                                $cargoReportNew->save();
                                // Log::emergency("Error cargo report by 1387: " . $cargoReportNew);

                            }
                        } catch (Exception $e) {
                            Log::emergency("Error cargo report by fleets: " . $e->getMessage());
                        }
                    }

                    try {

                        $load->fleets = FleetLoad::join('fleets', 'fleets.id', 'fleet_loads.fleet_id')
                            ->where('fleet_loads.load_id', $load->id)
                            ->select('fleet_id', 'userType', 'suggestedPrice', 'numOfFleets', 'pic', 'title')
                            ->get();

                        $fleets = json_decode($load->fleets, true);
                        $loadDuplicates = Load::where('mobileNumberForCoordination', $load->mobileNumberForCoordination)
                            ->where('origin_city_id', $request->origin_city_id)
                            ->where('destination_city_id', $request->destination_city_id)
                            ->where('fleets', 'LIKE', '%' . $fleets[0]['fleet_id'] . '%')
                            ->get();
                        // return $loadDuplicates;

                        if (count($loadDuplicates) > 0) {
                            foreach ($loadDuplicates as $loadDuplicate) {
                                $loadDuplicate->delete();
                            }
                        }

                        $load->save();
                    } catch (\Exception $exception) {
                        Log::emergency("---------------------------------------------------------");
                        Log::emergency($exception->getMessage());
                        Log::emergency("---------------------------------------------------------");
                    }
                    DB::commit();
                }
            } catch (\Exception $exception) {
                DB::rollBack();
                Log::emergency("----------------------ثبت بار جدید-----------------------");
                Log::emergency($exception);
                Log::emergency("---------------------------------------------------------");
            }

            try {
                $backup = new LoadBackup();
                $backup->id = $load->id;
                $backup->title = $load->title;
                $backup->weight = $load->weight;
                $backup->width = $load->width;
                $backup->length = $load->length;
                $backup->height = $load->height;
                $backup->loadingAddress = $load->loadingAddress;
                $backup->dischargeAddress = $load->dischargeAddress;
                $backup->senderMobileNumber = $load->senderMobileNumber;
                $backup->receiverMobileNumber = $load->receiverMobileNumber;
                $backup->insuranceAmount = $load->insuranceAmount;
                $backup->suggestedPrice = $load->suggestedPrice;
                $backup->marketing_price = $load->marketing_price;
                $backup->emergencyPhone = $load->emergencyPhone;
                $backup->dischargeTime = $load->dischargeTime;
                $backup->fleet_id = $load->fleet_id;
                $backup->load_type_id = $load->load_type_id;
                $backup->tenderTimeDuration = $load->tenderTimeDuration;
                $backup->packing_type_id = $load->packing_type_id;
                $backup->loadPic = $load->loadPic;
                $backup->user_id = $load->user_id;
                $backup->loadMode = $load->loadMode;
                $backup->loadingHour = $load->loadingHour;
                $backup->loadingMinute = $load->loadingMinute;
                $backup->numOfTrucks = $load->numOfTrucks;
                $backup->origin_city_id = $load->origin_city_id;
                $backup->destination_city_id = $load->destination_city_id;
                $backup->fromCity = $load->fromCity;
                $backup->toCity = $load->toCity;
                $backup->loadingDate = $load->loadingDate;
                $backup->time = $load->time;
                $backup->latitude = $load->latitude;
                $backup->longitude = $load->longitude;
                $backup->weightPerTruck = $load->weightPerTruck;
                $backup->bulk = $load->bulk;
                $backup->dangerousProducts = $load->dangerousProducts;
                $backup->origin_state_id = $load->origin_state_id;
                $backup->description = $load->description;
                $backup->priceBased = $load->priceBased;
                $backup->bearing_id = $load->bearing_id;
                $backup->proposedPriceForDriver = $load->proposedPriceForDriver;
                $backup->operator_id = $load->operator_id;
                $backup->userType = $load->userType;
                $backup->origin_longitude = $load->origin_longitude;
                $backup->destination_longitude = $load->destination_longitude;
                $backup->mobileNumberForCoordination = $load->mobileNumberForCoordination;
                $backup->storeFor = $load->storeFor;
                $backup->status = $load->status;
                $backup->fleets = $load->fleets;
                $backup->deliveryTime = $load->deliveryTime;
                $backup->save();
            } catch (\Exception $e) {
                Log::emergency("========================= Load Backup ==================================");
                Log::emergency($e->getMessage());
                Log::emergency("==============================================================");
            }
        }
        if (isset($load->id)) {
            return [
                'result' => SUCCESS,
                'load_id' => $load->id
            ];
        }


        $message[1] = 'خطا! لطفا دوباره تلاش کنید';
        return [
            'result' => UN_SUCCESS,
            'message' => $message
        ];
    }

    public function sendNotifLoad(Load $load)
    {
        try {
            event(new PostCargoSmsEvent($load));
        } catch (\Exception $exception) {
            Log::emergency("******************************** send sms load by driver ******************************");
            Log::emergency($exception->getMessage());
            Log::emergency("*******************************************************************************************");
        }

        try {
            $fleet = FleetLoad::where('load_id', $load->id)->first();
            $cityFrom = ProvinceCity::where('id', $load->origin_city_id)->first();
            $cityTo = ProvinceCity::where('id', $load->destination_city_id)->first();

            $driverFCM_tokens = Driver::whereNotNull('FCM_token')
                ->where('province_id', $cityFrom->parent_id)
                ->where('fleet_id', $fleet->fleet_id)
                ->where('version', '>', 58)
                ->pluck('FCM_token');
            $title = 'ایران ترابر رانندگان';
            $body = ' بار ' . $fleet->fleet->title . ':' . ' از ' . $cityFrom->name . ' به ' . $cityTo->name;
            foreach ($driverFCM_tokens as $driverFCM_token) {
                $this->sendNotification($driverFCM_token, $title, $body, API_ACCESS_KEY_OWNER);
            }
        } catch (\Exception $exception) {
            Log::emergency("----------------------send notification load by driver-----------------------");
            Log::emergency($exception);
            Log::emergency("---------------------------------------------------------");
        }
    }

    // تبدیل اعداد فارسی به انگلیسی
    public function convertNumbers($srting, $toPersian = true)
    {
        if (strlen($srting) == 0)
            $srting = 0;

        $en_num = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
        $fa_num = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');
        if ($toPersian) return str_replace($en_num, $fa_num, $srting);
        else return str_replace($fa_num, $en_num, $srting);
    }

    // درخواست لیست بارهای باربری
    public function requestLoadsBearing($bearing_id)
    {
        $loads = Load::join('load_statuses', 'loads.status', '=', 'load_statuses.status')
            ->join('cities as originCity', 'loads.origin_city_id', 'originCity.id')
            ->join('cities as destinationCity', 'loads.destination_city_id', 'destinationCity.id')
            ->where('loads.bearing_id', $bearing_id)
            ->select(
                'loads.id',
                'loads.title',
                'loads.weight',
                'loads.status',
                'loads.loadingDate',
                'loads.numOfTrucks',
                'loads.suggestedPrice',
                'loads.score',
                'loads.gibarDriverRequest as urgent', // بصورت موقت مقدار صفر را برای فوری نمایش بدهد
                'loads.insuranceAmount',
                'loads.price',
                'loads.loadingHour',
                'loads.loadingMinute',
                'loads.proposedPriceForDriver',
                'loads.priceBased',
                'loads.fromCity',
                'loads.toCity',
                'loads.mobileNumberForCoordination',
                'loads.userType',
                'loads.origin_city_id',
                'loads.destination_city_id',
                'loads.created_at',
                'loads.driverVisitCount',
                'loads.time',
                'loads.fleets',
                'originCity.name as from',
                'load_statuses.title as statusTitle',
                'destinationCity.name as to',
                'load_statuses.title as statusTitle'
            )
            ->orderBy('id', 'desc')
            ->take(150)
            ->get();

        if (count($loads) > 0) {
            return [
                'result' => SUCCESS,
                'currentTime' => time(),
                'loads' => $loads
            ];
        }
        return [
            'result' => THERE_IS_NO_LOAD,
            'message' => 'هیچ باری وجود ندارد'
        ];
    }

    // درخواست لیست بارهای راننده
    public function requestDriverLoadsList($driver_id)
    {

        $driverLoads = DriverLoad::where('driver_id', $driver_id)->pluck('load_id');
        // $driver = Driver::find($driver_id);

        $loads = Load::join('province_cities as originCity', 'loads.origin_city_id', 'originCity.id')
            ->join('province_cities as destinationCity', 'loads.destination_city_id', 'destinationCity.id')
            // ->join('load_statuses', 'load_statuses.status', 'loads.status')
            ->whereIn('loads.id', $driverLoads)
            ->withTrashed()
            ->select(
                'loads.id',
                'loads.status',
                'loads.loadingDate',
                // 'load_statuses.title as statusTitle',
                'loads.title',
                'originCity.name as from',
                'destinationCity.name as to',
                // 'loads.urgent',
                'loads.fromCity',
                'loads.toCity',
                'loads.fleets',
                'loads.time',
                'loads.priceBased',
                'loads.suggestedPrice',
                'loads.deleted_at',
                'loads.mobileNumberForCoordination',
                'loads.origin_city_id',
                'loads.destination_city_id',
                'loads.dateTime',
                'loads.user_id'
            )
            ->orderBy('loads.id', 'desc')
            ->skip(0)
            ->take(1000)
            ->get();

        $loadsList = $loads;
        // $loadsList[] = $loads;

        if (count($loads) > 0) {

            return [
                'result' => SUCCESS,
                'loads' => $loadsList,
                // 'fleet_id' => $driver->fleet_id
            ];
        }

        return [
            'result' => UN_SUCCESS,
            'message' => 'هیچ باری وجود ندارد'
        ];
    }

    // لیست بارها
    public function loads()
    {
        return view('admin.loadsCategories');
    }

    // نمایش گروه بندی بارها بر اساس وضعیت
    public static function displayLoadsCategoriesFromLoadStatus()
    {
        $loadStatus = LoadStatus::orderby('status', 'asc')->get();
        $bg[0] = "bg-light";
        $color[0] = "#FFFFFF";
        $bg[1] = "bg-light";
        $color[1] = "#17a2b8";
        $bg[2] = "bg-light";
        $color[2] = "#17a2b8";
        $bg[3] = "bg-info";
        $color[3] = "#FFFFFF";
        $bg[4] = "bg-info";
        $color[4] = "#17a2b8";
        $bg[5] = "bg-info";
        $color[5] = "#FFFFFF";
        $bg[6] = "bg-success";
        $color[6] = "#FFFFFF";
        $bg[7] = "bg-success";
        $color[7] = "#FFFFFF";
        $bg[8] = "bg-success";
        $color[8] = "#FFFFFF";
        $bg[9] = "bg-warning";
        $color[9] = "#17a2b8";
        $bg[10] = "bg-warning";
        $color[10] = "#FFFFFF";

        $index = 0;

        $result = '<div class="row row-cols-3">';

        foreach ($loadStatus as $status) {

            $result .= '<div class="col"> <a href="' . url('admin/loads') . '/' . $status->status . '" class="col-md-3 p-1" style="color: ' . $color[$index] . '">
                    <div class="alert ' . $bg[$index] . '">
                        <h1>' . Load::where('status', $status->status)->count() . '</h1>
                        <hr>
                        <h6 style="font-size: 14px;">' . $status->title . '</h6>
                    </div>
                </a> </div>';
            $index++;
        }
        return $result . "</div>";
    }


    // درخواست لیست بارهای مشتری
    public function requestCustomerLoadsLists($id)
    {
        $loads = Load::join('load_statuses', 'loads.status', '=', 'load_statuses.status')
            ->join('province_cities as originCity', 'loads.origin_city_id', 'originCity.id')
            ->join('province_cities as destinationCity', 'loads.destination_city_id', 'destinationCity.id')
            ->where('user_id', $id)
            ->where('userType', 'owner')
            ->select(
                'loads.id',
                'loads.suggestedPrice',
                'loads.origin_city_id',
                'loads.destination_city_id',
                'loads.priceBased',
                'loads.title',
                'loads.driverVisitCount',
                'loads.fleets',
                'loads.fromCity',
                'loads.toCity',
                'loads.loadingHour',
                'loads.loadingMinute',
                'loads.loadingDate',
                'loads.created_at',
                'loads.date',
                'loads.dateTime',
            )
            ->orderByDesc('created_at')
            ->paginate(10);

        if (count($loads) > 0) {
            return response()->json($loads, 200);
        } else {
            return response()->json(['message' => 'هیچ باری وجود ندارد'], 404);
        }
    }

    // درخواست لیست بارهای بایگانی صاحبان بار
    public function requestCustomerLoadsTrashed($id)
    {
        $loads = Load::join('load_statuses', 'loads.status', '=', 'load_statuses.status')
            ->join('province_cities as originCity', 'loads.origin_city_id', 'originCity.id')
            ->join('province_cities as destinationCity', 'loads.destination_city_id', 'destinationCity.id')
            ->where('user_id', $id)
            ->where('userType', 'owner')
            ->select(
                'loads.id',
                'loads.suggestedPrice',
                'loads.origin_city_id',
                'loads.destination_city_id',
                'loads.priceBased',
                'loads.title',
                'loads.driverVisitCount',
                'loads.fleets',
                'loads.fromCity',
                'loads.toCity',
                'loads.loadingHour',
                'loads.loadingMinute',
                'loads.loadingDate',
                'loads.created_at',
                'loads.date',
                'loads.dateTime',
            )
            ->orderByDesc('created_at')
            ->onlyTrashed()
            ->paginate(10);
        if (count($loads) > 0) {
            return response()->json($loads, 200);
        } else {
            return response()->json(['message' => 'هیچ باری وجود ندارد'], 404);
        }
        return [
            'result' => UN_SUCCESS,
            'message' => 'هیچ باری وجود ندارد'
        ];
    }

    // درخواست لیست بارهای جدید
    public function requestNewLoads($bearing_id)
    {
        $bearing = Bearing::where('id', $bearing_id)->first();

        $loads = Load::join('load_statuses', 'loads.status', '=', 'load_statuses.status')
            ->join('cities as originCity', 'loads.origin_city_id', 'originCity.id')
            ->join('cities as destinationCity', 'loads.destination_city_id', 'destinationCity.id')
            ->where([
                ['loads.status', '>', -1],
                ['loads.status', '<', 3],
                ['loads.origin_state_id', '=', $bearing->state_id],
                ['driverCallCounter', '>', 0]
            ])
            ->select(
                'loads.id',
                'loads.title',
                'loads.weight',
                'loads.status',
                'loads.loadingDate',
                'loads.numOfTrucks',
                'loads.suggestedPrice',
                'loads.score',
                'loads.urgent',
                'loads.insuranceAmount',
                'loads.price',
                'loads.loadingHour',
                'loads.loadingMinute',
                'loads.proposedPriceForDriver',
                'loads.priceBased',
                'loads.userType',
                'loads.origin_city_id',
                'loads.driverVisitCount',
                'loads.destination_city_id',
                'loads.created_at',
                'loads.time',
                'loads.storeFor',
                'loads.fleets',
                'originCity.name as from',
                'load_statuses.title as statusTitle',
                'destinationCity.name as to',
                'load_statuses.title as statusTitle'
            )
            ->orderBy('id', 'desc')
            ->skip(0)
            ->take(200)
            ->get();


        $loadsList = [];
        foreach ($loads as $load) {
            $load['checkSetPrice'] = false; //$this->getCheckSetPrice($load->id, $bearing_id);
            $loadsList[] = $load;
        }

        if (count($loads) > 0) {
            return [
                'result' => SUCCESS,
                'currentTime' => time(),
                'loads' => $loadsList,
            ];
        }

        return [
            'result' => UN_SUCCESS,
            'message' => 'باری وجود ندارد'
        ];
    }

    public function getCheckSetPrice($load_id, $transportationCompany_id)
    {

        $check = Tender::where([
            'bearing_id' => $transportationCompany_id,
            'load_id' => $load_id
        ])->count();
        //        return  $check;
        if ($check)
            return true;
        return false;
    }

    // درخواست لیست بارهای جدید
    public function requestNewLoadsForBearingInWeb()
    {
        $bearing = Bearing::where('id', \auth('bearing')->id())->first();

        if ($bearing->state_id == 8) {
            $loads = Load::join('load_statuses', 'loads.status', '=', 'load_statuses.status')
                ->join('cities as originCity', 'loads.origin_city_id', 'originCity.id')
                ->join('cities as destinationCity', 'loads.destination_city_id', 'destinationCity.id')
                ->where([
                    ['loads.status', '<', 3],
                    ['loads.status', '>', -1],
                    ['loads.origin_state_id', '=', $bearing->state_id],
                    ['driverCallCounter', '>', 0]
                ])
                ->orwhere([
                    ['loads.status', '<', 3],
                    ['loads.status', '>', -1],
                    ['loads.origin_state_id', '=', 3]
                ])
                ->select(
                    'loads.*'
                )
                ->orderBy('id', 'desc')
                ->get();
        } else {
            $loads = Load::join('load_statuses', 'loads.status', '=', 'load_statuses.status')
                ->join('province_cities as originCity', 'loads.origin_city_id', 'originCity.id')
                ->join('province_cities as destinationCity', 'loads.destination_city_id', 'destinationCity.id')
                ->where([
                    ['loads.status', '<', 3],
                    ['loads.status', '>', -1],
                    ['loads.origin_state_id', '=', $bearing->state_id],
                    ['driverCallCounter', '>', 0]
                ])
                ->select(
                    'loads.*'
                )
                ->orderBy('id', 'desc')
                ->get();
        }

        $loadListType = 'newLoad';
        $bearing_id = $bearing->id;
        $pageTitle = 'بارهای جدید';
        return view('users.loads', compact('bearing_id', 'loads', 'loadListType', 'pageTitle'));
    }

    public function loadInfoForUser($id)
    {

        if (Load::where('id', $id)->count() == 0) {
            $message = 'چنین باری وجود ندارد';
            $alert = 'alert-warning';
            return view('users.alert', compact('message', 'alert'));
        }

        //        if ((\auth('customer')->check() && Load::where([['id', $id], ['user_id', \auth('customer')->id()]])->count() == 0) ||    (\auth('bearing')->check() && Load::where([['id', $id], ['bearing_id', \auth('bearing')->id()]])->count() == 0) ) {
        //            $message = 'چنین باری وجود ندارد';
        //            $alert = 'alert-warning';
        //            return view('users.alert', compact('message', 'alert'));
        //        }

        $loadInfo = $this->requestLoadInfo($id, 'user');

        $load = $loadInfo['loadInfo'];

        if (\auth('bearing')->check()) {
            if ($load->status > 2 && ($load->bearing_id != \auth('bearing')->id())) {
                $message = 'متاسفانه قیمت شما توسط صاحب بار پذیرفته نشد';
                $alert = 'alert-warning';
                return view('users.alert', compact('message', 'alert'));
            }
        }

        $path = $loadInfo['path'];

        $drivers = Driver::join('driver_loads', 'drivers.id', 'driver_loads.driver_id')
            ->select('drivers.*')
            ->where('driver_loads.load_id', $id)
            ->get();

        $tenders = Tender::join('bearings', 'bearings.id', 'tenders.bearing_id')
            ->where('tenders.load_id', $id)
            ->orderby('tenders.suggestedPrice', 'asc')
            ->select('tenders.suggestedPrice', 'bearings.title as bearingTitle')
            ->get();

        $fleetLoads = $loadInfo['fleetLoads'];

        //        $tenders = Tender::where('load_id', $id)
        //            ->orderBy('suggestedPrice', 'asc')
        //            ->get();


        if (session()->get('result')) {
            $result = session()->get('result');
            return view('users/loadInfo', compact('load', 'path', 'drivers', 'id', 'tenders', 'result'));
        }
        return view('users.loadInfo', compact('load', 'path', 'drivers', 'id', 'tenders', 'fleetLoads'));
    }

    // درخواست اطلاعات بار
    public function requestLoadInfo($id, $userType = '')
    {
        try {
            $loadInfo = Load::join('load_statuses', 'load_statuses.status', 'loads.status')
                ->where('loads.id', $id)
                ->withTrashed()
                ->select(
                    'loads.*',
                    'load_statuses.title as statusTitle',
                    'load_statuses.info as statusInfo'
                )
                ->first();

            if (!isset($loadInfo->id))
                return [
                    'result' => UN_SUCCESS,
                    'message' => 'چنین باری وجود ندارد'
                ];


            $packingType = PackingType::find($loadInfo->packing_type_id);
            if (isset($packingType->id)) {
                $loadInfo['packingTypeTitle'] = $packingType->title;
                $loadInfo['packingTypePic'] = $packingType->pic;
            } else {
                $loadInfo['packingTypeTitle'] = "انتخاب نشده";
                $loadInfo['packingTypePic'] = "";
            }

            $fleetLoads = FleetLoad::join('fleets', 'fleet_loads.fleet_id', 'fleets.id')
                ->where('load_id', $id)
                ->select('fleets.*', 'fleet_loads.id as fleet_load_id', 'fleet_loads.numOfFleets', 'fleet_loads.userType', 'fleet_loads.suggestedPrice', 'fleet_loads.load_id', 'fleet_loads.fleet_id')
                ->get();

            // $remainingTimeStatus = 'noStart';
            // $inquiry = Inquiry::where('load_id', $id)
            //     ->orderBy('id', 'asc')
            //     ->first();

            // if ($inquiry) {
            //     if ((TNDER_TIME - DateController::getSecondFromCreateRowToPresent($inquiry->created_at)) > 0)
            //         $remainingTimeStatus = 'start';
            //     else
            //         $remainingTimeStatus = 'finish';
            // }
            //        else {
            //            $tenderStart = new TenderStart();
            //            $tenderStart->load_id = $id;
            //            $tenderStart->tender_start = date("Y-m-d H:i:s");
            //            $tenderStart->type = 'inquiry';
            //            $tenderStart->save();
            //        }
            if ($loadInfo->status < 4 && $userType != 'admin') {
                //            $loadInfo = Load::join('load_statuses', 'load_statuses.status', 'loads.status')
                //                ->where('loads.id', $id)
                //                ->select('title', 'weight', 'width', 'length', 'height', 'loadingAddress', 'dischargeAddress', 'senderMobileNumber', 'receiverMobileNumber', 'loadingDate', 'insuranceAmount', 'suggestedPrice', 'tenderTimeDuration', 'driver_id', 'emergencyPhone', 'dischargeTime', 'fleet_id', 'load_type_id', 'packing_type_id', 'loadPic', 'loadMode', 'price', 'description', 'bearing_id', 'status', 'load_statuses.title as statusTitle')
                //                ->first();

                $loadInfo = Load::join('load_statuses', 'load_statuses.status', 'loads.status')
                    ->where('loads.id', $id)
                    ->select(
                        'loads.*',
                        'load_statuses.title as statusTitle',
                        'load_statuses.info as statusInfo'
                    )
                    ->first();

                $packingType = PackingType::find($loadInfo->packing_type_id);
                if (isset($packingType->id)) {
                    $loadInfo['packingTypeTitle'] = $packingType->title;
                    $loadInfo['packingTypePic'] = $packingType->pic;
                } else {
                    $loadInfo['packingTypeTitle'] = "انتخاب نشده";
                    $loadInfo['packingTypePic'] = "";
                }
            }

            if ($loadInfo) {

                // $driver = Load::join('drivers', 'drivers.id', '=', 'loads.driver_id')
                //     ->where('loads.id', $id)
                //     ->select('drivers.name', 'drivers.lastName')
                //     ->first();

                $path = [
                    // 'from' => AddressController::geCityName($loadInfo->origin_city_id),
                    // 'to' => AddressController::geCityName($loadInfo->destination_city_id),
                    // 'stateFrom' => AddressController::geStateNameFromCityId($loadInfo->origin_city_id),
                    // 'stateTo' => AddressController::geStateNameFromCityId($loadInfo->destination_city_id),
                    'fromLatLong' => AddressController::getLatLong($loadInfo->origin_city_id),
                    'toLatLong' => AddressController::getLatLong($loadInfo->destination_city_id),
                ];

                if ($loadInfo->dischargeTime == 'night')
                    $loadInfo->dischargeTime = 'تخلیه در شب';
                else if ($loadInfo->dischargeTime == 'day')
                    $loadInfo->dischargeTime = 'تخلیه در روز';
                else
                    $loadInfo->dischargeTime = 'انتخاب نشده';

                if ($loadInfo->loadMode == 'outerCity')
                    $loadInfo->loadMode = 'برون شهری';
                else
                    $loadInfo->loadMode = 'درون شهری';

                // $selectDriverCost = 25000;
                // if ($loadInfo->price <= 2000000) {
                //     $selectDriverCost = 25000;
                // } else if ($loadInfo->price > 2000000 && $loadInfo->price <= 4000000) {
                //     $selectDriverCost = 35000;
                // } else if ($loadInfo->price > 4000000) {
                //     $selectDriverCost = 45000;
                // }

                $owner = Owner::where('id', $loadInfo->user_id)
                    ->where('userType', ROLE_OWNER)
                    ->select(['id', 'name', 'lastName', 'mobileNumber', 'isAccepted'])
                    ->first();

                // try {
                //     if ($loadInfo->operator_id > 0 || $loadInfo->userType == ROLE_CARGo_OWNER || $loadInfo->userType == "customer")
                //         if (isset($owner->mobileNumber))
                //             $owner->mobileNumber = $loadInfo->mobileNumberForCoordination;
                //         else
                //             $owner['mobileNumber'] = $loadInfo->mobileNumberForCoordination;
                // } catch (\Exception $exception) {
                //     $owner = $exception->getMessage();
                // }


                return [
                    'result' => SUCCESS,
                    'loadInfo' => $loadInfo,
                    // 'fleet' => FleetController::getFleetName($loadInfo->fleet_id),
                    'path' => $path,
                    // 'driver' => $driver,
                    'owner' => $owner,
                    // 'selectDriverCost' => $selectDriverCost,
                    // 'remainingTimeStatus' => $remainingTimeStatus,
                    'fleetLoads' => $fleetLoads,
                    // 'dateOfCargoDeclaration' => DateOfCargoDeclaration::where('load_id', $loadInfo->id)->get()
                ];
            }
        } catch (Exception $exception) {

            Log::emergency("************************************ Load info ******************************************");
            Log::emergency($exception->getMessage());
            Log::emergency("************************************ Load info ******************************************");
            return [
                'result' => UN_SUCCESS,
                'message' => 'خطا! دوباره تلاش کنید'
            ];
        }

        return [
            'result' => UN_SUCCESS,
            'message' => 'چنین باری وجود ندارد'
        ];
    }

    // درخواست اطلاعات بار
    public function requestLoadInfoWeb($id, $userType = '')
    {
        try {
            $loadInfo = Load::join('load_statuses', 'load_statuses.status', 'loads.status')
                ->where('loads.id', $id)
                ->withTrashed()
                ->select(
                    'loads.*',
                    'load_statuses.title as statusTitle',
                    'load_statuses.info as statusInfo'
                )
                ->first();

            if (!isset($loadInfo->id))
                return [
                    'result' => UN_SUCCESS,
                    'message' => 'چنین باری وجود ندارد'
                ];


            $packingType = PackingType::find($loadInfo->packing_type_id);
            if (isset($packingType->id)) {
                $loadInfo['packingTypeTitle'] = $packingType->title;
                $loadInfo['packingTypePic'] = $packingType->pic;
            } else {
                $loadInfo['packingTypeTitle'] = "انتخاب نشده";
                $loadInfo['packingTypePic'] = "";
            }

            $fleetLoads = FleetLoad::join('fleets', 'fleet_loads.fleet_id', 'fleets.id')
                ->where('load_id', $id)
                ->select('fleets.*', 'fleet_loads.id as fleet_load_id', 'fleet_loads.numOfFleets', 'fleet_loads.userType', 'fleet_loads.suggestedPrice', 'fleet_loads.load_id', 'fleet_loads.fleet_id')
                ->get();

            $remainingTimeStatus = 'noStart';
            $inquiry = Inquiry::where('load_id', $id)
                ->orderBy('id', 'asc')
                ->first();

            if ($inquiry) {
                if ((TNDER_TIME - DateController::getSecondFromCreateRowToPresent($inquiry->created_at)) > 0)
                    $remainingTimeStatus = 'start';
                else
                    $remainingTimeStatus = 'finish';
            } else {
                $tenderStart = new TenderStart();
                $tenderStart->load_id = $id;
                $tenderStart->tender_start = date("Y-m-d H:i:s");
                $tenderStart->type = 'inquiry';
                $tenderStart->save();
            }
            if ($loadInfo->status < 4 && $userType != 'admin') {
                $loadInfo = Load::join('load_statuses', 'load_statuses.status', 'loads.status')
                    ->where('loads.id', $id)
                    ->select('title', 'weight', 'width', 'length', 'height', 'loadingAddress', 'dischargeAddress', 'senderMobileNumber', 'receiverMobileNumber', 'loadingDate', 'insuranceAmount', 'suggestedPrice', 'tenderTimeDuration', 'driver_id', 'emergencyPhone', 'dischargeTime', 'fleet_id', 'load_type_id', 'packing_type_id', 'loadPic', 'loadMode', 'price', 'description', 'bearing_id', 'status', 'load_statuses.title as statusTitle')
                    ->first();

                $loadInfo = Load::join('load_statuses', 'load_statuses.status', 'loads.status')
                    ->where('loads.id', $id)
                    ->select(
                        'loads.*',
                        'load_statuses.title as statusTitle',
                        'load_statuses.info as statusInfo'
                    )
                    ->first();

                $packingType = PackingType::find($loadInfo->packing_type_id);
                if (isset($packingType->id)) {
                    $loadInfo['packingTypeTitle'] = $packingType->title;
                    $loadInfo['packingTypePic'] = $packingType->pic;
                } else {
                    $loadInfo['packingTypeTitle'] = "انتخاب نشده";
                    $loadInfo['packingTypePic'] = "";
                }
            }

            if ($loadInfo) {

                $driver = Load::join('drivers', 'drivers.id', '=', 'loads.driver_id')
                    ->where('loads.id', $id)
                    ->select('drivers.name', 'drivers.lastName')
                    ->first();

                $path = [
                    'from' => AddressController::geCityName($loadInfo->origin_city_id),
                    'to' => AddressController::geCityName($loadInfo->destination_city_id),
                    'stateFrom' => AddressController::geStateNameFromCityId($loadInfo->origin_city_id),
                    'stateTo' => AddressController::geStateNameFromCityId($loadInfo->destination_city_id),
                    'fromLatLong' => AddressController::getLatLong($loadInfo->origin_city_id),
                    'toLatLong' => AddressController::getLatLong($loadInfo->destination_city_id),
                ];

                if ($loadInfo->dischargeTime == 'night')
                    $loadInfo->dischargeTime = 'تخلیه در شب';
                else if ($loadInfo->dischargeTime == 'day')
                    $loadInfo->dischargeTime = 'تخلیه در روز';
                else
                    $loadInfo->dischargeTime = 'انتخاب نشده';

                if ($loadInfo->loadMode == 'outerCity')
                    $loadInfo->loadMode = 'برون شهری';
                else
                    $loadInfo->loadMode = 'درون شهری';

                $selectDriverCost = 25000;
                if ($loadInfo->price <= 2000000) {
                    $selectDriverCost = 25000;
                } else if ($loadInfo->price > 2000000 && $loadInfo->price <= 4000000) {
                    $selectDriverCost = 35000;
                } else if ($loadInfo->price > 4000000) {
                    $selectDriverCost = 45000;
                }

                $owner = Owner::where('id', $loadInfo->user_id)
                    ->where('userType', ROLE_OWNER)
                    ->select(['id', 'name', 'lastName', 'mobileNumber'])
                    ->first();

                try {
                    if ($loadInfo->operator_id > 0 || $loadInfo->userType == ROLE_CARGo_OWNER || $loadInfo->userType == "customer")
                        if (isset($owner->mobileNumber))
                            $owner->mobileNumber = $loadInfo->mobileNumberForCoordination;
                        else
                            $owner['mobileNumber'] = $loadInfo->mobileNumberForCoordination;
                } catch (\Exception $exception) {
                    $owner = $exception->getMessage();
                }


                return [
                    'result' => SUCCESS,
                    'loadInfo' => $loadInfo,
                    'fleet' => FleetController::getFleetName($loadInfo->fleet_id),
                    'path' => $path,
                    'driver' => $driver,
                    'owner' => $owner,
                    'selectDriverCost' => $selectDriverCost,
                    'remainingTimeStatus' => $remainingTimeStatus,
                    'fleetLoads' => $fleetLoads,
                    'dateOfCargoDeclaration' => DateOfCargoDeclaration::where('load_id', $loadInfo->id)->get()
                ];
            }
        } catch (Exception $exception) {

            Log::emergency("************************************ Load info ******************************************");
            Log::emergency($exception->getMessage());
            Log::emergency("************************************ Load info ******************************************");
            return [
                'result' => UN_SUCCESS,
                'message' => 'خطا! دوباره تلاش کنید'
            ];
        }

        return [
            'result' => UN_SUCCESS,
            'message' => 'چنین باری وجود ندارد'
        ];
    }

    // دریافت اطلاعات بار برای راننده
    public function getLoadInfoForDriver($load_id, $driver_id)
    {

        try {
            // if (DriverVisitLoad::where([
            //     ['load_id', $load_id],
            //     ['driver_id', $driver_id]
            // ])->count() == 0) {

            //     $driverVisitCount = new DriverVisitLoad();
            //     $driverVisitCount->load_id = $load_id;
            //     $driverVisitCount->driver_id = $driver_id;
            //     $driverVisitCount->save();

            //     $loadInfo = Load::find($load_id);
            //     $loadInfo->driverVisitCount++;
            //     $loadInfo->save();
            // }
            $loadInfo = Load::findOrFail($load_id);
            $loadInfo->driverVisitCount++;
            $loadInfo->save();
        } catch (\Exception $exception) {
        }

        return $this->requestLoadInfo($load_id);
    }

    // دریافت اطلاعات بار برای راننده
    public function loadDetail($load_id, $driver_id)
    {
        try {
            $result = Inquiry::where([['load_id', $load_id], ['driver_id', $driver_id]])->count();
        } catch (\Exception $exception) {
            $result = 0;
        }
        try {
            $loadInfo = Load::findOrFail($load_id);
            $loadInfo->driverVisitCount++;
            $loadInfo->save();

            $loadInfo = Load::where('id', $load_id)
                ->select(
                    'weightPerTruck',
                    'dischargeAddress',
                    'loadingAddress',
                    'loadingDate',
                    'dangerousProducts',
                    'width',
                    'length',
                    'height',
                    'insuranceAmount',
                    'destinationLatitude',
                    'fleets',
                    'user_id',
                    'title',
                    'description',
                    'fromCity',
                    'userType',
                    'toCity',
                    'origin_city_id',
                    'destination_city_id',
                    'mobileNumberForCoordination',
                    'priceBased',
                    'suggestedPrice',
                )->first();

            if ($loadInfo) {
                $path = [
                    'fromLatLong' => AddressController::getLatLong($loadInfo->origin_city_id),
                    'toLatLong' => AddressController::getLatLong($loadInfo->destination_city_id),
                ];
                $owner = Owner::where('id', $loadInfo->user_id)
                    ->where('userType', ROLE_OWNER)
                    ->select(['id', 'name', 'lastName', 'mobileNumber', 'isAccepted'])
                    ->first();
            }
            return [
                'result' => SUCCESS,
                'loadInfo' => $loadInfo,
                'path' => $path,
                'owner' => $owner,
                'result' => $result
            ];
        } catch (\Exception $exception) {
            return response()->json('بار مورد نظر یافت نشد', 404);
            // return [
            //     'result' => UN_SUCCESS,
            //     'message' => $exception
            // ];
        }
    }

    // درخواست محاسبه هزنیه انتخاب راننده
    public function requestSelectDriverCost($load_id)
    {

        $loadInfo = Load::where('id', $load_id)->first();
        $selectDriverCost = 25000;
        if ($loadInfo->price <= 2000000) {
            $selectDriverCost = 25000;
        } else if ($loadInfo->price > 2000000 && $loadInfo->price <= 4000000) {
            $selectDriverCost = 35000;
        } else if ($loadInfo->price > 4000000) {
            $selectDriverCost = 45000;
        }

        return ['selectDriverCost' => $selectDriverCost];
    }

    public function sendNotifManuall(Load $load)
    {
        try {
            event(new PostCargoSmsEvent($load));
            return back()->with('success', 'با موفقیت ارسال شد');

        } catch (\Exception $exception) {
            Log::emergency("******************************** send Notification Manual ******************************");
            Log::emergency($exception->getMessage());
            Log::emergency("*******************************************************************************************");
        }
    }

    // نمایش اطلاعات بار
    public function loadInfo($id)
    {
        // if (LoadBackup::where('id', $id)->count() == 0) {
        //     $message = 'چنین باری وجود ندارد';
        //     $alert = 'alert-warning';
        //     return view('users.alert', compact('message', 'alert'));
        // }

        $loadInfo = $this->requestLoadInfoWeb($id, 'admin');
        $load = Load::where('id', $id)->withTrashed()->first();
        if (!isset($load)) {
            $message = 'چنین باری وجود ندارد';
            $alert = 'alert-warning';
            return view('users.alert', compact('message', 'alert'));
        }

        $load = $loadInfo['loadInfo'];

        $path = $loadInfo['path'];

        $fleetLoads = $loadInfo['fleetLoads'];

        $drivers = Driver::join('driver_loads', 'drivers.id', 'driver_loads.driver_id')
            ->select('drivers.*')
            ->where('driver_loads.load_id', $id)
            ->get();


        $tenders = Tender::join('bearings', 'bearings.id', 'tenders.bearing_id')
            ->where('tenders.load_id', $id)
            ->select('tenders.suggestedPrice', 'bearings.title', 'bearings.id')
            ->orderBy('suggestedPrice', 'asc')
            ->get();

        $bearings = Bearing::select('id', 'title')->get();

        return view('admin.loadInfo', compact('load', 'path', 'drivers', 'tenders', 'bearings', 'fleetLoads'));
    }

    public function requestLoadStatus($id)
    {
        $loadInfo = Load::where('id', $id)->first();

        if ($loadInfo)
            return $loadInfo->status;
        return 0;
    }

    // چک کردن باربری انتخاب شده برای بار
    public function checkSelectedBearingOfLoad(Request $request)
    {
        $load_id = $request->load_id;
        $bearing_id = $request->bearing_id;

        $load = Load::where('id', $load_id)->first();

        if ($load) {

            if ($load->bearing_id == 0) {

                // هنوز هیج باربری انخاب ندشه است
                return [
                    'result' => NO_ONE
                ];
            } else if ($load->bearing_id == $bearing_id) {

                // این باربری انتخاب شده است
                return [
                    'result' => YOUR_SELF
                ];
            }

            // باربری دیگری انتخاب شده است
            return [
                'result' => ANOTHER
            ];
        }
        return [
            'result' => UN_SUCCESS,
            'message' => 'چنین باری وجود ندارد'
        ];
    }

    // انتخاب راننده برای بار
    public function selectDriverForLoad(Request $request)
    {
        try {

            if (DriverLoad::where([['load_id', $request->load_id], ['driver_id', $request->driver_id]])->count())
                return [
                    'result' => UN_SUCCESS,
                    'data' => null,
                    'message' => 'این راننده قبلا برای این بار ثبت شده است'
                ];


            if ($request->driver_id == 0) {
                Load::where('id', $request->load_id)
                    ->update(['status' => 5]);
                return [
                    'result' => SUCCESS
                ];
            }

            $load = Load::where([
                ['id', $request->load_id],
                ['bearing_id', $request->bearing_id]
            ])->first();

            $driver = Driver::find($request->driver_id);

            $driverLoad = new DriverLoad();
            $driverLoad->driver_id = $request->driver_id;
            $driverLoad->load_id = $request->load_id;
            $driverLoad->fleet_id = $driver->fleet_id;
            $driverLoad->save();

            if ($load->numOfRequestedDrivers <= $load->numOfSelectedDrivers)
                Load::where('id', $request->load_id)
                    ->update([
                        'status' => 5
                    ]);

            // ارسال نوتیفیکیشن استعلام بار برای رانندگان
            $data = [
                'title' => 'انتخاب راننده',
                'body' => 'شما به عنوان راننده این بار انتخاب شدید',
                'load_id' => $request->load_id,
                'notificationType' => 'selectedAsLoadDriver',
            ];

            $this->sendNotification($driver->FCM_token, $data, API_ACCESS_KEY_DRIVER);

            return [
                'result' => SUCCESS,
                'data' => null,
                'message' => null
            ];
        } catch (\Exception $exception) {

            Log::emergency($exception->getMessage());
        }

        return [
            'result' => UN_SUCCESS,
            'data' => null,
            'message' => 'درخواست شما ثبت نشد! لطفا دوباره تلاش کنید'
        ];
    }

    public function selectDriverForLoadByOwner(Request $request)
    {
        try {

            if (DriverLoad::where([['load_id', $request->load_id], ['driver_id', $request->driver_id]])->count())

                return [
                    'result' => 0,
                    'message' => 'این راننده قبلا برای این بار ثبت شده است'
                ];

            // if ($request->driver_id == 0) {
            //     Load::where('id', $request->load_id)
            //         ->update(['status' => 5]);
            //     return [
            //         'result' => SUCCESS
            //     ];
            // }

            // $load = Load::where([
            //     ['id', $request->load_id],
            // ])->first();

            $driver = Driver::find($request->driver_id);

            $driverLoadCount = DriverLoad::where('load_id', $request->load_id)
                ->where('fleet_id', $driver->fleet_id)
                ->count();

            $driverFleetsCount = FleetLoad::where('fleet_id', $driver->fleet_id)->where('load_id', $request->load_id)->first();

            if ($driverFleetsCount->numOfFleets > $driverLoadCount) {
                $driverLoad = new DriverLoad();
                $driverLoad->driver_id = $request->driver_id;
                $driverLoad->load_id = $request->load_id;
                $driverLoad->fleet_id = $driver->fleet_id;
                $driverLoad->save();

                // if ($load->numOfRequestedDrivers <= $load->numOfSelectedDrivers)
                //     Load::where('id', $request->load_id)
                //         ->update([
                //             'status' => 5
                //         ]);
                return [
                    'result' => 1,
                    'message' => 'راننده انتخاب شد'
                ];
            } else {
                return [
                    'result' => 0,
                    'message' => 'راننده با ناوگان مورد نظر تکمیل شده'
                ];
            }

            // ارسال نوتیفیکیشن استعلام بار برای رانندگان
            // $data = [
            //     'title' => 'انتخاب راننده',
            //     'body' => 'شما به عنوان راننده این بار انتخاب شدید',
            //     'load_id' => $request->load_id,
            //     'notificationType' => 'selectedAsLoadDriver',
            // ];

            // $this->sendNotification($driver->FCM_token, $data, API_ACCESS_KEY_DRIVER);

        } catch (\Exception $exception) {

            Log::emergency($exception->getMessage());
        }
        return response()->json('درخواست شما ثبت نشد! لطفا دوباره تلاش کنید', 404);
    }

    // تحویل بار
    public function loadDelivery(Request $request)
    {
        $load_id = $request->load_id;
        $driver_id = $request->driver_id;

        $load = Load::where([
            ['id', $load_id],
            ['driver_id', $driver_id]
        ])->count();

        if ($load > 0) {

            Load::where('id', $load_id)
                ->update(['status' => DELIVERY]);

            return [
                'result' => SUCCESS
            ];
        }

        return [
            'result' => UN_SUCCESS,
            '' => 'چنین باری وجود ندارد'
        ];
    }

    // انتخاب باربری برای بار توسط صاحب بار
    public function selectBearingForLoadInWeb(Request $request)
    {
        $result = $this->selectBearingForLoad($request);

        //        return redirect()->with('')
    }

    // انتخاب باربری برای بار توسط صاحب بار
    public function selectBearingForLoad(Request $request)
    {
        $load_id = $request->load_id;
        $customer_id = $request->customer_id;
        $bearing_id = $request->bearing_id;


        $load = Load::where([
            ['id', $load_id],
            ['user_id', $customer_id]
        ])->first();

        if (isset($load->id)) {

            $tender = Tender::where([
                ['bearing_id', $bearing_id],
                ['load_id', $load_id]
            ])->first();

            if ($tender) {

                Load::where('id', $load_id)
                    ->update([
                        'bearing_id' => $bearing_id,
                        'price' => $tender->suggestedPrice,
                        'status' => 3
                    ]);

                $bearings = Bearing::join('tenders', 'tenders.bearing_id', 'bearings.id')
                    ->where('tenders.load_id', $load_id)
                    ->whereRaw('LENGTH(FCM_token)>10')
                    ->select('bearings.id', 'bearings.mobileNumber', 'bearings.FCM_token')
                    ->get();

                $winnerData = [
                    'title' => 'ثبت قیمت',
                    'body' => 'قیمت شما توسط صاحب بار پذیرفته شد',
                    'load_id' => $load_id,
                    'notificationType' => 'youWonTheLoad',
                ];

                $loserData = [
                    'title' => 'ثبت قیمت',
                    'body' => 'متاسفانه قیمت شما توسط صاحب بار پذیرفته نشد',
                    'load_id' => $load_id,
                    'notificationType' => 'youLoseTheLoad',
                ];


                foreach ($bearings as $bearing) {

                    //                    if ($bearing->id == $bearing_id) {
                    //                        $this->sendNotification($bearing->FCM_token, $winnerData, API_ACCESS_KEY_TRANSPORTATION_COMPANY);
                    //                        SMSController::sendSMSWithPattern($bearing->mobileNumber, "g07o9tnenb84wts", ["cargo" => $load->title]);
                    //                        continue;
                    //                    }

                    $this->sendNotification($bearing->FCM_token, $loserData, API_ACCESS_KEY_TRANSPORTATION_COMPANY);
                }
                return [
                    'result' => SUCCESS
                ];
            }

            return [
                'result' => UN_SUCCESS,
                'message' => 'خطا لطفا دوباره تلاش کنید'
            ];
        }

        return [
            'result' => UN_SUCCESS,
            'message' => 'چنین باری وجود ندارد'
        ];
    }

    // درخواست لیست نوع بارها
    public function requestLoadTypeList()
    {
        return [
            'loadTypes' => LoadType::select('id', 'title', 'pic')->get()
        ];
    }

    // لیست بارهای باربری برای ادمین
    public function bearingLoads($bearing_id)
    {
        $info = $this->requestLoadsBearing($bearing_id);

        if ($info['result'] == SUCCESS) {
            $loads = $info['loads'];
            return view('admin.bearingLoads', compact('loads', 'bearing_id'));
        }
        $message = $info['message'];
        $loads = [];

        return view('admin.bearingLoads', compact('message', 'loads', 'bearing_id'));
    }

    // لیست بارهای مشتری برای ادمین
    public function customerLoads($customer_id)
    {
        $loads = LoadBackup::where('user_id', $customer_id)
            ->orderBy('id', 'desc')
            ->get();
        return view('admin.customerLoads', compact('loads', 'customer_id'));
    }

    // لیست بارهای مشتری برای ادمین
    public function ownerLoads($owner_id)
    {
        $loads = Load::where('user_id', $owner_id)
            ->where('userType', 'owner')
            ->orderBy('id', 'desc')
            ->withTrashed()
            ->get();
        $loadsTrashedCount = Load::onlyTrashed()->where('userType', 'owner')->where('user_id', $owner_id)->count();
        $loadsCount = Load::where('userType', 'owner')->where('user_id', $owner_id)->count();
        return view('admin.customerLoads', compact(['loads', 'loadsTrashedCount', 'loadsCount']));
    }

    public function loadBackup($loads = [], $showSearchResult = false)
    {
        if (!$showSearchResult) {
            $loads = LoadBackup::orderByDesc('created_at')
                ->with('customer')
                ->where('userType', ROLE_CUSTOMER)
                ->paginate(20);
            // return $loads;
        }
        return view('admin.loadBackup', compact('loads'));
    }

    public function loadOwner()
    {
        $loads = Load::orderByDesc('created_at')
            ->with('owner')
            ->withTrashed()
            ->where('userType', ROLE_OWNER)
            ->where('isBot', 0)
            ->paginate(20);
        $loadsCount = Load::orderByDesc('created_at')
            ->where('userType', ROLE_OWNER)
            ->where('isBot', 0)
            ->withTrashed()
            ->count();

        $loadsToday = Load::where('userType', ROLE_OWNER)
            ->where('created_at', '>', date('Y-m-d', time()) . ' 00:00:00')
            ->withTrashed()
            ->where('isBot', 0)
            ->count();
        return view('admin.load.owner', compact('loads', 'loadsCount', 'loadsToday'));
    }

    public function loadOperators()
    {
        $loads = Load::orderByDesc('created_at')
            ->with('owner')
            ->withTrashed()
            ->where('userType', ROLE_OWNER)
            ->where('isBot', 1)
            ->paginate(20);
        $loadsCount = Load::orderByDesc('created_at')
            ->where('userType', ROLE_OWNER)
            ->where('isBot', 1)
            ->withTrashed()
            ->count();

        $loadsToday = Load::where('userType', ROLE_OWNER)
            ->where('created_at', '>', date('Y-m-d', time()) . ' 00:00:00')
            ->withTrashed()
            ->where('isBot', 1)
            ->count();
        return view('admin.load.operators', compact('loads', 'loadsCount', 'loadsToday'));
    }


    public function searchLoadInquiry(string $load_id)
    {
        $drivers = Driver::whereHas('inquiries', function ($q) use ($load_id) {
            $q->where('load_id', $load_id);
        })->paginate(100);
        if (count($drivers))
            return view('admin.driver.searchDriver', compact('drivers'));

        return back()->with('danger', 'راننده ای پیدا نشد!');
    }
    public function searchLoadDriverCall(string $load_id)
    {
        $drivers = Driver::whereHas('driverCalls', function ($q) use ($load_id) {
            $q->where('load_id', $load_id);
        })->paginate(100);

        if (count($drivers))
            return view('admin.driver.searchDriver', compact('drivers'));

        return back()->with('danger', 'راننده ای پیدا نشد!');
    }

    // بار های ثبت شده توسط صاحبین بار (امروز)
    public function loadOwnerToday()
    {
        $loads = Load::orderByDesc('created_at')
            ->with('owner')
            ->withTrashed()
            ->where('userType', ROLE_OWNER)
            ->where('created_at', '>', date('Y-m-d', time()) . ' 00:00:00')
            ->paginate(20);

        $loadsCount = Load::orderByDesc('created_at')
            ->where('userType', ROLE_OWNER)
            ->withTrashed()
            ->count();

        $loadsToday = Load::where('userType', ROLE_OWNER)
            ->where('created_at', '>', date('Y-m-d', time()) . ' 00:00:00')
            ->withTrashed()
            ->count();

        return view('admin.load.owner', compact('loads', 'loadsCount', 'loadsToday'));
    }

    // جستجوی بار های صاحبین بار
    public function searchLoadBackupCustomer(Request $request)
    {
        $loads = LoadBackup::orderByDesc('created_at')
            ->where('userType', ROLE_CUSTOMER)
            ->where('mobileNumberForCoordination', 'like', '%' . $request->mobileNumber . '%')
            ->paginate(20);

        if (count($loads))
            return $this->loadBackup($loads, true);

        return back()->with('danger', 'آیتم پیدا نشد!');
    }

    public function loadBackupTransportation($loads = [], $showSearchResult = false)
    {
        if (!$showSearchResult) {
            $loads = LoadBackup::orderByDesc('created_at')
                ->where('userType', ROLE_TRANSPORTATION_COMPANY)
                ->with('bearing')
                ->paginate(20);
        }
        return view('admin.load.loadBackupTransportation', compact('loads'));
    }

    public function searchLoadBackupTransportation(Request $request)
    {
        $loads = LoadBackup::orderByDesc('created_at')
            ->where('userType', ROLE_TRANSPORTATION_COMPANY)
            ->where('mobileNumberForCoordination', 'like', '%' . $request->mobileNumber . '%')
            ->paginate(20);

        if (count($loads))
            return $this->loadBackupTransportation($loads, true);

        return back()->with('danger', 'آیتم پیدا نشد!');
    }

    /*************************************************************************************************/

    // درخواست لیست باریهای جدید برای راننده
    // تابع نسخه اول
    public function requestNewLoadsForDriver($driver_id)
    {
        try {

            return $this->requestNewLoadsForDrivers(Driver::find($driver_id));
        } catch (\Exception $exception) {
            Log::emergency($exception->getMessage());
        }
        return [
            'result' => UN_SUCCESS,
            'message' => 'درحال حاضر باری برای مسیرهای انتخابی شما آماده نیست'
        ];
    }

    public function requestNewLoadsForDrivers(Driver $driver)
    {
        try {
            DriverActivity::firstOrCreate([
                'driver_id' => $driver->id,
                'persianDate' => DateController::createPersianDate()
            ]);
        } catch (\Exception $exception) {
            Log::emergency("**************************************************************");
            Log::emergency($exception->getMessage());
            Log::emergency("**************************************************************");
        }

        try {
            $loads = Load::where([
                ['status', ON_SELECT_DRIVER],
                ['created_at', '>', \date('Y-m-d h:i:s', strtotime('-1 day', time()))],
                ['fleets', 'Like', '%fleet_id":' . $driver->fleet_id . ',%'],
                ['driverCallCounter', '>', 0]
            ])
                ->select(
                    'id',
                    'weight',
                    'numOfTrucks',
                    'loadingHour',
                    'loadingMinute',
                    'proposedPriceForDriver',
                    'suggestedPrice',
                    'title',
                    'priceBased',
                    'userType',
                    'urgent',
                    'status',
                    'origin_city_id',
                    'destination_city_id',
                    'created_at',
                    'time',
                    'loadingDate',
                    'fleets',
                    'storeFor'
                )
                ->skip(0)
                ->take(180)
                ->orderBy('id', 'desc')
                ->get();

            if (count($loads))
                return [
                    'counts' => count($loads),
                    'result' => SUCCESS,
                    'loads' => $loads,
                    'currentTime' => time(),
                    'fleet_id' => $driver->fleet_id
                ];

            return [
                'result' => UN_SUCCESS,
                'message' => 'درحال حاضر باری برای مسیرهای انتخابی شما آماده نیست'
            ];
        } catch (\Exception $exception) {
            Log::emergency($exception->getMessage());
        }
        return [
            'result' => UN_SUCCESS,
            'message' => 'درحال حاضر باری برای مسیرهای انتخابی شما آماده نیست'
        ];
    }

    public function requestNewLoadsForDriversV2(Driver $driver)
    {

        if ($driver->status == DE_ACTIVE)
            return [
                'result' => UN_SUCCESS,
                'data' => ['driverStatus' => false],
                'message' => 'حساب کاربری شما غیر فعال می باشد! لطفا جهت فعال سازی با شماره تلفن ' . TELL . ' تماس برقرار کنید.'
            ];

        try {
            DriverActivity::firstOrCreate([
                'driver_id' => $driver->id,
                'persianDate' => DateController::createPersianDate()
            ]);
        } catch (\Exception $exception) {
            Log::emergency("**************************************************************");
            Log::emergency($exception->getMessage());
            Log::emergency("**************************************************************");
        }

        try {
            $loads = Load::where([
                ['status', ON_SELECT_DRIVER],
                ['created_at', '>', \date('Y-m-d h:i:s', strtotime('-1 day', time()))],
                ['fleets', 'Like', '%fleet_id":' . $driver->fleet_id . ',%'],
                ['driverCallCounter', '>', 0]
            ])
                ->select(
                    'id',
                    'weight',
                    'numOfTrucks',
                    'loadingHour',
                    'loadingMinute',
                    'proposedPriceForDriver',
                    'suggestedPrice',
                    'title',
                    'priceBased',
                    'userType',
                    'urgent',
                    'status',
                    'created_at',
                    'time',
                    'fromCity',
                    'toCity',
                    'loadingDate',
                    'fleets',
                    'storeFor'
                )
                ->skip(0)
                ->take(180)
                ->orderBy('id', 'desc')
                ->get();

            if (count($loads))
                return [
                    'counts' => count($loads),
                    'result' => SUCCESS,
                    'loads' => $loads,
                    'currentTime' => time(),
                    'fleet_id' => $driver->fleet_id
                ];
        } catch (\Exception $exception) {
            Log::emergency("******************************** requestNewLoadsForDriversV2 ******************************");
            Log::emergency($exception->getMessage());
            Log::emergency("*******************************************************************************************");
        }

        return [
            'result' => UN_SUCCESS,
            'data' => ['driverStatus' => true],
            'message' => 'درحال حاضر باری برای ناوگان شما آماده نیست'
        ];
    }

    // دریافت لیست بارها برای راننده به صورت صفحه بندی شده
    public function getNewLoadForDriver(Driver $driver, $lastLoadId = 0)
    {
        if ($driver->version != 67) {
            $driver->version = 65;
        }
        $driver->save();

        if ($driver->status == DE_ACTIVE)
            return [
                'result' => UN_SUCCESS,
                'data' => ['driverStatus' => false],
                'message' => 'حساب کاربری شما غیر فعال می باشد! لطفا جهت فعال سازی با شماره تلفن ' . TELL . ' تماس برقرار کنید.'
            ];


        try {

            $page = 15;

            $conditions = [];
            if ($lastLoadId > 0) {
                $conditions[] = ['id', '<', $lastLoadId];
                $conditions[] = ['urgent', 0];
                $page = 15;
                try {
                    DriverActivity::firstOrCreate([
                        'driver_id' => $driver->id,
                        'persianDate' => DateController::createPersianDate()
                    ]);
                } catch (\Exception $exception) {
                    Log::emergency("**************************************************************");
                    Log::emergency($exception->getMessage());
                    Log::emergency("**************************************************************");
                }
            }
            $conditions[] = ['status', ON_SELECT_DRIVER];
            $conditions[] = ['created_at', '>', \date('Y-m-d h:i:s', strtotime('-1 day', time()))];
            $conditions[] = ['fleets', 'Like', '%fleet_id":' . $driver->fleet_id . ',%'];
            $conditions[] = ['driverCallCounter', '>', 0];

            $loads = Load::where($conditions)
                ->select(
                    'id',
                    'suggestedPrice',
                    'title',
                    'priceBased',
                    'mobileNumberForCoordination',
                    'urgent',
                    // 'status',
                    'time',
                    'fromCity',
                    'toCity',
                    'loadingDate',
                    'fleets',
                    'time',
                    'description',
                    'origin_city_id',
                    'destination_city_id',
                    // 'created_at',
                    // 'storeFor'
                    // 'weight',
                    // 'numOfTrucks',
                    // 'loadingHour',
                    // 'loadingMinute',
                    // 'proposedPriceForDriver',
                    'userType',
                )
                ->skip(0)
                ->take($page)

                ->orderBy('urgent', 'desc')
                ->orderBy('id', 'desc')
                ->get();
            $setting = Setting::first();

            if (count($loads))
                return [
                    // 'counts' => count($loads),
                    'result' => SUCCESS,
                    'loads' => $loads,
                    // 'currentTime' => time(),
                    'token' => $driver->FCM_token,
                    'transactionCount' => $driver->transactionCount,
                    'Tel' => $setting->tel,
                ];
        } catch (\Exception $exception) {
            Log::emergency("******************************** requestNewLoadsForDriversV2 ******************************");
            Log::emergency($exception->getMessage());
            Log::emergency("*******************************************************************************************");
        }
        return [
            'result' => UN_SUCCESS,
            'data' => ['driverStatus' => true],
            'message' => 'درحال حاضر باری برای ناوگان شما آماده نیست'
        ];
    }

    // تاریخچه لیست تماس های راننده
    public function callHistory(string $driver_id)
    {
        $driverCalls = Load::whereHas('driverCalls', function ($q) use ($driver_id) {
            $q->where('driver_id', $driver_id);
        })->select(
            'id',
            'suggestedPrice',
            'title',
            'priceBased',
            'mobileNumberForCoordination',
            'urgent',
            'status',
            'time',
            'fromCity',
            'toCity',
            'loadingDate',
            'fleets',
            'time',
            'description',
        )
            ->get();
        return response()->json($driverCalls, 200);
    }

    /*************************************************************************************************/

    // ثبت قیمت جدید در استعلام بار
    public function storeInquiryToLoad(Request $request)
    {
        $inquiry = Inquiry::where([
            ['driver_id', $request->driver_id],
            ['load_id', $request->load_id],
        ])->count();


        $load = Load::findOrFail($request->load_id);
        $driver = Driver::findOrFail($request->driver_id);
        $owner = Owner::where('mobileNumber', $load->mobileNumberForCoordination)->whereNotNull('FCM_token')->first();

        $cityFrom = ProvinceCity::where('id', $load->origin_city_id)->first();
        $cityTo = ProvinceCity::where('id', $load->destination_city_id)->first();

        if ($owner) {
            try {
                $title = 'ایران ترابر صاحبان بار';
                $body = $driver->name . ' ' . $driver->lastName . ' با شماره تماس ' . $driver->mobileNumber . ' درخواست حمل بار شما از مبدا ' . $cityFrom->name . ' به ' . $cityTo->name . ' را دارد.';

                $this->sendNotification($owner->FCM_token, $title, $body);
            } catch (\Exception $exception) {
                Log::emergency("----------------------send notif storeInquiryToLoad-----------------------");
                Log::emergency($exception);
                Log::emergency("---------------------------------------------------------");
            }
        }


        // اگر قبلا ثبت قیمت ثبت کرده بروز شود
        if ($inquiry > 0) {
            Inquiry::where([
                ['driver_id', $request->driver_id],
                ['load_id', $request->load_id],
            ])->update(['price' => $request->price]);

            // $this->sendNewInquiryNotification($request->load_id, $request->price, $request->driver_id);

            return ['result' => SUCCESS];
        }

        $city = ProvinceCity::where('parent_id', '!=', 0)->where('name', $request->city)->first();

        $inquiry = new Inquiry();
        $inquiry->driver_id = $request->driver_id;
        $inquiry->load_id = $request->load_id;
        $inquiry->price = $request->price;
        $inquiry->city_id = $city ? $city->id : null;
        $inquiry->latitude = $request->latitude == null ? 0 : $request->latitude;
        $inquiry->longitude = $request->longitude == null ? 0 : $request->longitude;
        $inquiry->date = gregorianDateToPersian(date('Y/m/d', time()), '/');
        $inquiry->dateTime = now()->format('H:i:s');
        $inquiry->save();

        if ($inquiry) {
            // $this->sendNewInquiryNotification($request->load_id, $request->price, $request->driver_id);
            return ['result' => SUCCESS];
        }

        return [
            'result' => UN_SUCCESS,
            'message' => 'خطا در ثبت استعلام لطفا دوباره تلاش کنید'
        ];
    }

    // درخواست لیست قیمت های استعلام بار رانندگان
    public function requestInquiriesOfLoad($load_id)
    {
        $inquiries = Inquiry::join('drivers', 'inquiries.driver_id', 'drivers.id')
            ->with('fleet')
            ->where('load_id', $load_id)
            ->select('drivers.name', 'drivers.lastName', 'drivers.mobileNumber', 'drivers.fleet_id', 'price', 'inquiries.created_at', 'inquiries.date', 'inquiries.dateTime')
            ->orderBy('price', 'asc')
            ->get();
        return response()->json($inquiries, 200);
    }
    // درخواست لیست قیمت های استعلام بار رانندگان
    public function requestInquiriesOfLoadCall($load_id)
    {
        $inquiries = Inquiry::join('drivers', 'inquiries.driver_id', 'drivers.id')
            ->with('fleet')
            ->where('load_id', $load_id)
            ->select('drivers.name', 'drivers.id',  'drivers.lastName', 'drivers.mobileNumber', 'drivers.fleet_id', 'price', 'inquiries.created_at', 'inquiries.date', 'inquiries.dateTime', 'inquiries.latitude', 'inquiries.longitude', 'inquiries.city_id', 'inquiries.driver_id', 'inquiries.load_id')
            ->orderBy('price', 'asc')
            ->get();

        $calls = DriverCall::where('load_id', $load_id)
            ->with('driverSelect')

            ->get();

        return response()->json([
            'inquiries' => $inquiries,
            'calls' => $calls
        ], 200);
    }

    // فرم افزودن بار توسط اپراتور
    public function addNewLoadForm($userType, $message = [])
    {
        $cities = City::orderby('centerOfProvince', 'desc')->get();

        $packingTypes = PackingType::get();

        if ($userType == ROLE_ADMIN || $userType == ROLE_OPERATOR) {
            $myFleets = FleetOperator::where('operator_id', auth()->id())->pluck('fleet_id');
            $fleets = Fleet::whereIn('id', $myFleets)
                ->orderBy('parent_id', 'asc')
                ->get();
            return view('users.addNewLoadForm', compact('message', 'cities', 'fleets', 'packingTypes', 'userType'));
            return view('admin.addNewLoadForm', compact('message', 'cities', 'fleets', 'packingTypes', 'userType'));
        }

        $fleets = Fleet::where('parent_id', '>', 0)->orderBy('parent_id', 'asc')->get();


        if (\auth('bearing')->check()) {
            $bearings = Bearing::select('id', 'title', 'mobileNumber')->get();
            $userType = ROLE_TRANSPORTATION_COMPANY;
            return view('users.addNewLoadForm', compact('message', 'cities', 'fleets', 'packingTypes', 'bearings', 'userType'));
        }

        $customers = Customer::select('id', 'name', 'lastName', 'mobileNumber')->get();
        return view('users.addNewLoadForm', compact('message', 'cities', 'fleets', 'packingTypes', 'customers', 'userType'));
    }

    public function acceptCargo()
    {
        $cargoAccepts = Load::where('status', BEFORE_APPROVAL)
            ->where('userType', 'customer')
            // ->select(['id', 'title', 'toCity', 'fromCity', 'mobileNumberForCoordination', 'created_at'])
            ->paginate(15);
        return view('admin.load.cargo_accept', compact('cargoAccepts'));
    }

    public function acceptCargoStore(Request $request, $id)
    {
        $load = Load::where('id', $id)->first();
        if ($request->accept == DE_ACTIVE) {
            $load->status = BEFORE_APPROVAL;
            $load->save();
            return back()->with('danger', 'بار مورد نظر رد شد');
        }
        if ($load->storeFor == ROLE_DRIVER) {
            $load->status = ON_SELECT_DRIVER;
        } else {
            $load->status = ON_SELECT_DRIVER;
        }
        $load->save();
        return back()->with('success', 'بار مورد نظر قبول شد');
    }

    // تغییر وضعیت بار
    public function changeLoadStatus(Request $request)
    {
        $load_id = $request->load_id;
        $bearing_id = $request->bearing_id;

        $count = Tender::where([
            ['load_id', $load_id],
            ['bearing_id', $bearing_id]
        ])->count();

        if ($count) {

            $load = Load::where('id', $load_id)->first();

            if ($load->status != END_TENDER)
                $this->sendNotificationForCustomer($load->user_id, $load_id);

            Load::where([
                ['id', $load_id],
                ['status', IN_TENDER]
            ])->update(['status' => END_TENDER]);


            return [
                'result' => SUCCESS
            ];
        }
        return [
            'result' => UN_SUCCESS,
            'message' => 'شما مجاز به دسترسی نیستید'
        ];
    }

    // تغییر وضعیت به درحال بارگیری
    public function changeStatusToOnLoading(Request $request)
    {
        $load_id = $request->load_id;
        $driver_id = $request->driver_id;

        $load = 0;

        if ($driver_id > 0) {
            $load = Load::where([
                ['id', $load_id],
                ['driver_id', $driver_id],
                ['status', 5]
            ])->count();
        } else {
            $load = Load::where([
                ['id', $load_id],
                ['status', 5]
            ])->count();
        }

        if ($load > 0) {

            Load::where([
                ['id', $load_id],
                ['status', 5]
            ])
                ->update(['status' => 6]);
            $data = [
                'title' => 'درحال ارسال راننده',
                'body' => 'درحال ارسال راننده جهت حمل بار',
                'load_id' => $load_id,
                'notificationType' => 'changeTheStatusToCarriage',
            ];

            $this->sendChangeStatusLoadNotification($load_id, $data);

            return [
                'result' => SUCCESS
            ];
        }

        return [
            'result' => UN_SUCCESS,
            'شما مجاز به تغییر وضعیت نیستید'
        ];
    }

    // تغییر وضعیت به درحال حمل
    public function changeTheStatusToCarriage(Request $request)
    {
        $load_id = $request->load_id;
        $driver_id = $request->driver_id;

        $load = 0;

        if ($driver_id > 0) {
            $load = Load::where([
                ['id', $load_id],
                ['driver_id', $driver_id],
                ['status', 6]
            ])->count();
        } else {
            $load = Load::where([
                ['id', $load_id],
                ['status', 6]
            ])->count();
        }

        if ($load > 0) {


            Load::where([
                ['id', $load_id],
                ['status', 6]
            ])
                ->update(['status' => 7]);

            $data = [
                'title' => 'درحال حمل ',
                'body' => 'درحال حمل بار',
                'load_id' => $load_id,
                'notificationType' => 'changeTheStatusToCarriage',
            ];
            $this->sendChangeStatusLoadNotification($load_id, $data);


            return [
                'result' => SUCCESS
            ];
        }

        return [
            'result' => UN_SUCCESS,
            'شما مجاز به تغییر وضعیت نیستید'
        ];
    }

    // تغییر وضعیت به تخلیه
    public function changeTheStatusToDischarge(Request $request)
    {
        $load_id = $request->load_id;
        $driver_id = $request->driver_id;

        $load = 0;

        if ($driver_id > 0) {
            $load = Load::where([
                ['id', $load_id],
                ['driver_id', $driver_id],
                ['status', 7]
            ])->count();
        } else {
            $load = Load::where([
                ['id', $load_id],
                ['status', 7]
            ])->count();
        }

        if ($load > 0) {


            Load::where([
                ['id', $load_id],
                ['status', 7]
            ])
                ->update(['status' => 8]);

            $data = [
                'title' => 'تخلیه بار',
                'body' => 'بار شما تخلیه شد',
                'load_id' => $load_id,
                'notificationType' => 'changeTheStatusToCarriage',
            ];
            $this->sendChangeStatusLoadNotification($load_id, $data);

            return [
                'result' => SUCCESS
            ];
        }

        return [
            'result' => UN_SUCCESS,
            'شما مجاز به تغییر وضعیت نیستید'
        ];
    }

    // دریافت عنوان وضعیت از روی وضعیت بار
    public static function getLoadStatusTitle($status)
    {
        $loadStatus = LoadStatus::where('status', $status)->first();
        if ($loadStatus)
            return $loadStatus->title;
        return 'بدون وضعیت';
    }

    // ارسال نوتیفیکیشن برای راننده
    public static function sendNewLoadNotificationForDrivers($origin_city_id, $destination_city_id, $load_id)
    {
        $drivers = Driver::join('driver_default_paths', 'driver_default_paths.driver_id', ' = ', 'drivers.id')
            ->where([
                ['driver_default_paths.origin_city_id', $origin_city_id],
                ['driver_default_paths.destination_city_id', $destination_city_id]
            ])
            ->select('drivers.FCM_token')
            ->get();

        $data = [
            'title' => 'بار جدید',
            'body' => 'یک بار جدید دریافت کرده اید',
            'load_id' => $load_id,
            'notificationType' => 'newLoad'
        ];

        foreach ($drivers as $driver) {
            $fcm_token = $driver->FCM_token;

            $url = 'https://fcm.googleapis.com/fcm/send';

            $notification = [
                'body' => $data['body'],
                'sound' => true,
            ];
            $fields = array(
                'to' => $fcm_token,
                'notification' => $notification,
                'data' => $data
            );

            $headers = array(
                'Authorization: key=' . API_ACCESS_KEY_DRIVER,
                'Content-Type: application/json'
            );

            #Send Reponse To FireBase Server
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            $result = curl_exec($ch);
            if ($result === FALSE) {
                die('Oops! FCM Send Error: ' . curl_error($ch));
            }

            curl_close($ch);
        }
    }

    // ارسال نوتیفیکیشن برای مشتری
    public static function sendNotificationForCustomer($customer_id, $load_id)
    {
        $customer = Customer::where('id', $customer_id)->first();

        $data = [
            'title' => 'پایان ثبت قیمت',
            'body' => 'زمان ثبت قیمت پایان یافت',
            'load_id' => $load_id,
            'notificationType' => 'endOfTender',
        ];


        $url = 'https://fcm.googleapis.com/fcm/send';

        $notification = [
            'body' => $data['body'],
            'sound' => true,
        ];
        $fields = array(
            'to' => $customer->FCM_token,
            'notification' => $notification,
            'data' => $data
        );

        $headers = array(
            'Authorization: key=' . API_ACCESS_KEY_USER,
            'Content-Type: application/json'
        );

        #Send Reponse To FireBase Server
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Oops! FCM Send Error: ' . curl_error($ch));
        }

        curl_close($ch);
    }

    // ارسال نوتیفیکیشن برای باربری
    public function sendNewLoadNotificationForBearing($origin_state_id, $load_id)
    {
        $bearingFCM_token = Bearing::where([
            ['state_id', $origin_state_id],
            ['notification', 'enable'],
            ['status', 1],
        ])
            ->whereRaw('LENGTH(FCM_token)>10')
            ->pluck('FCM_token');

        $data = [
            'title' => 'بار جدید',
            'body' => 'یک بار جدید دریافت کرده اید',
            'load_id' => $load_id,
            'notificationType' => 'newLoad'
        ];

        $this->sendNotification($bearingFCM_token, $data, API_ACCESS_KEY_TRANSPORTATION_COMPANY, true);
        //        $input_data = array(
        //            'num' => "یک"
        //        );
        //            $re = SMSController::sendSMSWithPattern($bearing->mobileNumber, "mex36tkepm1of5a", $input_data);

    }

    private function sendNotification($FCM_token, $title, $body)
    {

        $serviceAccountPath = asset('assets/zarin-tarabar-firebase-adminsdk-9x6c3-7dbc939cac.json');
        $serviceAccount = json_decode(file_get_contents($serviceAccountPath), true);

        $clientEmail = $serviceAccount['client_email'];
        $privateKey = $serviceAccount['private_key'];

        $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
        $now = time();
        $expiration = $now + 3600;
        $payload = json_encode([
            'iss' => $clientEmail,
            'scope' => 'https://www.googleapis.com/auth/cloud-platform https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $expiration,
            'iat' => $now
        ]);

        // Encode to base64
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        // Create the signature
        $signatureInput = $base64UrlHeader . "." . $base64UrlPayload;
        openssl_sign($signatureInput, $signature, $privateKey, 'sha256');
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        // Create the JWT
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
        // Exchange JWT for an access token
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]));
        $response = curl_exec($ch);
        curl_close($ch);
        $responseData = json_decode($response, true);
        $accessToken = $responseData['access_token'];

        $url = 'https://fcm.googleapis.com/v1/projects/zarin-tarabar/messages:send';
        $notification = [
            "message" => [
                "token" => $FCM_token,
                "notification" => [
                    "title" => $title,
                    "body" => $body
                ],
            ],
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($notification));
        curl_exec($ch);
        curl_close($ch);
    }

    // Pay for displaying the load information
    public function payForDisplayingTheLoadInformation(Request $request)
    {
        $load_id = $request->load_id;
        $bearing_id = $request->bearing_id;

        $load = Load::where([
            ['id', $load_id],
            ['bearing_id', $bearing_id]
        ])->first();

        if ($load) {
            if ($load->status == 3) {

                $cost = ((($load->price + $load->markrting_price) / 100) * 2) + $load->marketing_price;
                if ($load->numOfTrucks > 0)
                    $cost = ((($load->price * $load->numOfTrucks + $load->markrting_price) / 100) * 2) + $load->marketing_price;

                if ($cost <= 0) {
                    return [
                        'result' => UN_SUCCESS,
                        'message' => 'لطفا دوباره تلاش کنید'
                    ];
                }
                //برای متود پایین باید قیمت بیشتری ارسال شود
                if (BearingController::decreaseFromWallet($bearing_id, $cost)) {

                    Load::where('id', $load_id)
                        ->update([
                            'status' => 4
                        ]);

                    return [
                        'result' => SUCCESS
                    ];
                } else {
                    return [
                        'result' => WALLET_INVENTORY_IS_NOT_ENOUGH,
                        'message' => 'موجودی کیف پول شما کافی نیست'
                    ];
                }
            }
            return [
                'result' => UN_SUCCESS,
                'message' => ' نوع پرداخت شما مجاز نمی باشد'
            ];
        }
        return [
            'result' => UN_SUCCESS,
            'message' => 'درخواست شما مجاز نمی باشد'
        ];
    }

    // درخواست راننده برای حمل بار از طرف باربری
    public function requestDriverForLoad(Load $load)
    {
        try {

            // ارسال نوتیفیکیشن استعلام بار برای رانندگان
            $data = [
                'title' => 'بار جدید',
                'body' => 'یک بار جدید با عنوان ' . $load->title . ' دریافت کردید.',
                'load_id' => $load->id,
                'notificationType' => 'newLoad',
            ];

            $driverFCM_token = Driver::join('fleet_loads', 'fleet_loads.fleet_id', '=', 'drivers.fleet_id')
                ->where('fleet_loads.load_id', $load->id)
                ->whereRaw('LENGTH(FCM_token)>10')
                ->pluck('drivers.FCM_token');

            $this->sendNotification($driverFCM_token, $data, API_ACCESS_KEY_DRIVER, true);

            return [
                'result' => SUCCESS
            ];
        } catch (\Exception $exception) {
            Log::emergency("---------------------------------------------------------------------------------");
            Log::emergency("Error in requestDriverForLoad method ");
            Log::emergency($exception->getMessage());
            Log::emergency("---------------------------------------------------------------------------------");
        }

        return [
            'result' => UN_SUCCESS,
            'message' => 'درخواست شما مجاز نمی باشد'
        ];
    }

    // ارسال نوتیفیکیشن استعلام بار برای رانندگان
    private function sendLoadInquiryNotificationToDrivers($load_id, $proposedPriceForDriver)
    {

        $data = [
            'title' => 'بار جدید',
            'body' => 'استعلام بار جدید با قیمت ' . $proposedPriceForDriver . ' تومان ',
            'load_id' => $load_id,
            'notificationType' => 'newLoad',
        ];

        $drivers = Driver::join('fleet_loads', 'fleet_loads.fleet_id', '=', 'drivers.fleet_id')
            ->where('fleet_loads.load_id', $load_id)
            ->whereRaw('LENGTH(FCM_token)>10')
            ->select('drivers.FCM_token')
            ->get();

        foreach ($drivers as $driver) {
            $this->sendNotification($driver->FCM_token, $data, API_ACCESS_KEY_DRIVER);
        }
    }

    /**
     * @param $proposedPriceForDriver
     * @return mixed
     */
    private function addComa($proposedPriceForDriver)
    {
        if ($proposedPriceForDriver < 1000)
            return $proposedPriceForDriver;
        $temp = '';
        $counter = 0;
        for ($index = strlen($proposedPriceForDriver) - 1; $index > -1; $index--) {
            if ($counter == 3) {
                $temp = $proposedPriceForDriver[$index] . ',' . $temp;
                $counter = 1;
            } else {
                $temp = $proposedPriceForDriver[$index] . $temp;
                $counter++;
            }
        }
        return $temp;
    }

    /**
     * Send new inquiry notification
     * @param $load_id
     * @param $price
     */
    // private function sendNewInquiryNotification($load_id, $price, $driver_id)
    // {
    //     $load = Load::where('id', $load_id)->first();

    //     $bearing = Bearing::where('id', $load->bearing_id)->first();

    //     if ($load->userType == ROLE_CARGo_OWNER) {
    //         $customer = Customer::find($load->user_id);
    //         if (isset($customer->id)) {
    //             $data = [
    //                 'title' => '',
    //                 'body' => '',
    //                 'notificationType' => REFRESH_LOAD_INFO_PAGE
    //             ];
    //             $this->sendNotification($customer->FCM_token, $data, API_ACCESS_KEY_USER);
    //         }
    //     }

    //     $data = [
    //         'title' => 'قیمت جدید در استعلام',
    //         'body' => 'یک قیمت جدید به مبلغ ' . $price . ' تومان  در استعلام ثبت شد',
    //         'load_id' => $load_id,
    //         'notificationType' => 'newInquiryPrice',
    //     ];

    //     if (isset($bearing->FCM_token))
    //         $this->sendNotification($bearing->FCM_token, $data, API_ACCESS_KEY_TRANSPORTATION_COMPANY);

    //     //
    //     //        $drivers = Driver::join('inquiries', 'drivers.id', '=', 'inquiries.driver_id')
    //     //            ->where([
    //     //                ['inquiries.load_id', $load_id],
    //     //                ['inquiries.driver_id', '!=', $driver_id]
    //     //            ])
    //     //            ->select('drivers.FCM_token')
    //     //            ->get();
    //     //
    //     //
    //     //        foreach ($drivers as $driver)
    //     //            $this->sendNotification($driver->FCM_token, $data, API_ACCESS_KEY_DRIVER);

    // }

    /**
     * @param $load_id
     * @param $driver_id
     */
    // ارسال نوتیفیکیشن استعلام بار برای رانندگان
    private function sendNotificationForSelectedDriverAndDoNotSelected($load_id, $driver_id)
    {

        $data = [
            'title' => 'انتخاب راننده',
            'body' => 'شما به عنوان راننده این بار انتخاب شدید',
            'load_id' => $load_id,
            'notificationType' => 'selectedAsLoadDriver',
        ];


        $driver = Driver::where('id', $driver_id)
            ->select('FCM_token')
            ->first();

        $this->sendNotification($driver->FCM_token, $data, API_ACCESS_KEY_DRIVER);

        //        $data = [
        //            'title' => 'انتخاب راننده',
        //            'body' => 'شما به عنوان راننده این بار انتخاب نشدید',
        //            'load_id' => $load_id,
        //            'notificationType' => 'doNotSelectedAsLoadDriver',
        //        ];
        //
        //        $drivers = Driver::join('inquiries', 'drivers.id', '=', 'inquiries.driver_id')
        //            ->where([
        //                ['inquiries.load_id', $load_id],
        //                ['inquiries.driver_id', '!=', $driver_id]
        //            ])
        //            ->select('drivers.FCM_token')
        //            ->get();
        //
        //        foreach ($drivers as $driver) {
        //            $this->sendNotification($driver->FCM_token, $data, API_ACCESS_KEY_DRIVER);
        //        }
    }

    /**
     * @param $load_id
     */
    private function sendChangeStatusLoadNotification($load_id, $data)
    {

        $load = Load::where('id', $load_id)->first();

        $customer = Customer::where('id', $load->user_id)->first();

        $this->sendNotification($customer->FCM_token, $data, API_ACCESS_KEY_USER);
    }

    // ذخیره عکس کابر
    private function savePicOfUsers($picture)
    {
        $picName = null;
        if (strlen($picture)) {
            $fileType = $picture->guessClientExtension();
            if ($picture->isValid() && ($fileType == 'jpg' || $fileType == 'jpeg' || $fileType == 'gif' || $fileType == 'png' || $fileType == 'bmp')) {
                $picName = sha1(time()) . "." . $fileType;
                $picture->move('pictures/loads', $picName);
                $picName = 'pictures/loads/' . $picName;
            }
        }
        return $picName;
    }

    //
    public function requestDriver(Request $request)
    {
        if (\auth('bearing')->check()) {

            Load::where([
                ['bearing_id', \auth('bearing')->id()],
                ['id', $request->load_id]
            ])
                ->update(['proposedPriceForDriver' => $request->proposedPriceForDriver]);

            $this->sendLoadInquiryNotificationToDrivers($request->load_id, $request->proposedPriceForDriver);


            $drivers = [];

            return $this->requestDriverForm($request->load_id, $drivers);
        }

        $message = 'شما مجاز به انجام این عملیات نیستید';
        $alert = 'alert-danger';
        return view('users.alert', compact('message', 'alert'));
    }

    // درخواست راننده
    public function requestDriverForm($load_id, $drivers = [])
    {

        if (\auth('bearing')->check()) {
            $load = Load::where('id', $load_id)
                ->select('*')
                ->first();

            return view('users.requestDriverForm', compact('load', 'drivers'));
        }
        $message = 'شما مجاز به انجام این عملیات نیستید';
        $alert = 'alert-danger';
        return view('users.alert', compact('message', 'alert'));
    }

    // تایید یا رد بار
    public function approveOrRejectLoad(Request $request, $acceptLoadFromLoadList = false)
    {

        if (Auth::check() && ($request->status == 0 || $request->status == -2 || $request->status == -1)) {

            $load = Load::find($request->load_id);

            $message = 'ایران ترابر، بار شما  ' . $load->title . '  تایید نشد، مشاهده دلیل عدم تایید در اطلاعات بار با اپلیکیشن یا سایت.';
            $result = 'نشد، مشاهده دلیل عدم تایید در اطلاعات بار در اپلیکیشن یا سایت.';

            if ($load->userType == ROLE_TRANSPORTATION_COMPANY) {

                $user = Bearing::find($load->user_id);
                $API_ACCESS_KEY = API_ACCESS_KEY_TRANSPORTATION_COMPANY;

                $message = 'ایران ترابر، بار شما با عنوان ' . $load->title;
                if ($request->status == 0) {
                    // ارسال نوتیفیکیشن برای رانندگان
                    //                    $this->requestDriverForLoad($load);

                    $request->status = 4;
                    $message .= ' تایید شد.';
                } else
                    $message .= ' تایید نشد.';
            } else {
                $user = Customer::find($load->user_id);
                $API_ACCESS_KEY = API_ACCESS_KEY_USER;
                if ($request->status == 0) {
                    $result = 'شد';
                    $message = 'ایران ترابر، بار شما  ' . $load->title . '  تایید شد';
                    $this->sendNewLoadNotificationForBearing($load->origin_state_id, $request->load_id);
                } else
                    $result = 'نشد';
            }

            $load->status = $request->status;
            $load->adminMessage = $request->adminMessage;
            $load->loadingRange = $request->loadingRange;
            $load->dischargeRange = $request->dischargeRange;
            $load->save();

            $data = [
                'title' => 'وضعیت بار',
                'body' => $message,
                'notificationType' => 'changeLoadStatus',
                'load_id' => $request->load_id,
            ];

            // اطلاع رسانی به کاربر
            if (isset($user->id)) {
                $this->sendNotification($user->FCM_token, $data, $API_ACCESS_KEY);
                //                $re = SMSController::sendSMSWithPattern($user->mobileNumber, "6e9qlrv6kae79yw", array('result' => $result));
            }
        }

        if ($acceptLoadFromLoadList)
            return true;

        return redirect(url('admin/loadInfo/' . $request->load_id));
    }

    // فرم نمایش اطلعات بار
    public function editLoadInfoForm($load_id)
    {
        $cities = City::orderby('centerOfProvince', 'desc')->get();
        $fleets = Fleet::where('parent_id', '>', 0)->orderBy('parent_id', 'asc')->get();
        $packingTypes = PackingType::get();
        $load = Load::where('id', $load_id)->first();
        $message = '';
        $fleetListArray = '';

        try {
            foreach ($load->fleets as $selectedFleet)
                $fleetListArray .= '{"numOfFleets":"' . $selectedFleet->numOfFleets . '","fleet_id":"' . $selectedFleet->fleet_id . '","title":"' . $selectedFleet->title . '"},';
        } catch (\Exception $exception) {
        }
        $fleetListArray = substr_replace($fleetListArray, "", -1);


        return view('admin.editLoadInfoForm', compact('message', 'cities', 'fleets', 'packingTypes', 'load', 'fleetListArray'));
    }

    // ویرایش اطلاعات بار در وب
    public function editLoadInfoInWeb(Request $request, Load $load)
    {
        return $this->editLoadInfo($request, $load);
        return redirect(url('admin/loadInfo/' . $request->id));
    }

    // ویرایش اطلاعات بار در اپ
    public function editLoadInfo(NewLoadRequest $request, Load $load, $api = false)
    {

        try {

            $senderMobileNumber = isset($request->mobileNumberForCoordination) ? $request->mobileNumberForCoordination : $request->senderMobileNumber;
            $senderMobileNumber = convertFaNumberToEn($senderMobileNumber);

            if (BlockPhoneNumber::where('phoneNumber', $senderMobileNumber)->count()) {
                $message[1] = 'شماره تلفن وارد شده در لیست ممنوعه می باشد، و امکان ثبت بار با شماره تلفن ' . $senderMobileNumber .
                    ' امکان پذیر نمی باشد. لطفا برای دلیل آن با ایران ترابر تماس بگیرید';

                if ($api)
                    return [
                        'result' => false,
                        'message' => $message[1]
                    ];

                return [
                    'result' => UN_SUCCESS,
                    'message' => $message
                ];
            }
        } catch (\Exception $exception) {
        }

        try {

            $request->weight = $this->convertNumbers(str_replace(',', '', $request->weight), false);
            $request->width = $this->convertNumbers(str_replace(',', '', $request->width), false);
            $request->length = $this->convertNumbers(str_replace(',', '', $request->length), false);
            $request->height = $this->convertNumbers(str_replace(',', '', $request->height), false);
            $request->suggestedPrice = $this->convertNumbers(str_replace(',', '', $request->suggestedPrice), false);
            $request->insuranceAmount = $this->convertNumbers(str_replace(',', '', $request->insuranceAmount), false);
            $request->numOfFleets = $this->convertNumbers(str_replace(',', '', $request->numOfFleets), false);
            $request->loadingDate = $this->convertNumbers($request->loadingDate, false);

            $loadPic = null;

            if (isset($request->marketing_price))
                $request->marketing_price = $request->marketing_price;
            else
                $request->marketing_price = 0;

            if (!isset($request->tenderTimeDuration))
                $request->tenderTimeDuration = 15;

            if ($request->image != "noImage") {
                $loadPic = "pictures/loads/" . sha1(time() . $request->user_id) . ".jpg";
                file_put_contents($loadPic, base64_decode($request->pic));
            }

            DB::beginTransaction();

            $load->title = $request->title;
            $load->weight = $this->convertNumbers($request->weight, false);
            $load->width = $this->convertNumbers($request->width, false);
            $load->length = $this->convertNumbers($request->length, false);
            $load->height = $this->convertNumbers($request->height, false);
            $load->loadingAddress = $request->loadingAddress;
            $load->dischargeAddress = $request->dischargeAddress;
            // $load->senderMobileNumber = $request->senderMobileNumber;
            // $load->receiverMobileNumber = $request->receiverMobileNumber;
            $load->loadingDate = $request->loadingDate;
            $load->insuranceAmount = strlen($request->insuranceAmount) ? $request->insuranceAmount : 0;
            $load->suggestedPrice = $request->suggestedPrice;
            $load->marketing_price = $request->marketing_price;
            $load->emergencyPhone = $request->emergencyPhone;
            $load->dischargeTime = $request->dischargeTime;
            $load->fleet_id = $request->fleet_id;
            $load->load_type_id = $request->load_type_id;
            $load->tenderTimeDuration = $request->tenderTimeDuration;
            $load->packing_type_id = $request->packing_type_id;
            $load->loadPic = $loadPic;
            $load->loadMode = $request->loadMode;
            $load->loadingHour = $request->loadingHour;
            $load->loadingMinute = $request->loadingMinute;
            $load->numOfTrucks = $request->numOfTrucks;
            $load->origin_city_id = $request->origin_city_id;
            $load->destination_city_id = $request->destination_city_id;
            $load->originLatitude = $request->originLatitude;
            $load->originLongitude = $request->originLongitude;
            $load->destinationLatitude = $request->destinationLatitude;
            $load->destinationLongitude = $request->destinationLongitude;


            if ($request->loadMode == 'innerCity') {
                $load->origin_latitude = $request->origin_latitude;
                $load->origin_longitude = $request->origin_longitude;
                $load->destination_latitude = $request->destination_latitude;
                $load->destination_longitude = $request->destination_longitude;
            }

            if (isset($request->origin_city_id) && isset($request->destination_city_id)) {

                $load->origin_city_id = $request->origin_city_id;
                $load->destination_city_id = $request->destination_city_id;

                $load->fromCity = $this->getCityName($request->origin_city_id);
                $load->toCity = $this->getCityName($request->destination_city_id);
                try {
                    $city = ProvinceCity::find($request->origin_city_id);
                    if (isset($city->id)) {
                        $load->latitude = $city->latitude;
                        $load->longitude = $city->longitude;
                    }
                } catch (\Exception $exception) {
                }
            }

            $load->origin_state_id = AddressController::geStateIdFromCityId($request->origin_city_id);
            $load->description = $request->description;
            if ($load->suggestedPrice == 0)
                $load->priceBased = 'توافقی';
            else
                $load->priceBased = $request->priceBased;

            $load->proposedPriceForDriver = $request->suggestedPrice;


            if (isset($request->proposedPriceForDriver))
                $load->proposedPriceForDriver = $this->convertNumbers($request->proposedPriceForDriver, false);



            if (isset($request->mobileNumberForCoordination))
                $load->mobileNumberForCoordination = convertFaNumberToEn($request->mobileNumberForCoordination);
            else if (isset($request->senderMobileNumber))
                $load->mobileNumberForCoordination = convertFaNumberToEn($request->senderMobileNumber);


            $load->weightPerTruck = isset($request->weightPerTruck) && $request->weightPerTruck > 0 ? $this->convertNumbers($request->weightPerTruck, false) : 0;
            $load->bulk = isset($request->bulk) ? $request->bulk : 2;
            $load->dangerousProducts = isset($request->dangerousProducts) ? $request->dangerousProducts : false;
            $load->deliveryTime = isset($request->deliveryTime) && $request->deliveryTime > 0 ? $request->deliveryTime : 24;

            $load->save();

            if (isset($load->id) && isset($request->fleetList)) {

                if (!$api)
                    $request->fleetList = json_decode($request->fleetList, true);
                if ($request->fleetList) {

                    FleetLoad::where('load_id', $load->id)->delete();

                    foreach ($request->fleetList as $item) {
                        $fleetLoad = new FleetLoad();
                        $fleetLoad->load_id = $load->id;
                        $fleetLoad->fleet_id = $item['fleet_id'];
                        $fleetLoad->numOfFleets = $item['numOfFleets'];
                        $fleetLoad->userType = $load->userType;
                        if ($request->userType == ROLE_TRANSPORTATION_COMPANY) {
                            $load->proposedPriceForDriver = $request->suggestedPrice;
                            $transportationCompany = Bearing::find($request->user_id);
                            $transportationCompany->countOfLoadsAfterValidityDate -= 1;
                            $transportationCompany->save();
                        }
                        $fleetLoad->save();
                    }

                    try {

                        $load->fleets = FleetLoad::join('fleets', 'fleets.id', 'fleet_loads.fleet_id')
                            ->where('fleet_loads.load_id', $load->id)
                            ->select('fleet_id', 'userType', 'suggestedPrice', 'numOfFleets', 'pic', 'title')
                            ->get();

                        $load->save();
                    } catch (\Exception $exception) {
                        Log::emergency("---------------------------------------------------------");
                        Log::emergency($exception->getMessage());
                        Log::emergency("---------------------------------------------------------");
                    }
                }

                if (isset($request->dateOfCargoDeclaration)) {

                    DateOfCargoDeclaration::where('load_id', $load->id)->delete();

                    $dateOfCargoDeclarations = explode(",", str_replace(" ", "", str_replace("[", "", str_replace("]", "", $request->dateOfCargoDeclaration))));

                    for ($dateOfCargoDeclarationIndex = 0; $dateOfCargoDeclarationIndex < count($dateOfCargoDeclarations); $dateOfCargoDeclarationIndex++) {
                        if (strlen($dateOfCargoDeclarations[$dateOfCargoDeclarationIndex]) > 0) {
                            $dateOfCargoDeclaration = new DateOfCargoDeclaration();
                            $dateOfCargoDeclaration->load_id = $load->id;
                            $dateOfCargoDeclaration->declarationDate = $dateOfCargoDeclarations[$dateOfCargoDeclarationIndex];
                            $dateOfCargoDeclaration->save();
                        }
                    }
                }

                DB::commit();

                if ($api)
                    return [
                        'result' => true,
                        'message' => 'ویرایش انجام شد'
                    ];

                return redirect(url('admin/loadInfo/' . $load->id))->with('success', 'ویرایش انجام شد');
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::emergency($exception);
        }

        if ($api)
            return [
                'result' => false,
                'message' => 'ویرایش انجام نشد! لطفا دوباره تلاش کنید'
            ];

        return back()->with('danger', 'خطا! لطفا دوباره تلاش کنید');
    }

    // نمایش لیست بارها
    public function loadsWithStatusType($status)
    {
        $loads = Load::where('status', $status)
            ->orderby('id', 'desc')
            ->paginate(100);
        $loadStatus = LoadStatus::where('status', $status)
            ->select('title')
            ->first();
        if (!empty($loadStatus))
            $pageTitle = $loadStatus->title;
        $cities = City::orderby('centerOfProvince', 'desc')->get();
        $fleets = Fleet::where('parent_id', '>', 0)->orderBy('parent_id', 'asc')->get();
        return view('admin.loads', compact('loads', 'status', 'cities', 'fleets'));
    }

    // حذف اطلاعات بار توسط ادمین
    public function removeLoadInfo($load_id)
    {
        $load = Load::where('id', $load_id)->first();
        if ($load) {

            Load::where('id', $load_id)->delete();

            //            if ($load->loadPic)
            //                unlink($load->loadPic);

            Tender::where('load_id', $load_id)->delete();

            $message = 'بار مورد نظر حذف شد';
            $alert = 'alert-success';
        } else {
            $message = 'چنین باری وجود ندارد';
            $alert = 'alert-warning';
        }
        return view('admin.alert', compact('message', 'alert'));
    }

    // تغییر نوع فوری
    public function changeUrgentType(Request $request)
    {
        if (Load::where([['id', $request->load_id], ['bearing_id', $request->bearing_id]])->count() > 0) {

            Load::where('id', $request->load_id)
                ->update([
                    'urgent' => $request->urgent,
                    'proposedPriceForDriver' => $request->proposedPriceForDriver,
                    'status' => ON_SELECT_DRIVER
                ]);
            // ارسال نوتیفیکیشن
            $this->sendLoadInquiryNotificationToDrivers($request->load_id, $request->proposedPriceForDriver);
            return ['result' => SUCCESS];
        }
        return ['result' => UN_SUCCESS];
    }

    // حذف بار
    public function removeLoad(Request $request)
    {
        foreach ($request->load_id as $load_id)
            $this->removeLoadInfo($load_id);

        return $this->loadsWithStatusType($request->status);
    }

    public function removeLoadItem(Load $load)
    {
        $load->delete();
        return back()->with('success', 'بار مورد نظر حذف شد');
    }

    // جستجوی بار
    public function searchLoad(Request $request)
    {

        try {

            $conditions = [];
            $loads = [];
            if ($request->fleet_id > 0)
                $loads = FleetLoad::where([
                    ['fleet_id', $request->fleet_id],
                    ['created_at', '>', \date('Y-m-d', strtotime('-1 day', time())) . ' 00:00:00']
                ])->select('load_id')
                    ->take(250)
                    ->get();
            else
                $loads = FleetLoad::select('load_id')
                    ->where('created_at', '>', \date('Y-m-d', strtotime('-1 day', time())) . ' 00:00:00')
                    ->take(250)
                    ->get();

            $ids = $loads->pluck('load_id');

            if (count($loads)) {

                $conditions[] = ['status', ON_SELECT_DRIVER];
                $conditions[] = ['created_at', '>', \date('Y-m-d', strtotime('-1 day', time())) . ' 00:00:00'];

                $loads = Load::where($conditions)
                    ->whereIn('id', $ids)
                    ->select(
                        'id',
                        'weight',
                        'numOfTrucks',
                        'loadingHour',
                        'loadingMinute',
                        'proposedPriceForDriver',
                        'suggestedPrice',
                        'title',
                        'priceBased',
                        'userType',
                        'urgent',
                        'status',
                        'origin_city_id',
                        'destination_city_id',
                        'time',
                        'fleets'
                    )
                    ->orderBy('id', 'desc')
                    ->take(150)
                    ->get();

                if (count($loads)) {
                    return [
                        'result' => SUCCESS,
                        'loads' => $loads
                    ];
                }
            }
        } catch (\Exception $exception) {
            Log::emergency("searchLoad : " + $exception->getMessage());
        }

        return [
            'result' => 0,
            'message' => 'باری مطابق موارد انتخاب شده پیدا نشد'
        ];
    }

    // جستجوی بار برای راننده
    public function searchLoadForDriver(Request $request, Driver $driver)
    {
        try {

            $rows = 50;
            $conditions = [];
            $loads = [];

            if ($request->packingType_id > 0)
                $conditions[] = ['loads.packingType_id', $request->packing_type_id];
            $conditions[] = ['loads.created_at', '>', \date('Y-m-d', strtotime('-1 day', time())) . ' 00:00:00'];
            $conditions[] = ['loads.status', ON_SELECT_DRIVER];

            $fleet_id = $driver->fleet_id;
            if ($request->fleet_id > 0)
                $fleet_id = $request->fleet_id;
            $conditions[] = ['fleet_loads.fleet_id', $fleet_id];
            $conditions[] = ['fleet_loads.created_at', '>', \date('Y-m-d', strtotime('-1 day', time())) . ' 00:00:00'];

            if (isset($request->lastLoadId))
                if ($request->lastLoadId > 0) {
                    $conditions[] = ['loads.id', '<', $request->lastLoadId];
                    $rows = 25;
                }
            $selectedLoadingCitiesIds = isset($request->selectedLoadingCitiesIds) ? json_decode($request->selectedLoadingCitiesIds) : [];
            $selectedDischargeCitiesIds = isset($request->selectedDischargeCitiesIds) ? json_decode($request->selectedDischargeCitiesIds) : [];

            // TODO : اجرای کوئری فقط یک بار و حذف از if های مختلف
            if (count($selectedLoadingCitiesIds) && count($selectedDischargeCitiesIds))
                $loads = Load::join('fleet_loads', 'fleet_loads.load_id', 'loads.id')
                    ->where($conditions)
                    ->whereIn('loads.origin_city_id', $selectedLoadingCitiesIds)
                    ->whereIn('loads.destination_city_id', $selectedDischargeCitiesIds)
                    ->select(
                        'loads.id',
                        'loads.status',
                        'loads.weight',
                        'loads.loadingDate',
                        'loads.title',
                        'loads.priceBased',
                        'loads.proposedPriceForDriver',
                        'loads.suggestedPrice',
                        'loads.origin_city_id',
                        'loads.mobileNumberForCoordination',
                        'loads.destination_city_id',
                        'loads.fromCity',
                        'loads.toCity',
                        'loads.urgent',
                        'loads.time',
                        'loads.fleets'
                    )
                    ->orderBy('urgent', 'desc')
                    ->orderBy('id', 'desc')
                    ->take($rows)
                    ->get();
            else if (count($selectedLoadingCitiesIds) && count($selectedDischargeCitiesIds) == 0)
                $loads = Load::join('fleet_loads', 'fleet_loads.load_id', 'loads.id')
                    ->where($conditions)
                    ->whereIn('loads.origin_city_id', $selectedLoadingCitiesIds)
                    ->select(
                        'loads.id',
                        'loads.status',
                        'loads.weight',
                        'loads.loadingDate',
                        'loads.title',
                        'loads.priceBased',
                        'loads.proposedPriceForDriver',
                        'loads.suggestedPrice',
                        'loads.origin_city_id',
                        'loads.destination_city_id',
                        'loads.mobileNumberForCoordination',
                        'loads.urgent',
                        'loads.fromCity',
                        'loads.toCity',
                        'loads.time',
                        'loads.fleets'
                    )
                    ->orderBy('urgent', 'desc')
                    ->orderBy('id', 'desc')
                    ->take($rows)
                    ->get();
            else if (count($selectedLoadingCitiesIds) == 0 && count($selectedDischargeCitiesIds))
                $loads = Load::join('fleet_loads', 'fleet_loads.load_id', 'loads.id')
                    ->where($conditions)
                    ->whereIn('loads.destination_city_id', $selectedDischargeCitiesIds)
                    ->select(
                        'loads.id',
                        'loads.status',
                        'loads.weight',
                        'loads.loadingDate',
                        'loads.title',
                        'loads.priceBased',
                        'loads.proposedPriceForDriver',
                        'loads.suggestedPrice',
                        'loads.origin_city_id',
                        'loads.mobileNumberForCoordination',
                        'loads.destination_city_id',
                        'loads.urgent',
                        'loads.time',
                        'loads.fromCity',
                        'loads.toCity',
                        'loads.fleets'
                    )
                    ->orderBy('urgent', 'desc')
                    ->orderBy('id', 'desc')
                    ->take($rows)
                    ->get();
            else
                $loads = Load::join('fleet_loads', 'fleet_loads.load_id', 'loads.id')
                    ->where($conditions)
                    ->select(
                        'loads.id',
                        'loads.status',
                        'loads.weight',
                        'loads.loadingDate',
                        'loads.title',
                        'loads.priceBased',
                        'loads.proposedPriceForDriver',
                        'loads.suggestedPrice',
                        'loads.origin_city_id',
                        'loads.mobileNumberForCoordination',
                        'loads.destination_city_id',
                        'loads.urgent',
                        'loads.time',
                        'loads.fromCity',
                        'loads.toCity',
                        'loads.fleets'
                    )
                    ->orderBy('urgent', 'desc')
                    ->orderBy('id', 'desc')
                    ->take($rows)
                    ->get();

            if (count($loads)) {
                return [
                    'result' => SUCCESS,
                    'currentTime' => time(),
                    'loads' => $loads
                ];
            }
        } catch (\Exception $exception) {
            Log::emergency($exception);
        }

        return [
            'result' => 0,
            'message' => 'باری مطابق موارد انتخاب شده پیدا نشد'
        ];
    }

    public function searchLoadInWeb(Request $request)
    {
        $fleet_id = $request->fleet_id;
        $origin_city_id = $request->origin_city_id;
        $destination_city_id = $request->destination_city_id;
        $status = $request->status;

        $condition = [];

        if ($fleet_id > 0)
            $condition[] = ['loads.fleet_id', $fleet_id];
        if ($origin_city_id > 0)
            $condition[] = ['loads.origin_city_id', $origin_city_id];
        if ($destination_city_id > 0)
            $condition[] = ['loads.destination_city_id', $destination_city_id];

        $condition[] = ['loads.status', $status];

        $condition[] = ['loads.created_at', '>', \date('Y-m-d h:i:s', strtotime('-1 day', time()))];

        $loads = Load::join('province_cities as originCity', 'loads.origin_city_id', 'originCity.id')
            ->join('province_cities as destinationCity', 'loads.destination_city_id', 'destinationCity.id')
            ->join('load_statuses', 'load_statuses.status', 'loads.status')
            ->where($condition)
            ->select(
                'loads.*',
                'originCity.name as from',
                'destinationCity.name as to',
                'originCity.state as stateFrom',
                'destinationCity.state as stateTo'
            )
            ->orderBy('loads.id', 'desc')
            ->paginate(200);

        $loadStatus = LoadStatus::where('status', $status)
            ->select('title')
            ->first();

        $pageTitle = $loadStatus->title;
        $cities = City::orderby('centerOfProvince', 'desc')->get();
        $fleets = Fleet::where('parent_id', '>', 0)->orderBy('parent_id', 'asc')->get();
        return view('admin.loads', compact('loads', 'pageTitle', 'status', 'cities', 'fleets'));
    }

    // تغییر وضعیت بار به مرحله قبل
    public function changeLoadStatusToPastStatus($load_id)
    {
        Load::where([
            ['id', $load_id],
            ['status', '>', -2]
        ])->decrement('status');

        return redirect('admin/loadInfo/' . $load_id);
    }

    // بررسی ثبت درخواست حمل توسط راننده
    public function checkDriverInquiry($driver_id, $load_id)
    {
        try {
            return [
                'result' =>
                Inquiry::where([
                    ['load_id', $load_id],
                    ['driver_id', $driver_id]
                ])->count()
            ];
        } catch (\Exception $exception) {
        }
        return [
            'result' => 0
        ];
    }

    // اضافه کردن ناوگان به بار توسط صاحب بار
    public function addFleetToLoadByCustomer(Request $request)
    {
        try {

            DB::beginTransaction();

            if (FleetLoad::where([['load_id', $request->load_id], ['userType', ROLE_CARGo_OWNER], ['fleet_id', $request->fleet_id]])->count() > 0)
                return [
                    'result' => false,
                    'message' => 'ناوگان مورد نظر قبلا ثبت شده است!'
                ];

            $fleetLoad = new FleetLoad();
            $fleetLoad->load_id = $request->load_id;
            $fleetLoad->fleet_id = $request->fleet_id;
            $fleetLoad->numOfFleets = $request->numOfFleets;
            $fleetLoad->userType = ROLE_CARGo_OWNER;
            $fleetLoad->suggestedPrice = $request->suggestedPrice;
            if (isset($request->priceBase))
                $fleetLoad->priceBase = $request->priceBase;
            $fleetLoad->save();

            $load = Load::find($request->load_id);

            $load->fleets = FleetLoad::join('fleets', 'fleets.id', 'fleet_loads.fleet_id')
                ->where('fleet_loads.load_id', $load->id)
                ->select('fleet_id', 'userType', 'priceBase', 'suggestedPrice', 'numOfFleets', 'pic', 'title')
                ->get();

            $load->save();

            DB::commit();

            return [
                'result' => true,
                'data' => $fleetLoad,
                'message' => null,
            ];
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::emergency($exception->getMessage());
        }

        return [
            'result' => false,
            'message' => 'خطا در ذخیره ناوگان'
        ];
    }


    public function removeFleetOfLoadByCustomer(FleetLoad $fleetLoad)
    {
        $fleetLoad->delete();

        $load = Load::find($fleetLoad->load_id);

        $load->fleets = FleetLoad::join('fleets', 'fleets.id', 'fleet_loads.fleet_id')
            ->where('fleet_loads.load_id', $fleetLoad->load_id)
            ->select('fleet_id', 'userType', 'suggestedPrice', 'numOfFleets', 'pic', 'title')
            ->get();

        $load->save();
    }

    // اضافه کردن ناوگان به بار توسط باربری
    public function addFleetToLoadByTransportationCompany(Request $request)
    {
        try {
            $fleetLoad = new FleetLoad();
            $fleetLoad->load_id = $request->load_id;
            $fleetLoad->fleet_id = $request->fleet_id;
            $fleetLoad->numOfFleets = $request->numOfFleets;
            $fleetLoad->userType = ROLE_TRANSPORTATION_COMPANY;
            $fleetLoad->suggestedPrice = $request->suggestedPrice;
            $fleetLoad->save();

            return [
                'result' => true,
                'data' => $fleetLoad,
                'message' => null,
            ];
        } catch (\Exception $exception) {
            Log::emergency($exception->getMessage());
        }
        return [
            'result' => false,
            'message' => 'خطا در ذخیره ناوگان'
        ];
    }

    // حذف ناوگان انتخاب شده از لیست توسط باربری
    public function removeFleetToLoadByTransportationCompany($fleet_load_id)
    {
        try {
            $fleet_load = FleetLoad::find($fleet_load_id);
            $fleet_load->delete();
            return [
                'result' => true,
                'message' => null,
                'data' => null
            ];
        } catch (\Exception $exception) {
            Log::emergency($exception->getMessage());
        }
        return [
            'result' => false,
            'message' => null,
            'data' => null
        ];
    }

    // جستجوی بارهای نزدیک من
    public function searchTheNearestCargo(Request $request, Driver $driver, $city = null, $radius = 1000)
    {
        $rows = 150;

        try {
            if (isset($city->latitude) && $city->longitude) {
                $latitude = $city->latitude;
                $longitude = $city->longitude;
            } else {
                $latitude = $request->latitude;
                $longitude = $request->longitude;
            }

            $fleet_id = $driver->fleet_id;

            // اگر جستجو براساس فیلتر بود
            // if (isset($request->filter) && $request->filter)
            //     $fleet_id = $request->fleet_id;

            $conditions[] = ['fleet_loads.fleet_id', $fleet_id];
            $conditions[] = ['loads.status', ON_SELECT_DRIVER];
            $conditions[] = ['loads.created_at', '>', \date('Y-m-d h:i:s', strtotime('-1 day', time()))];
            $conditions[] = ['loads.driverCallCounter', '>', 0];

            if (isset($request->lastLoadId)) {
                $rows = 25;
                if ($request->lastLoadId > 0) {
                    $conditions[] = ['loads.id', '<', $request->lastLoadId];
                }
            }
            $haversine = "(6371 * acos(cos(radians(" . $latitude . "))
                    * cos(radians(`latitude`))
                    * cos(radians(`longitude`)
                    - radians(" . $longitude . "))
                    + sin(radians(" . $latitude . "))
                    * sin(radians(`latitude`))))";

            $loads = Load::join('fleet_loads', 'fleet_loads.load_id', 'loads.id')
                ->select(
                    'loads.id',
                    'loads.suggestedPrice',
                    'loads.title',
                    'loads.priceBased',
                    'loads.userType',
                    'loads.urgent',
                    'loads.mobileNumberForCoordination',
                    'loads.origin_city_id',
                    'loads.destination_city_id',
                    'loads.time',
                    'loads.fromCity',
                    'loads.toCity',
                    'loads.fleets',
                    'loads.created_at',
                )
                ->where($conditions)
                ->selectRaw("{$haversine} AS distance")
                ->whereRaw("{$haversine} < ?", $radius)
                ->orderBy('distance', 'asc')
                ->orderByDesc('created_at')
                ->take($rows)
                ->get();

            return [
                'result' => SUCCESS,
                'loads' => $loads
                // 'currentTime' => time(),
            ];
        } catch (\Exception $exception) {
            Log::emergency("-------------------------------------------------------------------------");
            Log::emergency("LoadController : searchTheNearestCargo");
            Log::emergency($exception);
            Log::emergency("-------------------------------------------------------------------------");
        }

        return [
            'result' => UN_SUCCESS,
            'message' => 'درحال حاضر باری در محدوده شما موجود نیست'
        ];
    }

    // درخواست اطلاعات رانندگان درحال حمل بار
    public function requestDriversInfoOfCargo(Driver $driver)
    {

        $driver->latitude = 0;
        $driver->longitude = 0;

        return [
            'result' => true,
            'data' => [
                'driver' => $driver,
                'serverKeyDriver' => API_ACCESS_KEY_DRIVER,
                'serverKeyTransportationCompanies' => API_ACCESS_KEY_TRANSPORTATION_COMPANY,
            ],
            'message' => null
        ];
    }

    // درخواست لیست رانندگان بار
    public function requestLoadDriversList(Load $load)
    {

        try {

            $drivers = Driver::join('driver_loads', 'driver_loads.driver_id', 'drivers.id')
                ->where('driver_loads.load_id', $load->id)
                ->select('drivers.id', 'drivers.name', 'drivers.lastName', 'drivers.FCM_token', 'drivers.driverImage as pic', 'drivers.fleet_id')
                ->get();

            if (count($drivers))
                return [
                    'result' => true,
                    'data' => [
                        'drivers' => $drivers,
                        'serverKeyDriver' => API_ACCESS_KEY_DRIVER,
                        'serverKeyTransportationCompanies' => API_ACCESS_KEY_TRANSPORTATION_COMPANY,
                    ],
                    'message' => null
                ];
        } catch (\Exception $exception) {
        }

        return [
            'result' => false,
            'data' => null,
            'message' => 'راننده ای برای این بار ثبت نشده است'
        ];
    }

    // درخواست لیست رانندگان بار جهت کنترل ناوگان
    public function requestLoadDriversListForFleetControl(Load $load, $userType, $mobileNumber)
    {

        $versionWithFleetControl = 58;

        try {

            $serverKeyUserIndex = 'serverKeyCustomer';

            $user = [];
            if ($userType == ROLE_TRANSPORTATION_COMPANY) {
                $user = Bearing::where('mobileNumber', $mobileNumber)->first();
                $serverKeyUserIndex = 'serverKeyTransportationCompanies';
            } else
                $user = Customer::where('mobileNumber', $mobileNumber)->first();

            if (isset($user->id)) {

                $drivers = Driver::join('driver_loads', 'driver_loads.driver_id', 'drivers.id')
                    ->where('driver_loads.load_id', $load->id)
                    ->select('drivers.id', 'drivers.version', 'drivers.name', 'drivers.lastName', 'drivers.FCM_token', 'drivers.driverImage as pic', 'drivers.fleet_id')
                    ->get();

                if (count($drivers)) {

                    if ($user->numOfFleetControl > 0 && $load->fleetControl == false) {
                        $load->fleetControl = true;
                        $load->save();
                        $user->numOfFleetControl--;
                        $user->save();
                    }

                    if ($load->fleetControl == false)
                        return [
                            'result' => true,
                            'data' => [
                                'paid' => false,
                                'wallet' => $user->wallet,
                                'fleetControlPackagesInfo' => getFleetControlPackagesInfo(),
                                'drivers' => [],
                                'serverKeyDriver' => '',
                                $serverKeyUserIndex => '',
                                'tel' => TELL,
                                'bankName' => BANK_NAME,
                                'cardOwner' => CARD_NUMBER,
                                'banckCardOwner' => BANK_CARD_OWNER,
                                'versionWithFleetControl' => $versionWithFleetControl
                            ],
                            'message' => null
                        ];

                    $apiKey = '';
                    if ($userType == ROLE_TRANSPORTATION_COMPANY)
                        $apiKey = API_ACCESS_KEY_TRANSPORTATION_COMPANY;
                    else if ($userType == ROLE_CUSTOMER)
                        $apiKey = API_ACCESS_KEY_USER;

                    if (strlen($apiKey))
                        return [
                            'result' => true,
                            'data' => [
                                'paid' => true,
                                'drivers' => $drivers,
                                'serverKeyDriver' => API_ACCESS_KEY_DRIVER,
                                $serverKeyUserIndex => $apiKey,
                                'tel' => TELL,
                                'bankName' => BANK_NAME,
                                'cardOwner' => CARD_NUMBER,
                                'banckCardOwner' => BANK_CARD_OWNER,
                                'versionWithFleetControl' => $versionWithFleetControl
                            ],
                            'message' => null
                        ];
                } else
                    return [
                        'result' => false,
                        'message' => 'راننده ای برای این بار ثبت نشده است'
                    ];

                return [
                    'result' => false,
                    'message' => ' کاربر معتبر نمی باشد.'
                ];
            }
        } catch (\Exception $exception) {
        }

        return [
            'result' => false,
            'data' => [
                'countOfDrivers' => 0,
                'paid' => true,
                'tel' => TELL,
                'bankName' => BANK_NAME,
                'cardOwner' => CARD_NUMBER,
                'banckCardOwner' => BANK_CARD_OWNER,
                'versionWithFleetControl' => $versionWithFleetControl
            ],
            'message' => 'راننده ای برای این بار ثبت نشده است'
        ];
    }

    // تایید بار در لیست بارها
    public function acceptLoadFromLoadList(Load $load)
    {
        $request = new Request();
        $request->status = 0;
        $request->load_id = $load->id;
        $request->adminMessage = '';
        $request->loadingRange = '';
        $request->dischargeRange = '';

        if ($this->approveOrRejectLoad($request, true))
            return back()->with('success', 'بار مورد نظر تایید شد');

        return back()->with('danger', 'خطا در تایید بار');
    }

    // لیست بارهای ثبت شده توسط اپراتورها
    public function listOfLoadsByOperator()
    {
        $users = null;
        if (\auth()->user()->role == ROLE_ADMIN || \auth()->id() == 13 || in_array('listOfLoadsByOperator', \auth()->user()->userAccess))
            $users = User::get();
        else
            $users = User::where('id', \auth()->id())->get();

        $countOfToday = Load::where([
            ['operator_id', '>', 0],
            ['created_at', '>', date('Y-m-d') . ' 00:00:00']
        ])->count();

        $countOfThisWeek = Load::where([
            ['operator_id', '>', 0],
            ['created_at', '>', getCurrentWeekSaturdayDate()]
        ])->count();


        $countOfAll = Load::where('operator_id', '>', 0)->count();

        return view('admin.listOfLoadsByOperator', compact('users', 'countOfToday', 'countOfThisWeek', 'countOfAll'));
    }

    public function getLoadListFromDate(Driver $driver, $date, $fleetId = 0)
    {

        try {
            DriverActivity::firstOrCreate([
                'driver_id' => $driver->id,
                'persianDate' => DateController::createPersianDate()
            ]);
        } catch (\Exception $exception) {
            Log::emergency($exception->getMessage());
        }

        try {
            try {

                $checkDate = explode('-', $date);
                if (isset($checkDate[0]) && $checkDate[0] > 2000) {
                    $dateController = new DateController();
                    $date = $dateController->gregorian_to_jalali($checkDate[0], $checkDate[1], $checkDate[2], '-');
                }
            } catch (\Exception $exception) {
            }

            if ($fleetId == 0)
                $fleetId = $driver->fleet_id;

            $loadIds = FleetLoad::where('fleet_id', $fleetId)->pluck('load_id');

            $loads = Load::where([
                ['status', ON_SELECT_DRIVER],
                ['loadingDate', str_replace('-', '/', $date)]
            ])
                ->whereIn('id', $loadIds)
                ->select(
                    'id',
                    'weight',
                    'numOfTrucks',
                    'loadingHour',
                    'loadingMinute',
                    'proposedPriceForDriver',
                    'suggestedPrice',
                    'title',
                    'priceBased',
                    'userType',
                    'urgent',
                    'status',
                    'origin_city_id',
                    'destination_city_id',
                    'fromCity',
                    'toCity',
                    'created_at',
                    'time',
                    'fleets'
                )
                ->skip(0)
                ->take(200)
                ->orderBy('id', 'desc')
                ->get();

            if (count($loads))
                return [
                    'result' => true,
                    'loads' => $loads,
                    'currentTime' => time(),
                    'fleet_id' => $fleetId
                ];
        } catch (\Exception $exception) {
            Log::emergency($exception->getMessage());
        }
        return [
            'result' => false,
            'message' => 'درحال حاضر باری برای تاریخ انتخابی شما آماده نیست'
        ];
    }

    // بارهای موجود در مقصد
    public function LoadsInDestinationCity(Driver $driver, string $city, $radius = 1000)
    {
        $rows = 150;
        $province_city = ProvinceCity::findOrFail($city);

        try {
            $latitude = $province_city->latitude;
            $longitude = $province_city->longitude;


            $fleet_id = $driver->fleet_id;

            // اگر جستجو براساس فیلتر بود
            // if (isset($request->filter) && $request->filter)
            //     $fleet_id = $request->fleet_id;

            $conditions[] = ['fleet_loads.fleet_id', $fleet_id];
            $conditions[] = ['loads.status', ON_SELECT_DRIVER];
            $conditions[] = ['loads.created_at', '>', \date('Y-m-d h:i:s', strtotime('-1 day', time()))];
            $conditions[] = ['loads.driverCallCounter', '>', 0];

            // if (isset($request->lastLoadId)) {
            //     $rows = 25;
            //     if ($request->lastLoadId > 0) {
            //         $conditions[] = ['loads.id', '<', $request->lastLoadId];
            //     }
            // }
            $haversine = "(6371 * acos(cos(radians(" . $latitude . "))
                    * cos(radians(`latitude`))
                    * cos(radians(`longitude`)
                    - radians(" . $longitude . "))
                    + sin(radians(" . $latitude . "))
                    * sin(radians(`latitude`))))";

            $loads = Load::join('fleet_loads', 'fleet_loads.load_id', 'loads.id')
                ->select(
                    'loads.id',
                    'loads.suggestedPrice',
                    'loads.title',
                    'loads.priceBased',
                    'loads.userType',
                    'loads.urgent',
                    'loads.mobileNumberForCoordination',
                    'loads.origin_city_id',
                    'loads.destination_city_id',
                    'loads.time',
                    'loads.fromCity',
                    'loads.toCity',
                    'loads.fleets',
                    'loads.created_at',
                )
                ->where($conditions)
                ->selectRaw("{$haversine} AS distance")
                ->whereRaw("{$haversine} < ?", $radius)
                ->orderBy('distance', 'asc')
                ->orderByDesc('created_at')
                ->take($rows)
                ->get();

            return [
                'result' => SUCCESS,
                'loads' => $loads
                // 'currentTime' => time(),
            ];
        } catch (\Exception $exception) {
            Log::emergency("-------------------------------------------------------------------------");
            Log::emergency("LoadController : searchTheNearestCargo");
            Log::emergency($exception);
            Log::emergency("-------------------------------------------------------------------------");
        }

        return [
            'result' => UN_SUCCESS,
            'message' => 'درحال حاضر باری در محدوده شما موجود نیست'
        ];

        // return $this->searchTheNearestCargo($request, $driver, $city, 200);
    }

    // حذف بار
    public function removeOwnerLoad(string $load, string $owner)
    {
        Load::where('id', $load)
            ->where('userType', 'owner')
            ->where('user_id', $owner)
            ->delete();
        return response()->json(['result' => true], 200);
    }

    // تکرار بار
    public function repeatOwnerLoad(string $load)
    {
        $task = Load::withTrashed()->find($load);
        if (BlockPhoneNumber::where('phoneNumber', $task->senderMobileNumber)->count()) {
            $message[1] = 'شماره تلفن وارد شده در لیست ممنوعه می باشد، و امکان ثبت بار با شماره تلفن ' . $task->senderMobileNumber .
                ' امکان پذیر نمی باشد. لطفا برای دلیل آن با ایران ترابر تماس بگیرید';
            return [
                'result' => UN_SUCCESS,
                'message' => $message
            ];
        }

        $load = $task->replicate();
        $load->created_at = now();
        $load->loadingDate = gregorianDateToPersian(date('Y-m-d', time()), '-');
        $load->date = gregorianDateToPersian(date('Y-m-d', time()), '-');
        $load->dateTime = date('H:i:s');
        $load->loadingHour = date('h');
        $load->loadingMinute = date('m');
        $load->deleted_at = null;
        $load->driverVisitCount = 0;
        $load->time = time();
        $load->urgent = 1;
        $load->save();
        try {
            if (isset($load->id)) {
                $fleetLists = FleetLoad::where('load_id', $task->id)->get();

                foreach ($fleetLists as $item) {

                    $fleetLoad = new FleetLoad();
                    $fleetLoad->load_id = $load->id;
                    $fleetLoad->fleet_id = $item->fleet_id;
                    $fleetLoad->numOfFleets = $item->numOfFleets;
                    $fleetLoad->userType = $item->userType;
                    $fleetLoad->save();

                    try {
                        $persian_date = gregorianDateToPersian(date('Y/m/d', time()), '/');

                        $cargoReport = CargoReportByFleet::where('fleet_id', $fleetLoad->fleet_id)
                            ->where('date', $persian_date)
                            ->first();

                        if (isset($cargoReport->id)) {
                            $cargoReport->count_owner += 1;
                            $cargoReport->save();
                        } else {
                            $cargoReportNew = new CargoReportByFleet;
                            $cargoReportNew->fleet_id = $fleetLoad->fleet_id;
                            $cargoReportNew->count_owner = 1;
                            $cargoReportNew->date = $persian_date;
                            $cargoReportNew->save();
                        }
                    } catch (Exception $e) {
                        Log::emergency("Error cargo report by fleets: " . $e->getMessage());
                    }
                }

                try {

                    $load->fleets = FleetLoad::join('fleets', 'fleets.id', 'fleet_loads.fleet_id')
                        ->where('fleet_loads.load_id', $load->id)
                        ->select('fleet_id', 'userType', 'suggestedPrice', 'numOfFleets', 'pic', 'title')
                        ->get();

                    $fleets = json_decode($load->fleets, true);
                    $loadDuplicates = Load::where('userType', 'operator')
                        ->where('mobileNumberForCoordination', $load->mobileNumberForCoordination)
                        ->where('origin_city_id', $load->origin_city_id)
                        ->where('destination_city_id', $load->destination_city_id)
                        ->where('fleets', 'LIKE', '%' . $fleets[0]['fleet_id'] . '%')
                        ->get();

                    if (count($loadDuplicates) > 0) {
                        foreach ($loadDuplicates as $loadDuplicate) {
                            $loadDuplicate->delete();
                        }
                    }

                    $load->save();
                } catch (\Exception $exception) {
                    Log::emergency("---------------------------------------------------------");
                    Log::emergency($exception->getMessage());
                    Log::emergency("---------------------------------------------------------");
                }
                DB::commit();
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
        try {
            $backup = new LoadBackup();
            $backup->id = $load->id;
            $backup->title = $load->title;
            $backup->weight = $load->weight;
            $backup->width = $load->width;
            $backup->length = $load->length;
            $backup->height = $load->height;
            $backup->loadingAddress = $load->loadingAddress;
            $backup->dischargeAddress = $load->dischargeAddress;
            $backup->senderMobileNumber = $load->senderMobileNumber;
            $backup->receiverMobileNumber = $load->receiverMobileNumber;
            $backup->insuranceAmount = $load->insuranceAmount;
            $backup->suggestedPrice = $load->suggestedPrice;
            $backup->marketing_price = $load->marketing_price;
            $backup->emergencyPhone = $load->emergencyPhone;
            $backup->dischargeTime = $load->dischargeTime;
            $backup->fleet_id = $load->fleet_id;
            $backup->load_type_id = $load->load_type_id;
            $backup->tenderTimeDuration = $load->tenderTimeDuration;
            $backup->packing_type_id = $load->packing_type_id;
            $backup->loadPic = $load->loadPic;
            $backup->user_id = $load->user_id;
            $backup->loadMode = $load->loadMode;
            $backup->loadingHour = $load->loadingHour;
            $backup->loadingMinute = $load->loadingMinute;
            $backup->numOfTrucks = $load->numOfTrucks;
            $backup->origin_city_id = $load->origin_city_id;
            $backup->destination_city_id = $load->destination_city_id;
            $backup->fromCity = $load->fromCity;
            $backup->toCity = $load->toCity;
            $backup->loadingDate = $load->loadingDate;
            $backup->time = $load->time;
            $backup->latitude = $load->latitude;
            $backup->longitude = $load->longitude;
            $backup->weightPerTruck = $load->weightPerTruck;
            $backup->bulk = $load->bulk;
            $backup->dangerousProducts = $load->dangerousProducts;
            $backup->origin_state_id = $load->origin_state_id;
            $backup->description = $load->description;
            $backup->priceBased = $load->priceBased;
            $backup->bearing_id = $load->bearing_id;
            $backup->proposedPriceForDriver = $load->proposedPriceForDriver;
            $backup->operator_id = $load->operator_id;
            $backup->userType = $load->userType;
            $backup->origin_longitude = $load->origin_longitude;
            $backup->destination_longitude = $load->destination_longitude;
            $backup->mobileNumberForCoordination = $load->mobileNumberForCoordination;
            $backup->storeFor = $load->storeFor;
            $backup->status = $load->status;
            $backup->fleets = $load->fleets;
            $backup->deliveryTime = $load->deliveryTime;
            $backup->save();
        } catch (\Exception $e) {
            Log::emergency("========================= Load Backup ==================================");
            Log::emergency($e->getMessage());
            Log::emergency("==============================================================");
        }
        return response()->json([
            'result' => true,
            'load_id' => $load->id
        ], 200);
    }


    // فرم ثبت بار توسط صاحب بار
    public function createNewLoadForm($storeFor)
    {
        $cities = City::orderby('centerOfProvince', 'desc')->get();

        $fleets = Fleet::where('parent_id', '>', 0)->orderBy('parent_id', 'asc')->get();

        return view('users.createNewLoadForm', compact('cities', 'fleets', 'storeFor'));
    }

    // دریافت لیست بارهای مشتری
    public function getCustomerLoadsList()
    {
        $loads = Load::where('user_id', \auth('customer')->id())
            ->orderBy('id', 'desc')
            ->get();
        return view('users.loads', compact('loads'));
    }

    /**
     * @return mixed
     */
    private function getCityName($city_id)
    {
        try {
            $city = ProvinceCity::where('id', $city_id)->where('parent_id', '!=', 0)->select('name', 'parent_id')->first();
            $state = ProvinceCity::where('id', $city->parent_id)->first();
            if (isset($city->name))
                return $state->name . ', ' . $city->name;
        } catch (\Exception $e) {
        }
        return '';
    }

    // حذف بار روزهای گذشته
    public function deletePreviousLoads()
    {
        $date = date('Y-m-d', strtotime('-2 day', time())) . ' 00:00:00';
        Load::where('created_at', '<', $date)
            ->select('id', 'created_at')
            ->delete();
    }

    // گرفتن نام شهرستان از آدرس کامل
    private function getCountyFromFullAddress($fullAddress)
    {

        $fullAddress = str_replace(',', ' ', $fullAddress);
        $fullAddress = explode(' ', $fullAddress);

        for ($i = 0; $i < count($fullAddress); $i++) {
            try {
                if ($i > 0 && $fullAddress[$i - 1] == "شهرستان") {

                    $city = ProvinceCity::where('name', $fullAddress[$i])->where('parent_id', '!=', 0)->first();
                    if (isset($city->id))
                        return $city;

                    $fullAddress[$i] = str_replace('ک', 'ك', $fullAddress[$i]);
                    $city = ProvinceCity::where('name', $fullAddress[$i])->where('parent_id', '!=', 0)->first();
                    if (isset($city->id))
                        return $city;

                    $fullAddress[$i] = str_replace('ی', 'ي', $fullAddress[$i]);
                    $city = ProvinceCity::where('name', $fullAddress[$i])->where('parent_id', '!=', 0)->first();
                    if (isset($city->id))
                        return $city;

                    $fullAddress[$i] = str_replace('ا', 'أ', $fullAddress[$i]);
                    $city = ProvinceCity::where('name', $fullAddress[$i])->where('parent_id', '!=', 0)->first();
                    if (isset($city->id))
                        return $city;
                }
            } catch (\Exception $e) {
                Log::emergency(" --------------------------- getCountyFromFullAddress -----------------------------");
                Log::emergency($e->getMessage());
                Log::emergency(" ------------------------------------------------------------------------------");
            }
        }

        return "";
    }

    /**************************************************************** */
    // جستجو بارها
    public function searchLoadsForm()
    {
        $cities = ProvinceCity::where('parent_id', '!=', 0)->get();
        $fleets = Fleet::where('parent_id', '>', 0)->orderBy('parent_id', 'asc')->get();
        $operators = User::where([['role', 'operator'], ['status', 1]])->get();
        $loads = [];
        $countLoads = 0;
        return view('admin.searchLoads', compact('loads', 'cities', 'fleets', 'operators', 'countLoads'));
    }

    public function searchLoads(Request $request)
    {

        $condition = [];

        if ($request->origin_city_id != "0")
            $condition[] = ['origin_city_id', $request->origin_city_id];
        if ($request->destination_city_id != "0")
            $condition[] = ['destination_city_id', $request->destination_city_id];
        if ($request->fleet_id != "0")
            $condition[] = ['fleets', 'LIKE', '%:' . $request->fleet_id . ',%'];
        if ($request->operator_id != "0")
            $condition[] = ['operator_id', $request->operator_id];
        if ($request->mobileNumber != "0" && $request->mobileNumber != null)
            $condition[] = ['mobileNumberForCoordination', $request->mobileNumber];

        $loads = Load::where($condition)->withTrashed()->get();
        $firstDateLoad = LoadBackup::where('mobileNumberForCoordination', $request->mobileNumber)
            ->orderBy('created_at', 'ASC')->first();
        $cities = ProvinceCity::where('parent_id', '!=', 0)->get();
        $fleets = Fleet::where('parent_id', '>', 0)->orderBy('parent_id', 'asc')->get();
        $operators = User::where([['role', 'operator'], ['status', 1]])->get();
        $countLoads = LoadBackup::where('mobileNumberForCoordination', $request->mobileNumber)->count();
        return view(
            'admin.searchLoads',
            compact(
                'loads',
                'cities',
                'fleets',
                'operators',
                'countLoads',
                'firstDateLoad'
            )
        );
    }

    public function deleteAll(Request $request)
    {
        $loads = Load::findOrFail($request->loads);
        foreach ($loads as $load) {
            $load->delete();
        }
        return redirect()->route('search.load.form');
    }
    public function storeDistanceCalculate(Request $request)
    {
        $duplicates = CityDistanceCalculate::where('fromCity_id', $request->fromCity)->where('toCity_id', $request->toCity)->get();
        if (count($duplicates) == 0) {
            $cityDistance = new CityDistanceCalculate();
            $cityDistance->fromCity_id = $request->fromCity;
            $cityDistance->toCity_id = $request->toCity;
            $cityDistance->value = $request->value;
            $cityDistance->save();
            return response()->json('با موفقیت ذخیره شد', 200);
        }
    }

    public function score(Request $request)
    {
        if ($request->type == 'Owner') {
            $userOwner = Score::where('owner_id', '=', $request->owner_id)
                ->where('driver_id', '=', $request->driver_id)
                ->where('type', '=', 'Owner')
                ->first();

            if ($userOwner === null && $request->type == 'Owner') {
                $score = new Score();
                $score->owner_id = $request->owner_id;
                $score->driver_id = $request->driver_id;
                $score->value = $request->value;
                $score->description = $request->description;
                $score->type = $request->type;
                $score->save();
            } else {
                $userOwner->value = $request->value;
                $userOwner->save();
            }
        } else {
            $userDriver = Score::where('owner_id', '=', $request->owner_id)
                ->where('driver_id', '=', $request->driver_id)
                ->where('type', '=', 'Driver')
                ->first();
            if ($userDriver === null) {
                $scoreDriver = new Score();
                $scoreDriver->owner_id = $request->owner_id;
                $scoreDriver->driver_id = $request->driver_id;
                $scoreDriver->value = $request->value;
                $scoreDriver->description = $request->description;
                $scoreDriver->type = $request->type;
                $scoreDriver->save();
            } else {
                $userDriver->value = $request->value;
                $userDriver->save();
            }
        }




        return response()->json('OK', 200);
    }
}
