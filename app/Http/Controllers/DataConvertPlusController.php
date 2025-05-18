<?php

namespace App\Http\Controllers;

use App\Models\BlockPhoneNumber;
use App\Models\CargoConvertList;
use App\Models\CargoReportByFleet;
use App\Models\Equivalent;
use App\Models\Fleet;
use App\Models\FleetLoad;
use App\Models\Load;
use App\Models\OperatorCargoListAccess;
use App\Models\Owner;
use App\Models\ProvinceCity;
use App\Models\User;
use App\Models\UserActivityReport;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DataConvertPlusController extends Controller
{
    public function smartStoreCargo()
    {

        $cargo = CargoConvertList::where([
            ['operator_id', auth()->id()],
            ['status', 0],
        ])
            ->orderby('id', 'desc')
            ->first();
        $operatorCargoListAccess = OperatorCargoListAccess::where('user_id', auth()->id())
            ->select('fleet_id')
            ->pluck('fleet_id')
            ->toArray();

        if (!isset($cargo->id)) {
            if (count($operatorCargoListAccess)) {
                $dictionary = Equivalent::where('type', 'fleet')
                    ->whereIn('original_word_id', $operatorCargoListAccess)
                    ->select('equivalentWord')
                    ->pluck('equivalentWord')
                    ->toArray();
                $conditions = [];

                foreach ($dictionary as $item) {
                    $conditions[] = ['cargo', 'LIKE', '%' . $item . '%'];

                    $cargo = CargoConvertList::where(function ($q) use ($conditions) {
                        return $q->orWhere($conditions);
                    })
                        ->where('operator_id', 0)
                        ->orderby('id', 'asc')
                        ->first();

                    if (isset($cargo->id)) {
                        break;
                    }
                }
            }

            if (!isset($cargo->id))
                $cargo = CargoConvertList::where('operator_id', 0)->orderby('id', 'asc')->first();
        }
        if (isset($cargo->id)) {

            $dictionary = Equivalent::where('type', 'fleet')
                ->whereIn('original_word_id', $operatorCargoListAccess)
                ->select('equivalentWord')
                ->pluck('equivalentWord')
                ->toArray();
            $conditions = [];
            foreach ($dictionary as $item) {
                $oldCargo = CargoConvertList::where('cargo', 'LIKE', '%' . $item . '%')
                    ->where('id', $cargo->id)
                    ->where('status', 0)
                    ->first();

                if (isset($oldCargo->id)) {
                    $oldCargo->operator_id = auth()->id();
                    $oldCargo->save();
                    return $this->dataConvertSmart($oldCargo);
                }
            }

            foreach ($dictionary as $item) {
                $newCargo = CargoConvertList::where('cargo', 'LIKE', '%' . $item . '%')
                    ->where('operator_id', 0)
                    ->orderby('id', 'asc')
                    ->first();

                if (isset($newCargo->id)) {
                    $newCargo->operator_id = auth()->id();
                    $newCargo->save();
                    return $this->dataConvertSmart($newCargo);
                }
            }

            $cargo->operator_id = auth()->id();
            $cargo->save();
            return $this->dataConvertSmart($cargo);
        }


        return redirect(url('dashboard'))->with('danger', 'هیچ باری وجود ندارد');
    }

    public function dataConvertSmart($cargo)
    {
        $text = $cargo->cargo;

        // نرمال‌سازی و آماده‌سازی
        $normalizedText = str_replace(["\r\n", "\r"], "\n", trim($text));
        $flatText = preg_replace('/\s+/', ' ', $normalizedText);
        $flatText = preg_replace('/\b(کرایه|2|وزن|بار|تخلیه|نوع بار|کل)\b/u', '', $flatText);
        $lines = array_values(array_filter(array_map('trim', explode("\n", $normalizedText))));

        // کش دیتاهای ثابت
        $fleetMap = cache()->remember('fleet_map', 60, fn() => DB::table('fleets')->pluck('id', 'title')->toArray());
        $cityMap = cache()->remember('city_map', 60, fn() => DB::table('province_cities')->pluck('id', 'name')->toArray());
        $parentCityMap = cache()->remember('parent_city_map', 60, fn() => DB::table('province_cities')->pluck('parent_id', 'name')->toArray());

        $equivalentWordsMap = method_exists($this, 'getEquivalentWords') ? $this->getEquivalentWords() : [];

        // آماده‌سازی لیست ناوگان‌ها
        $fleetTitles = array_unique(array_merge(array_keys($fleetMap), array_keys($equivalentWordsMap), ['کفی', 'تریلی', 'نیسان', 'ترانزیت']));
        usort($fleetTitles, fn($a, $b) => mb_strlen($b) <=> mb_strlen($a));
        $fleetPattern = implode('|', array_map('preg_quote', $fleetTitles));

        // لیست شهرها
        $cityTitles = array_keys($cityMap);
        usort($cityTitles, fn($a, $b) => mb_strlen($b) <=> mb_strlen($a));
        $cityPattern = implode('|', array_map('preg_quote', $cityTitles));

        // شماره تماس
        preg_match_all('/\b09\d{9}\b/u', $text, $phoneMatches);
        $phone = $phoneMatches[0][0] ?? null;

        preg_match_all("/\b($cityPattern)\b/u", $flatText, $cityMatches);
        $allCities = $cityMatches[0] ?? [];

        // استخراج مبدا اولیه
        $origin = null;
        if (preg_match("/(?:از|بارگیری(?:\s+از)?|مبدا)\s*(?:در\s*)?($cityPattern)/u", $flatText, $match)) {
            $origin = $match[1];
        } elseif (count($allCities) >= 2) {
            $origin = $allCities[0];
        }

        $results = [];
        $lastOrigin = $origin;

        foreach ($lines as $line) {
            $line = preg_replace('/\b(کرایه|وزن|بار|تخلیه|نوع بار|کل)\b/u', '', $line);

            // شماره تماس در خط
            if (preg_match('/\b09\d{9}\b/u', $line, $m)) {
                $phone = $m[0];
                $line = preg_replace('/\b09\d{9}\b/u', '', $line);
            }

            // تشخیص ناوگان
            preg_match_all("/\b($fleetPattern)\b/u", $line, $fleetMatches);
            $fleets = array_unique(array_map(fn($f) => $equivalentWordsMap[$f] ?? $f, $fleetMatches[0]));

            // مسیر "شهر به شهر"
            if (preg_match_all("/\b($cityPattern)\b\s+به\s+\b($cityPattern)\b/u", $line, $routeMatches, PREG_SET_ORDER)) {
                foreach ($routeMatches as $match) {
                    foreach ($fleets ?: [null] as $fleet) {
                        $results[] = [
                            'fleet' => $fleet,
                            'fleet_id' => $fleetMap[$fleet] ?? null,
                            'origin' => $match[1],
                            'origin_id' => $cityMap[$match[1]] ?? null,
                            'origin_state_id' => $parentCityMap[$match[1]] ?? null,
                            'destination' => $match[2],
                            'destination_id' => $cityMap[$match[2]] ?? null,
                            'destination_state_id' => $parentCityMap[$match[2]] ?? null,
                            'phone' => $phone,
                        ];
                    }
                    $lastOrigin = $match[1];
                }
                continue;
            }

            // حالت مقصد بدون مبدا (مبدا از قبل ذخیره شده)
            if ($lastOrigin && !empty($fleets)) {
                preg_match_all("/\b($cityPattern)\b/u", $line, $citiesInLine);
                foreach ($citiesInLine[0] ?? [] as $dest) {
                    foreach ($fleets as $fleet) {
                        $results[] = [
                            'fleet' => $fleet,
                            'fleet_id' => $fleetMap[$fleet] ?? null,
                            'origin' => $lastOrigin,
                            'origin_id' => $cityMap[$lastOrigin] ?? null,
                            'origin_state_id' => $parentCityMap[$lastOrigin] ?? null,
                            'destination' => $dest,
                            'destination_id' => $cityMap[$dest] ?? null,
                            'destination_state_id' => $parentCityMap[$dest] ?? null,
                            'phone' => $phone,
                        ];
                    }
                }
            }
        }

        // fallback
        if (empty($results) && $origin && count($allCities) >= 2) {
            $destinations = array_filter($allCities, fn($c) => $c !== $origin);
            foreach ($fleetTitles as $f) {
                if (Str::contains($flatText, $f)) {
                    $fleet = $equivalentWordsMap[$f] ?? $f;
                    foreach ($destinations as $dest) {
                        $results[] = [
                            'fleet' => $fleet,
                            'fleet_id' => $fleetMap[$fleet] ?? null,
                            'origin' => $origin,
                            'origin_id' => $cityMap[$origin] ?? null,
                            'origin_state_id' => $parentCityMap[$origin] ?? null,
                            'destination' => $dest,
                            'destination_id' => $cityMap[$dest] ?? null,
                            'destination_state_id' => $parentCityMap[$dest] ?? null,
                            'phone' => $phone,
                        ];
                    }
                }
            }
        }

        // حذف تکراری‌ها
        $uniqueResults = collect($results)->unique(fn($item) => ($item['fleet'] ?? '') . '-' . $item['origin'] . '-' . $item['destination'] . '-' . ($item['phone'] ?? ''))->values()->all();
        // اطلاعات تکمیلی برای نمایش
        $countOfCargos = CargoConvertList::where('operator_id', 0)->count();
        $users = UserController::getOnlineAndOfflineUsers();

        return view('admin.load.smartCreateCargo', compact('cargo', 'countOfCargos', 'users', 'uniqueResults'));
    }

    // ذخیره دسته ای بارها
    public function storeMultiCargoSmart(Request $request, CargoConvertList $cargo)
    {
        // return $request;
        try {
            $expiresAt = now()->addMinutes(3);
            $userId = Auth::id();

            Cache::put("user-is-active-$userId", true, $expiresAt);
            User::whereId($userId)->update(['last_active' => now()]);
        } catch (Exception $e) {
            Log::emergency("UserActivityActiveOnlineReport - Error: " . $e->getMessage());
        }



        $keys = $request->input('key'); // لیست کلیدهای موجود در درخواست
        $rules = [];
        $messages = [];
        foreach ($keys as $key) {
            $rules["mobileNumber_{$key}"] = 'required|digits:11';
            $messages["mobileNumber_{$key}.required"] = "شماره تلفن {$key} الزامی است.";
            $messages["mobileNumber_{$key}.digits"] = "شماره تلفن {$key} باید دقیقا ۱۱ رقم باشد.";
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return back()->with('danger', 'شماره موبایل کمتر از 11 رقم است')->withErrors($validator)->withInput();
        }
        try {

            if (UserActivityReport::where([
                ['created_at', '>', date('Y-m-d H:i:s', strtotime('-5 minute', time()))],
                ['user_id', \auth()->id()]
            ])->count() == 0)
                UserActivityReport::create(['user_id' => \auth()->id()]);
        } catch (Exception $e) {
            Log::emergency("-------------------------- UserActivityReport ----------------------------------------");
            Log::emergency($e->getMessage());
            Log::emergency("------------------------------------------------------------------");
        }
        // return dd($request);
        $counter = 0;
        foreach ($request->key as $key) {
            $origin = "origin_" . $key;
            $originState = "origin_state_id_" . $key;
            $destination = "destination_" . $key;
            $destinationState = "destination_state_id_" . $key;
            $mobileNumber = "mobileNumber_" . $key;
            $description = "description_" . $key;
            $fleet = "fleets_" . $key;
            $title = "title_" . $key;
            // $pattern = "pattern_" . $key;
            try {
                $this->storeCargoSmart(
                    $request->$origin,
                    $request->$originState,
                    $request->$destination,
                    $request->$destinationState,
                    $request->$mobileNumber,
                    $request->$description,
                    $request->$fleet,
                    $request->$title,
                    // $request->$pattern,
                    $counter,
                    $cargo->id
                );
            } catch (\Exception $exception) {
                return $exception;
                Log::emergency("storeMultiCargo : " . $exception->getMessage());
            }
        }

        $cargo->status = true;
        $cargo->save();
        return back()->with('success', $counter . 'بار ثبت شد');
    }
    public function storeCargoSmart($origin, $originState, $destination, $destinationState, $mobileNumber, $description, $fleet, $title, &$counter, $cargoId)
    {
        if (!strlen(trim($origin)) || $origin == null || $origin == 'null' || !strlen(trim($destination)) || $destination == null || $destination == 'null' || !strlen($fleet) || !strlen($mobileNumber))
            return;

        substr($mobileNumber, 0, 1) !== '0' ? $mobileNumber = '0' . $mobileNumber : $mobileNumber;

        $cargoPattern = '';

        try {
            $cargoPattern = $origin .$destination . $mobileNumber . $fleet;

            if (
                BlockPhoneNumber::where('phoneNumber', $mobileNumber)->exists() ||
                Load::where('cargoPattern', $cargoPattern)
                ->where('created_at', '>', now()->subMinutes(180))
                ->exists()
            ) {
                return;
            }
        } catch (\Exception $exception) {
            Log::emergency(str_repeat("-", 75));
            Log::emergency("خطای جستجوی تکراری");
            Log::emergency($exception->getMessage());
            Log::emergency(str_repeat("-", 75));
            return;
        }


        try {

            DB::beginTransaction();
            $load = new Load();
            $load->title = strlen($title) == 0 ? 'بدون عنوان' : $title;
            $load->cargo_convert_list_id = $cargoId;
            $load->senderMobileNumber = $mobileNumber;
            $load->emergencyPhone = $mobileNumber;
            $load->load_type_id = 0;
            $load->tenderTimeDuration = 0;
            $load->packing_type_id = 0;
            $owner = Owner::where('mobileNumber', $mobileNumber)->first();
            if (isSendBotLoadOwner() == true) {
                if ($owner != null) {
                    $load->user_id = $owner->id;
                    $load->userType = ROLE_OWNER;
                    $load->operator_id = auth()->id();
                    $load->isBot = 1;
                    if (BlockPhoneNumber::where(function ($query) use ($owner, $mobileNumber) {
                        $query->where('nationalCode', $owner->nationalCode)
                            ->orWhere('phoneNumber', $mobileNumber);
                    })->where(function ($query) {
                        $query->where('type', 'operator')
                            ->orWhere('type', 'both');
                    })->exists()) {
                        return;
                    }
                } else {
                    $load->user_id = auth()->id();
                    $load->userType = ROLE_OPERATOR;
                    $load->operator_id = auth()->id();
                }
            } else {
                $load->user_id = auth()->id();
                $load->userType = ROLE_OPERATOR;
                $load->operator_id = auth()->id();
            }
            // $load->urgent = 0;
            $load->loadMode = 'outerCity';
            $load->loadingHour = 0;
            $load->loadingMinute = 0;
            // $load->numOfTrucks = 1;
            $load->cargoPattern = $cargoPattern;

            $origin = str_replace('_', ' ', str_replace('[', '', str_replace(']', '', $origin)));
            $destination = str_replace('_', ' ', str_replace('[', '', str_replace(']', '', $destination)));



            $originCity = ProvinceCity::where('name', 'like', '%' . $origin)
                ->where('parent_id', $originState)
                ->first();

            $destinationCity = ProvinceCity::where('name', 'like', '%' . $destination)
                ->where('parent_id', $destinationState)
                ->first();
            Log::alert($destinationState);
            Log::alert($destinationState);

            $load->origin_city_id = $originCity->id;
            $load->destination_city_id = $destinationCity->id;

            $load->fromCity = $this->getCityName($load->origin_city_id);
            $load->toCity = $this->getCityName($load->destination_city_id);

            $load->loadingDate = gregorianDateToPersian(date('Y-m-d', time()), '-');
            $load->time = time();

            try {
                $city = ProvinceCity::where('parent_id', '!=', 0)->find($load->origin_city_id);
                if (isset($city->id)) {
                    $load->latitude = $city->latitude;
                    $load->longitude = $city->longitude;
                }
            } catch (\Exception $exception) {
            }

            $load->weightPerTruck = 0;

            $load->bulk = 2;
            $load->dangerousProducts = false;

            $load->origin_state_id = AddressController::geStateIdFromCityId($load->origin_city_id);
            $load->description = $description ?? '';

            $load->mobileNumberForCoordination = $mobileNumber;
            $load->storeFor = ROLE_DRIVER;
            $load->status = ON_SELECT_DRIVER;
            $load->deliveryTime = 24;

            $load->date = gregorianDateToPersian(date('Y/m/d', time()), '/');
            $load->dateTime = now()->format('H:i:s');



            // $loadDuplicateHour = Load::where('userType', 'operator')
            //     ->where('mobileNumberForCoordination', $load->mobileNumberForCoordination)
            //     ->where('origin_city_id', $load->origin_city_id)
            //     ->where('destination_city_id', $load->destination_city_id)
            //     ->where('cargoPattern', 'LIKE', '%' . $fleet . '%')
            //     ->first();

            $fleet = str_replace('_', ' ', str_replace('[', '', str_replace(']', '', $fleet)));

            $fleet_id = Fleet::where('title', $fleet)->first();
            if (!isset($fleet_id->id)) {
                $fleet_id = Fleet::where('title', str_replace('ك', 'ک', $fleet))->first();
            }
            if (!isset($fleet_id->id)) {
                $fleet_id = Fleet::where('title', str_replace('ي', 'ی', $fleet))->first();
            }
            if (!isset($fleet_id->id)) {
                $fleet_id = Fleet::where('title', str_replace('ي', 'ی', str_replace('ك', 'ک', $fleet)))->first();
            }
            if (!isset($fleet_id->id)) {
                $fleet_id = Fleet::where('title', str_replace('ک', 'ك', $fleet))->first();
            }
            if (!isset($fleet_id->id)) {
                $fleet_id = Fleet::where('title', str_replace('ی', 'ي', $fleet))->first();
            }
            if (!isset($fleet_id->id)) {
                $fleet_id = Fleet::where('title', str_replace('ی', 'ي', str_replace('ک', 'ك', $fleet)))->first();
            }

            $conditions = [
                'mobileNumberForCoordination' => $load->mobileNumberForCoordination,
                'origin_city_id' => $load->origin_city_id,
                'destination_city_id' => $load->destination_city_id,
                ['fleets', 'LIKE', '%fleet_id":' . $fleet_id->id . ',%']
            ];
            $loadDuplicate = Load::where($conditions)
                ->where('userType', 'operator')
                ->first();

            $loadDuplicateOwner = Load::where($conditions)
                ->where('userType', 'owner')
                ->where('isBot', 0)
                ->first();

            if (is_null($loadDuplicate) && is_null($loadDuplicateOwner)) {
                $load->save();
            }


            if (isset($load->id)) {

                $counter++;

                if (isset($fleet_id->id)) {
                    if ($fleet_id->id == 86) {
                        $fleet_ids = [86, 87];
                        foreach ($fleet_ids as $id) {
                            $fleetLoad = new FleetLoad();
                            $fleetLoad->load_id = $load->id;
                            $fleetLoad->fleet_id = $id;
                            $fleetLoad->numOfFleets = 1;
                            $fleetLoad->userType = $load->userType;
                            $fleetLoad->save();
                        }
                    } else {
                        $fleetLoad = new FleetLoad();
                        $fleetLoad->load_id = $load->id;
                        $fleetLoad->fleet_id = $fleet_id->id;
                        $fleetLoad->numOfFleets = 1;
                        $fleetLoad->userType = $load->userType;
                        $fleetLoad->save();
                    }

                    try {
                        $persian_date = gregorianDateToPersian(date('Y/m/d', time()), '/');
                        // Log::emergency("Error cargo report by 1371: ");

                        $cargoReport = CargoReportByFleet::where('fleet_id', $fleetLoad->fleet_id)
                            ->where('date', $persian_date)
                            ->first();
                        // Log::emergency("Error cargo report by 1376: ");

                        if (isset($cargoReport->id)) {
                            $cargoReport->count += 1;
                            $cargoReport->save();
                        } else {
                            $cargoReportNew = new CargoReportByFleet;
                            $cargoReportNew->fleet_id = $fleetLoad->fleet_id;
                            $cargoReportNew->count = 1;
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

                    // if ($loadDuplicate === null) {
                    $load->save();
                    // $this->sendLoadToOtherWeb($load);

                    // }
                } catch (\Exception $exception) {
                    Log::emergency("---------------------------------------------------------");
                    Log::emergency($exception->getMessage());
                    Log::emergency("---------------------------------------------------------");
                }
                try {
                    $ownerLoadCount = Owner::where('mobileNumber', $load->mobileNumberForCoordination)->first();
                    if ($ownerLoadCount) {
                        $ownerLoadCount->loadCount += 1;
                        $ownerLoadCount->save();
                    }
                } catch (\Exception $th) {
                    //throw $th;
                }
            }


            DB::commit();
        } catch (\Exception $exception) {

            DB::rollBack();

            Log::emergency("----------------------ثبت بار جدید-----------------------");
            Log::emergency($exception);
            Log::emergency("---------------------------------------------------------");
        }
    }

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
    private function getCityId($cityName)
    {
        try {
            $city = ProvinceCity::where('name', $cityName)->where('parent_id', '!=', 0)->select('id')->first();
            if (!isset($city->id)) {
                $city = ProvinceCity::where('name', str_replace('ک', 'ك', $cityName))->where('parent_id', '!=', 0)->select('id')->first();
            }
            if (!isset($city->id)) {
                $city = ProvinceCity::where('name', str_replace('ی', 'ي', $cityName))->where('parent_id', '!=', 0)->select('id')->first();
            }
            if (!isset($city->id)) {
                $city = ProvinceCity::where('name', str_replace('ی', 'ي', str_replace('ک', 'ك', $cityName)))->where('parent_id', '!=', 0)->select('id')->first();
            }
            if (isset($city->id))
                return $city->id;
        } catch (\Exception $e) {
        }


        return 0;
    }

    private function getEquivalentWords(): array
    {
        static $cache = null;
        if (!$cache) {
            $equivalents = Equivalent::get(['equivalentWord', 'original_word_id']);
            $fleetTitles = DB::table('fleets')->pluck('title', 'id'); // [id => title]
            $cache = [];

            foreach ($equivalents as $equiv) {
                $title = $fleetTitles[$equiv->original_word_id] ?? null;
                if ($title) {
                    $cache[$equiv->equivalentWord] = $title;
                }
            }
        }
        return $cache; // ['نیسانی' => 'نیسان', ...]
    }
}
