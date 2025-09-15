<?php

namespace App\Http\Controllers;

use App\Jobs\SendPushNotificationPersonalizeJob;
use App\Models\Bearing;
use App\Models\BlockPhoneNumber;
use App\Models\BotTest;
// use App\Models\CargoCanvertList;
use App\Models\CargoConvertList;
use App\Models\City;
// use App\Models\Customer;
// use App\Models\DateOfCargoDeclaration;
use App\Models\Dictionary;
// use App\Models\DriverCallCount;
// use App\Models\DriverCallReport;
use App\Models\Fleet;
use App\Models\FleetLoad;
use App\Models\Load;
use App\Models\LoadBackup;
use App\Models\CargoReportByFleet;
use App\Models\Driver;
use App\Models\DriverCall;
use App\Models\Equivalent;
use App\Models\FleetlessNumbers;
use App\Models\LoadOwnerCount;
// use App\Models\FirstLoad;
// use App\Models\LimitCall;
use App\Models\OperatorCargoListAccess;
use App\Models\Owner;
use App\Models\ProvinceCity;
use App\Models\RejectCargoOperator;
// use App\Models\Setting;
use App\Models\StoreCargoOperator;
// use App\Models\Tender;
// use App\Models\Transaction;
use App\Models\User;
use App\Models\UserActivityReport;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Exception;
use Jenssegers\Agent\Agent;

class DataConvertController extends Controller
{
    const SINGLE_ORIGIN_SINGLE_DESTINATION = 1;
    const SINGLE_ORIGIN_MULTI_DESTINATION = 2;
    const MULTI_ORIGIN_MULTI_DESTINATION = 3;
    const  EVERY_LINE_ONE_CARGO = 4;
    const SINGLE_FLEET = 1;
    const MULTI_FLEET = 2;

    public function storeCargoInformation(Request $request)
    {
        try {
            if (CargoConvertList::where('cargo', $request->cargo)->count() == 0) {
                $cargoConvertList = new CargoConvertList();
                $cargoConvertList->cargo = $request->cargo;
                $cargoConvertList->cargo_user_id = Auth::id();
                $cargoConvertList->save();

                // گزارش بار ها بر اساس اپراتور
                $persian_date = gregorianDateToPersian(date('Y/m/d', time()), '/');
                $storeCargoOperator = StoreCargoOperator::firstOrNew([
                    'user_id' => Auth::id(),
                    'persian_date' => $persian_date,
                ]);

                $storeCargoOperator->count = ($storeCargoOperator->count ?? 0) + 1;
                $storeCargoOperator->save();

                return back()->with('success', 'ذخیره شد');
            }
            return back()->with('success', 'اطلاعات ارسال شده تکراری بود!');
        } catch (Exception $exception) {
        }
        return back()->with('danger', 'ذخیره انجام نشد');
    }

    public function removeCargoFromCargoList(CargoConvertList $cargo)
    {
        $cargo->status = 1;
        $cargo->rejected = 1;
        $cargo->save();
        $persian_date = gregorianDateToPersian(date('Y/m/d', time()), '/');
        $rejectCargo = RejectCargoOperator::where('user_id', auth()->id())
            ->where('persian_date', $persian_date)
            ->first();
        if (isset($rejectCargo->id)) {
            $rejectCargo->count += 1;
            $rejectCargo->save();
        } else {
            $rejectCargo = new RejectCargoOperator();
            $rejectCargo->persian_date = $persian_date;
            $rejectCargo->count = 1;
            $rejectCargo->user_id = auth()->id();
            $rejectCargo->save();
        }
        return back();
    }

    public function updateCargoInfo(CargoConvertList $cargo, Request $request)
    {
        $cargo->cargo = $request->cargo;
        $cargo->save();
        return back()->with('success', 'ویرایش شد');
    }

    public function finalApprovalAndStoreCargo()
    {
        $userId = auth()->id();

        // ۱. پیدا کردن باری که قبلاً به اپراتور تخصیص داده شده
        $cargo = CargoConvertList::where([
            ['operator_id', $userId],
            ['status', 0],
            ['isBlocked', 0],
            ['isDuplicate', 0],
        ])
            ->latest('id')
            ->first();

        // ۲. اگر باری برای اپراتور نبود → دنبال بار آزاد مناسب بگرد
        if (!$cargo) {
            $operatorCargoListAccess = OperatorCargoListAccess::where('user_id', $userId)
                ->pluck('fleet_id')
                ->toArray();

            $dictionary = [];
            if ($operatorCargoListAccess) {
                $dictionary = Equivalent::where('type', 'fleet')
                    ->whereIn('original_word_id', $operatorCargoListAccess)
                    ->pluck('equivalentWord')
                    ->toArray();
            }

            // اگر دیکشنری داریم → دنبال اولین باری بگرد که یکی از کلماتش داخل بار هست
            if ($dictionary) {
                $cargo = CargoConvertList::where(function ($q) use ($dictionary) {
                    foreach ($dictionary as $word) {
                        $q->orWhere('cargo', 'LIKE', "%{$word}%");
                    }
                })
                    ->where([
                        ['operator_id', 0],
                        ['status', 0],
                        ['isBlocked', 0],
                        ['isDuplicate', 0],
                    ])
                    ->oldest('id')
                    ->first();
            }

            // اگر باز هم بار پیدا نشد → اولین بار آزاد عمومی
            if (!$cargo) {
                $cargo = CargoConvertList::where([
                    ['operator_id', 0],
                    ['status', 0],
                    ['isBlocked', 0],
                    ['isDuplicate', 0],
                ])
                    ->oldest('id')
                    ->first();
            }
        }

        // ۳. اگر بار پیدا شد → مالکیت بده به اپراتور
        if ($cargo) {
            // بررسی اگر بار واقعاً جزو دیکشنری اپراتور هست
            if (!empty($dictionary)) {
                foreach ($dictionary as $word) {
                    if (str_contains($cargo->cargo, $word)) {
                        $cargo->operator_id = $userId;
                        $cargo->save();
                        return $this->dataConvert($cargo);
                    }
                }

                // اگر بار فعلی نبود، دنبال بار جدیدی که match کنه
                $newCargo = CargoConvertList::where(function ($q) use ($dictionary) {
                    foreach ($dictionary as $word) {
                        $q->orWhere('cargo', 'LIKE', "%{$word}%");
                    }
                })
                    ->where([
                        ['operator_id', 0],
                        ['status', 0],
                        ['isBlocked', 0],
                        ['isDuplicate', 0],
                    ])
                    ->oldest('id')
                    ->first();

                if ($newCargo) {
                    $newCargo->operator_id = $userId;
                    $newCargo->save();
                    return $this->dataConvert($newCargo);
                }
            }

            // در نهایت بار فعلی رو بده به اپراتور
            $cargo->operator_id = $userId;
            $cargo->save();
            return $this->dataConvert($cargo);
        }

        // ۴. اگر هیج باری نبود → برگرد به داشبورد
        return redirect(url('dashboard'))->with('danger', 'هیچ باری وجود ندارد');
    }


    private function removeEmojis($text)
    {
        // حذف بیشتر ایموجی‌های متداول (unicode ranges)
        return preg_replace('/[\x{1F600}-\x{1F64F}' . // شکلک‌های چهره
            '\x{1F300}-\x{1F5FF}' . // نمادها و اشیاء
            '\x{1F680}-\x{1F6FF}' . // وسایل نقلیه و نمادهای نقشه
            '\x{2600}-\x{26FF}' .   // نمادهای متفرقه
            '\x{2700}-\x{27BF}' .   // نمادهای دکمه‌ای
            '\x{1F1E6}-\x{1F1FF}' . // پرچم‌ها
            '\x{1F900}-\x{1F9FF}' . // ایموجی‌های جدیدتر
            '\x{1FA70}-\x{1FAFF}' . // ایموجی‌های نسخه‌های اخیر
            '\x{200D}' .            // Zero-width joiner (ZWJ)
            '\x{FE0F}]/u', ' ', $text);
    }

    // دریافت لیست ناوگان
    private function getFleetsList()
    {
        $fleets = Fleet::where('parent_id', '>', 0)->pluck('title')->toArray();
        for ($i = 0; $i < count($fleets); $i++)
            $fleets[$i] = $this->replaceToPersianAlphabet($fleets[$i]);

        return $fleets;
    }

    // دریافت لیست شهرها
    private function getCitiesList()
    {
        $cities = ProvinceCity::where('parent_id', '!=', 0)->select('name')->pluck('name')->toArray();
        for ($i = 0; $i < count($cities); $i++)
            $cities[$i] = $this->replaceToPersianAlphabet($cities[$i]);
        return $cities;
    }

    // دریافت لیست شهرها
    private function getProvincesList()
    {
        $provinces = ProvinceCity::where('parent_id', '=', 0)->get();
        return $provinces;
    }

    // دریافت لیست کلمات مهم در شناسایی رابطه ها
    private function getExtraWords()
    {
        return [
            '[از]',
            '[به]',
            '[صافی]',
            '[صاف]',
            '[هرتن]',
            '[به_ازای_هرتن]',
            '[به_ازاء_هرتن]',
            '[هر_تن]',
            '[به_ازای_هر_تن]',
            '[به_ازاء_هر_تن]',
            '[کرایه]',
            '[قیمت]',
            '[م]',
            '[میلیون]',
            '[تومن]',
            '[تن]',
        ];
    }

    public function dataConvert($cargo)
    {
        $prefixFreightConditions  = ['صافی', 'صاف', 'هرتن', 'کرایه', 'قیمت'];
        $postfixFreightConditions = ['صافی', 'صاف', 'هرتن', 'کرایه', 'م', 'میلیون'];

        $originalText   = $cargo->cargo;
        $fleetsList     = $this->getFleetsList();
        $citiesList     = $this->getCitiesList();
        $provincesList  = $this->getProvincesList();
        $extraWords     = $this->getExtraWords();
        $originWords    = $this->getOriginWords();
        // return dd($originWords);
        $equivalentWords = $this->getEquivalentWords();

        $cleanedText = $this->getCleanedText(
            $cargo->cargo,
            $fleetsList,
            $citiesList,
            $equivalentWords,
            $originWords,
            $extraWords,
            $prefixFreightConditions,
            $postfixFreightConditions,
            $provincesList
        );

        // خروجی‌ها
        $cargoList    = [];
        $origins      = [];
        $fleets       = [];
        $phoneNumbers = $this->extractPhoneNumbers($cleanedText);

        // وضعیت
        $lastCity = '';
        $currentOrigin = -1;
        $originName = '';
        $originProvince = null;
        $expectNextCityToBeOrigin = false;

        foreach ($cleanedText as $key => $item) {
            $token = trim($item);

            // فلیت
            if (in_array($token, $fleetsList)) {
                if (($cleanedText[$key - 1] ?? null) === '[_]') {
                    $fleets = [];
                }
                $fleets[$token] = $token;
                continue;
            }

            // [از] → شهر بعدی مبدا
            if ($token === '[از]' || $token === 'از') {
                $expectNextCityToBeOrigin = true;
                continue;
            }

            // [به] → شهر قبلی مبدا + شهر بعدی مقصد
            if ($token === '[به]' || $token === 'به') {
                if (!empty($lastCity)) {
                    $originName    = $lastCity;
                    $originProvince = $this->getProvince($originName);
                    $origins[]     = $originName;
                    $currentOrigin = $key;
                }
                continue;
            }

            // اگر شهر است
            if (in_array($token, $citiesList)) {
                $lastCity = $token;

                // اگر انتظار داشتیم مبدا باشد (بعد از [از])
                if ($expectNextCityToBeOrigin) {
                    $originName     = $token;
                    $originProvince = $this->getProvince($token);
                    $origins[]      = $token;
                    $currentOrigin  = $key;
                    $lastCity       = '';
                    $expectNextCityToBeOrigin = false;
                    continue;
                }

                // اگر شهری غیر از مبداها آمد → مقصد
                if (!in_array($token, $origins) && $currentOrigin > -1) {
                    $cargoPhoneNumber = $this->getNearestPhone($phoneNumbers, $key);
                    $descProvinces    = $this->getProvince($token);

                    $cargoList[] = [
                        'origin'         => $originName,
                        'originProvince' => $originProvince,
                        'destination'    => $token,
                        'descProvinces'  => $descProvinces,
                        'fleets'         => $fleets,
                        'mobileNumber'   => $cargoPhoneNumber,
                        'freight'        => 0,
                        'priceType'      => 'توافقی'
                    ];
                }
            }
        }

        // آمار + کاربرها
        $countOfCargos = CargoConvertList::where('operator_id', 0)
            ->where('isBlocked', 0)
            ->where('isDuplicate', 0)
            ->count();

        $users = UserController::getOnlineAndOfflineUsers();

        return view('admin.storeCargoForm', compact(
            'cargoList',
            'originalText',
            'cargo',
            'countOfCargos',
            'users'
        ));
    }

    private function extractPhoneNumbers(array $text): array
    {
        $phones = [];
        foreach ($text as $key => $item) {
            if (preg_match("/^0\d{10}$/", $item)) {
                $phones[] = ['phoneNumber' => $item, 'key' => $key];
            }
        }
        return $phones;
    }

    private function getProvince(string $city)
    {
        $city = str_replace(['[', ']', '_'], ['', '', ' '], $city);
        return ProvinceCity::where('name', $city)->where('parent_id', '!=', 0)->get();
    }

    private function getNearestPhone(array $phones, int $currentKey): ?string
    {
        foreach ($phones as $phone) {
            if ($currentKey < $phone['key']) {
                return $phone['phoneNumber'];
            }
        }
        return null;
    }

    private function isOriginMarker(string $item, int $key, array $text, array $citiesList): bool
    {
        return in_array($item, $citiesList) && (
            ($text[$key - 1] ?? '') === '[از]' || ($text[$key + 1] ?? '') === '[به]'
        );
    }

    private function isDestination(string $item, array $citiesList, array $origins, array $text, int $key): bool
    {
        return in_array($item, $citiesList)
            && !in_array($item, $origins)
            && ($text[$key + 1] ?? '') !== '[به]';
    }


    // جابجایی حروف فارسی با حروف عربی
    private function replaceToPersianAlphabet($text)
    {
        return str_replace('أ', 'ا', str_replace('ي', 'ی', str_replace('ك', 'ک', convertFaNumberToEn($text))));
    }

    // تمیز کردن متن
    private function getCleanedText($text, &$fleetsList, &$citiesList, array $equivalentWords, array &$originWords, array &$extraWords, $prefixFreightConditions, $postfixFreightConditions): array
    {
        // جایگیزی حروف فارسی به جای عربی
        $text = $this->replaceToPersianAlphabet($text);


        $text = str_replace("-", " ", $text);
        $text = str_replace("*", " ", $text);
        $text = str_replace(".", " ", $text);
        $text = str_replace("_", " ", $text);
        $text = str_replace("/", "-", $text);
        $pattern = "/[0-9]{4}-[0-9]{2}-[0-9]{2}/";
        $text = preg_replace($pattern, "", $text);
        $text = str_replace("-", ".", $text);


        $text = str_replace(["\n", "\r"], ' _ ', $text);

        // جای گزینی حروف اضافه با جای خالی
        $text = preg_replace('/[^_.آ-ی0-9]+/u', ' ', $text);

        // جدا کردن اعداد و حرف از هم
        $text = preg_replace('/(\d+\.?\d*)/', ' $0 ', $text);


        // یکی کردن اسامی چند کلمه ای با اضافه کردن "_"
        for ($i = 0; $i < count($fleetsList); $i++) {
            $mergeWords = str_replace(' ', '_', $fleetsList[$i]);
            $text = str_replace($fleetsList[$i], $mergeWords, $text);
            $fleetsList[$i] = '[' . $mergeWords . ']';
        }

        for ($i = 0; $i < count($citiesList); $i++) {
            $mergeWords = str_replace(' ', '_', $citiesList[$i]);
            $text = str_replace($citiesList[$i], $mergeWords, $text);
            $citiesList[$i] = '[' . $mergeWords . ']';
        }

        // اول ناوگان دو کلمه ای را جستجو و یک کلمه ای شود
        for ($i = 0; $i < count($equivalentWords); $i++) {
            if (count(explode(' ', $equivalentWords[$i])) > 1)
                $text = str_replace($equivalentWords[$i], $originWords[$i], $text);
        }

        // تمام کلمات بین [] قرار گیرند
        $text = explode(' ', $text);

        $newText = '';
        for ($i = 0; $i < count($text); $i++)
            if (strlen($text[$i]) && $text[$i] != ' ' && !(isset($text[$i - 1]) && ($text[$i - 1] == "[_]" || $text[$i - 1] == "_") && $text[$i] == "_")) {
                $text[$i] = is_numeric($text[$i]) || $text[$i] == '.' ? $text[$i] : '[' . $text[$i] . ']';
                $newText .= ' ' . $text[$i];
            }

        for ($i = 0; $i < count($originWords); $i++)
            $originWords[$i] = '[' . $originWords[$i] . ']';

        // جایگزی کلمات معادل
        for ($i = 0; $i < count($equivalentWords); $i++) {
            $originWordsList = '';
            foreach ($equivalentWords as $key => $eqw)
                if ($eqw == $equivalentWords[$i] && isset($originWords[$key]))
                    $originWordsList .= ' ' . $originWords[$key];
            $newText = str_replace('[' . $equivalentWords[$i] . ']', $originWordsList, $newText);
        }

        $newText = explode(' ', $newText);

        // حذف حروف اضافه
        $cleanText = [];
        for ($i = 0; $i < count($newText); $i++)
            if (isset($newText[$i]) && (in_array($newText[$i], $fleetsList) || in_array($newText[$i], $citiesList) || in_array($newText[$i], $extraWords) || in_array($newText[$i], $prefixFreightConditions) || in_array($newText[$i], $postfixFreightConditions) || is_numeric($newText[$i]) || $newText[$i] == '.' || $newText[$i] == '[_]'))
                $cleanText[] = $newText[$i];

        return $cleanText;
    }

    // دریافت لیست کلمات اصلی
    private function getOriginWords()
    {
        return Cache::remember('origin_words', 60 * 5, function () {
            $dictionary = Equivalent::get()->pluck('originalWord');
            $array = [];
            foreach ($dictionary as $item) {
                $array[] = str_replace(' ', '_', $item);
            }

            return $array;
        });
    }

    // دریافت لیست کلمات معادل
    private function getEquivalentWords(): array
    {
        return Equivalent::get()->pluck('equivalentWord')->toArray();
    }

    // فرم ثبت بار (بررسی و ثبت)
    public function storeCargoConvertForm()
    {
        $countOfCargos = CargoConvertList::where('operator_id', 0)
            ->where('isBlocked', 0)
            ->where('isDuplicate', 0)
            ->count();

        return view('admin.storeCargoConvertForm', compact('countOfCargos'));
    }

    // ذخیره بار
    public function storeMultiCargo(Request $request, CargoConvertList $cargo)
    {
        $agent = new \Jenssegers\Agent\Agent();
        $device = $agent->isMobile() ? "Mobile" : ($agent->isTablet() ? "Tablet" : "Desktop");

        try {
            $expiresAt = now()->addMinutes(3);
            $userId = Auth::id();

            Cache::put("user-is-active-$userId", true, $expiresAt);
            User::whereId($userId)->update([
                'last_active' => now(),
                'device' => $device
            ]);
        } catch (\Exception $e) {
            Log::emergency("UserActivityActiveOnlineReport - Error: " . $e->getMessage());
        }

        // ✅ اعتبارسنجی موبایل‌ها
        $keys = $request->input('key', []);
        $rules = $messages = [];
        foreach ($keys as $key) {
            $rules["mobileNumber_{$key}"] = 'required|digits:11';
            $messages["mobileNumber_{$key}.required"] = "شماره تلفن {$key} الزامی است.";
            $messages["mobileNumber_{$key}.digits"]   = "شماره تلفن {$key} باید دقیقا ۱۱ رقم باشد.";
        }

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return back()->with('danger', 'شماره موبایل کمتر از 11 رقم است')
                ->withErrors($validator)
                ->withInput();
        }

        try {
            if (UserActivityReport::where([
                ['created_at', '>', now()->subMinutes(5)],
                ['user_id', auth()->id()]
            ])->count() == 0) {
                UserActivityReport::create(['user_id' => auth()->id()]);
            }
        } catch (\Exception $e) {
            Log::emergency("UserActivityReport Error: " . $e->getMessage());
        }

        // ✅ کش کردن داده‌های ثابت
        $fleetsCache = Cache::rememberForever('fleets_cache', function () {
            return Fleet::pluck('id', 'title')->toArray();
        });

        $citiesCache = Cache::rememberForever('province_cities_cache', function () {
            return ProvinceCity::select('id', 'name', 'parent_id', 'latitude', 'longitude')->get();
        });

        $counter = 0;

        foreach ($keys as $key) {
            $fleets = $request->input("fleets_$key", []);
            foreach ($fleets as $fleet) {
                $this->storeCargo(
                    $request->input("origin_$key"),
                    $request->input("originState_$key"),
                    $request->input("destination_$key"),
                    $request->input("destinationState_$key"),
                    $request->input("mobileNumber_$key"),
                    $request->input("description_$key"),
                    $fleet,
                    $request->input("freight_$key"),
                    $request->input("priceType_$key"),
                    $request->input("title_$key"),
                    $request->input("pattern_$key"),
                    $counter,
                    $cargo->id,
                    $fleetsCache,
                    $citiesCache
                );
            }
        }

        $cargo->status = true;
        $cargo->save();

        return back()->with('success', $counter . ' بار ثبت شد');
    }

    public function storeCargo(
        $origin,
        $originState,
        $destination,
        $destinationState,
        $mobileNumber,
        $description,
        $fleet,
        $freight,
        $priceType,
        $title,
        $pattern,
        &$counter,
        $cargoId,
        $fleetsCache,
        $citiesCache
    ) {
        if (!$origin || !$destination || !$fleet || !$mobileNumber) return;

        $freight = convertFaNumberToEn(str_replace(',', '', $freight));
        if (substr($mobileNumber, 0, 1) !== '0') {
            $mobileNumber = '0' . $mobileNumber;
        }

        $cargoPattern = $origin . $destination . $mobileNumber . $fleet;

        // 🚀 چک Duplicate سریع‌تر
        if (
            BlockPhoneNumber::where('phoneNumber', $mobileNumber)->exists() ||
            Load::where('cargoPattern', $cargoPattern)
            ->where('created_at', '>', now()->subMinutes(180))
            ->exists()
        ) {
            return;
        }

        DB::transaction(function () use (
            $origin,
            $originState,
            $destination,
            $destinationState,
            $mobileNumber,
            $description,
            $fleet,
            $freight,
            $priceType,
            $title,
            $pattern,
            &$counter,
            $cargoId,
            $fleetsCache,
            $citiesCache,
            $cargoPattern
        ) {
            $load = new Load();
            $load->title = $title ?: 'بدون عنوان';
            $load->pattern = $pattern;
            $load->cargo_convert_list_id = $cargoId;
            $load->senderMobileNumber = $mobileNumber;
            $load->emergencyPhone = $mobileNumber;
            $load->cargoPattern = $cargoPattern;
            // $load->user_id = auth()->id();
            // $load->userType = ROLE_OPERATOR;
            if (isSendBotLoadOwner() == true) {
                $owner = Owner::where('mobileNumber', $mobileNumber)->first();
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

            // $load->operator_id = auth()->id();
            $origin = str_replace('_', ' ', str_replace('[', '', str_replace(']', '', $origin)));
            $destination = str_replace('_', ' ', str_replace('[', '', str_replace(']', '', $destination)));

            // ✅ پیدا کردن شهر از cache
            $originCity = $citiesCache
                ->where('name', $origin)
                ->where('parent_id', $originState)
                ->first();

            $destinationCity = $citiesCache
                ->where('name', $destination)
                ->where('parent_id', $destinationState)
                ->first();

            // return dd($destinationCity);
            $load->origin_city_id = $originCity->id ?? null;
            $load->destination_city_id = $destinationCity->id ?? null;

            try {
                $city = ProvinceCity::where('parent_id', '!=', 0)->find($load->origin_city_id);
                if (isset($city->id)) {
                    $load->latitude = $city->latitude;
                    $load->longitude = $city->longitude;
                }
            } catch (\Exception $exception) {
            }

            $load->fromCity = $this->getCityName($load->origin_city_id);
            $load->toCity   = $this->getCityName($load->destination_city_id);
            $load->origin_state_id = AddressController::geStateIdFromCityId($load->origin_city_id);

            $load->loadingDate = gregorianDateToPersian(now()->format('Y-m-d'), '-');
            $load->time = time();
            $load->priceBased = $priceType;
            $load->proposedPriceForDriver = $freight;
            $load->suggestedPrice = $freight;
            $load->mobileNumberForCoordination = $mobileNumber;
            $load->storeFor = ROLE_DRIVER;
            $load->status = ON_SELECT_DRIVER;
            $load->deliveryTime = 24;
            $load->date = gregorianDateToPersian(now()->format('Y/m/d'), '/');
            $load->dateTime = now()->format('H:i:s');

            $fleet = str_replace('_', ' ', str_replace('[', '', str_replace(']', '', $fleet)));

            // ✅ fleet از cache
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
            $loadDuplicateOwnerBot = Load::where($conditions)
                ->where('userType', 'owner')
                ->where('isBot', 1)
                ->first();
            // return dd($loadDuplicate);

            if ($loadDuplicate || $loadDuplicateOwnerBot) {
                collect([$loadDuplicate, $loadDuplicateOwnerBot])
                    ->filter()
                    ->each(fn($duplicate) => $duplicate->delete());

                $load->save();
            }


            $loadDuplicateOwner = Load::where($conditions)
                ->where('userType', 'owner')
                ->where('isBot', 0)
                // ->withTrashed()
                ->first();

            if (is_null($loadDuplicate) && is_null($loadDuplicateOwner)) {
                $load->save();
            }


            if (isset($load->id)) {
                $counter++;

                // if ($fleet_id->id == 86) {
                //     $fleet_ids = [86, 87];
                //     foreach ($fleet_ids as $id) {
                //         $fleetLoad = new FleetLoad();
                //         $fleetLoad->load_id = $load->id;
                //         $fleetLoad->fleet_id = $id;
                //         $fleetLoad->numOfFleets = 1;
                //         $fleetLoad->userType = $load->userType;
                //         $fleetLoad->save();
                //     }
                // } else {
                //     $fleetLoad = new FleetLoad();
                //     $fleetLoad->load_id = $load->id;
                //     $fleetLoad->fleet_id = $fleet_id->id;
                //     $fleetLoad->numOfFleets = 1;
                //     $fleetLoad->userType = $load->userType;
                //     $fleetLoad->save();
                // }
                $fleetLoad = new FleetLoad();
                $fleetLoad->load_id = $load->id;
                $fleetLoad->fleet_id = $fleet_id->id;
                $fleetLoad->numOfFleets = 1;
                $fleetLoad->userType = $load->userType;
                $fleetLoad->save();
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

                if ($fleet_id) {


                    $load->fleets = FleetLoad::join('fleets', 'fleets.id', 'fleet_loads.fleet_id')
                        ->where('fleet_loads.load_id', $load->id)
                        ->select('fleet_id', 'userType', 'suggestedPrice', 'numOfFleets', 'pic', 'title')
                        ->get();
                    $load->save();
                }

                try {
                    // گزارش بار ها بر اساس اپراتور
                    $loadOwnerCount = LoadOwnerCount::firstOrNew([
                        'mobileNumber' => $mobileNumber,
                        'persian_date' => $persian_date,
                    ]);

                    $loadOwnerCount->count = ($loadOwnerCount->count ?? 0) + 1;
                    $loadOwnerCount->save();
                } catch (\Exception $e) {
                    Log::emergency($exception->getMessage());
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
        });
    }



    public function sendLoadToOtherWeb($load)
    {
        // تبدیل کل داده‌های $load به JSON
        $data = json_encode($load);

        // تنظیم URL API
        $url = 'https://dashboard.elambar-sarasari.ir/api/storeLoad';

        // مقداردهی اولیه cURL
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($ch);
        curl_close($ch);

        // بررسی پاسخ API
        // if ($response) {
        //     Log::warning('ارسال شد');
        // } else {
        //     Log::warning('خطا');
        // }
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

    /***************************************************************************************************/
    // دیکشنری کلمات معادل در ثبت بار
    public function dictionary()
    {
        $cities = ProvinceCity::all();
        $fleets = Fleet::where('parent_id', '>', 0)->get();

        $dictionary = Equivalent::paginate(300);

        return view('admin.dictionary', compact('cities', 'fleets', 'dictionary'));
    }

    // دیکشنری کلمات معادل در ثبت بار
    public function equivalents()
    {
        $cities = ProvinceCity::where('parent_id', '!=', 0)
            ->orderBy('name', 'asc')
            ->select(['id', 'name'])
            ->get();
        // return $cities;

        $fleets = Fleet::where('parent_id', '>', 0)->get();

        $dictionary = Equivalent::paginate(500);

        return view('admin.equivalent.index', compact('cities', 'dictionary', 'fleets'));
    }

    public function addWordToEquivalent(Request $request)
    {
        try {
            $original_word_id = $request->type == 'city' ? $request->city_id : $request->fleet_id;
            if (Equivalent::where([
                ['equivalentWord', $request->equivalentWord],
                ['type', $request->type],
                ['original_word_id', $original_word_id],
            ])->count() > 0)
                return back()->with('danger', 'کلمه اصلی، کلمه معادل و دسته تکراری است');

            if (strlen($request->equivalentWord)) {
                $dictionary = new Equivalent();
                $dictionary->type = $request->type;
                $dictionary->original_word_id = $original_word_id;
                $dictionary->equivalentWord = $request->equivalentWord;
                $dictionary->save();

                return back()->with('success', 'کلمه مورد نظر ثبت شد');
            }
        } catch (\Exception $exception) {
        }

        return back()->with('danger', 'خطا در ذخیره');
    }

    public function addWordToDictionary(Request $request)
    {
        try {
            $original_word_id = $request->type == 'city' ? $request->city_id : $request->fleet_id;
            if (Dictionary::where([
                ['equivalentWord', $request->equivalentWord],
                ['type', $request->type],
                ['original_word_id', $original_word_id],
            ])->count() > 0)
                return back()->with('danger', 'کلمه اصلی، کلمه معادل و دسته تکراری است');

            if (strlen($request->equivalentWord)) {
                $dictionary = new Dictionary();
                $dictionary->type = $request->type;
                $dictionary->original_word_id = $original_word_id;
                $dictionary->equivalentWord = $request->equivalentWord;
                $dictionary->save();

                return back()->with('success', 'کلمه مورد نظر ثبت شد');
            }
        } catch (\Exception $exception) {
        }

        return back()->with('danger', 'خطا در ذخیره');
    }

    public function removeDictionaryWord(Dictionary $dictionary)
    {
        $dictionary->delete();
        return back()->with('success', ' کلمه ' . $dictionary->equivalentWord . ' حذف شد ');
    }

    public function removeEquivalentWord(Equivalent $equivalent)
    {
        $equivalent->delete();
        return back()->with('success', ' کلمه ' . $equivalent->equivalentWord . ' حذف شد ');
    }

    /**************************************************************************************************/
    // یک مبدا و یک مقصد
    /**************************************************************************************************/

    public function singleOriginSingleDestination($text)
    {
        // الگورتیم تغییر کند و از روی آرایه جستجو کند،
        // همچنین شهرها، ناوگان و کلماتی شبیه به "هر تن" که دوکلمه از هم جدا هستند ابتدا بررسی و بین آنها علامت _ "Underline" گذاشته شود
        // کلمات همسان هم دسته بندی و مشخص شود

        try {

            $weight = 0;
            $freight = 0;
            $originText = str_replace(array("\r", "\n"), '<br>', $text);;

            $text = str_replace(array("\r", "\n"), ' ', $text);
            $array = explode(' ', $text);

            $array = $this->removeItem($array, ['', ' ']);
            $cities = $this->getCitiesList();


            $origin = $this->getOrigin($array);
            $destination = $this->getDestination($array, $cities, $origin);

            // دریافت شماره تلفن
            $mobileNumber = $this->getMobileNumber($text);
            $array = $this->removeItem($array, [$mobileNumber]);
            $text = str_replace($mobileNumber, '', $text);


            // استخراج وزن
            $weight = $this->getWeight($text);

            $text = str_replace($weight, '', $text);

            $freight = $this->getFreight($text);

            $fleetsList = $this->getFleetsList();

            $fleets = [];
            foreach ($fleetsList as $key => $fleet) {
                $text = str_replace($fleet, str_replace(' ', '_', $fleet), $text);
                $fleetsList[$key] = str_replace($fleet, str_replace(' ', '_', $fleet), str_replace('ک', 'ك', str_replace('ی', 'ي', $fleet)));
            }
            $array = explode(' ', $text);
            $array = $this->removeItem($array, ['', ' ']);

            foreach ($fleetsList as $fleet) {
                if (in_array($fleet, $array))
                    $fleets[] = $fleet;
            }

            $data[] = [
                'origin' => $origin,
                'destination' => $destination,
                'weight' => $weight,
                'mobileNumber' => $mobileNumber,
                'freight' => $freight,
                'fleets' => $fleets
            ];

            return $data;
        } catch (\Exception $exception) {
            return back()->with('danger', 'خطایی رخ داده لطفا اطلاعات وارد شده را بررسی کنید');
        }
    }

    /**************************************************************************************************/
    // هر خط یک بار
    /**************************************************************************************************/

    public function everyLineIsOneCargo($texts)
    {

        try {

            $originText = str_replace(array("\r", "\n"), '<br>', $texts);;

            $texts = $this->cleanText(convertFaNumberToEn($texts));

            $mobileNumber = $this->getMobileNumber($texts);

            $texts = explode("\n", $texts);

            $cargoInfo = [];

            $cities = $this->getCitiesList();

            foreach ($texts as $text) {

                $origin = '';
                $destination = '';

                $text = str_replace(array("\r", "\n"), ' ', $text);


                $array = $this->removeItem(explode(' ', $text), ['', ' ']);
                foreach ($array as $item) {
                    if (in_array($item, $cities))
                        if ($origin == '')
                            $origin = $item;
                        else if ($destination == '')
                            $destination = $item;
                }

                $fleet = '';


                if (strlen($origin) > 0 && strlen($destination) > 0)
                    $cargoInfo[] =
                        'از ' . $origin . "\n" .
                        ' به ' . $destination . "\n" .
                        $mobileNumber . "\n" .
                        ' وزن 0' . "\n" .
                        ' کرایه توافقی' . "\n" .
                        ' ' . $fleet;
            }

            return $cargoInfo;

            return view('admin.storeCargoForm', compact('cargoInfo', 'originText'));
        } catch (\Exception $exception) {

            return back()->with('danger', 'خطایی رخ داده لطفا اطلاعات وارد شده را بررسی کنید');
        }
    }


    /**************************************************************************************************/
    // یک مبدا و چند مقصد
    /**************************************************************************************************/

    public function singleOriginMultiDestination($text)
    {
        try {

            $text = convertFaNumberToEn($text);
            $text = str_replace('ک', 'ک', $text);
            $text = str_replace('ی', 'ي', $text);


            $originText = str_replace(array("\r", "\n"), '<br>', $text);;

            $destinations = [];
            $firstItemIsFleet = false;

            $cities = $this->getCitiesList();

            $fleetsList = $this->getFleetsList();

            foreach ($fleetsList as $key => $fleet) {
                $text = str_replace($fleet, ' ' . str_replace(' ', '_', $fleet) . ' ', $text);
                $fleetsList[$key] = str_replace($fleet, str_replace(' ', '_', $fleet), str_replace('ک', 'ك', str_replace('ی', 'ي', $fleet)));
            }

            foreach ($cities as $key => $city) {
                $text = str_replace($city, ' ' . str_replace(' ', '_', $city) . ' ', $text);
                $cities[$key] = str_replace($city, str_replace(' ', '_', $city), $city);
            }


            $text = str_replace(array("\r", "\n"), ' ', $text);

            $array = $this->removeItem(explode(' ', $text), ['', ' ']);

            $origin = $this->getOrigin($array);

            $text = str_replace($origin, '', $text);
            $mobileNumber = $this->getMobileNumber($text);

            $text = str_replace(array("\r", "\n"), ' ', str_replace($mobileNumber, '', $text));

            $array = explode(' ', $text);

            $freight = $this->getFreight($text);

            // زمان جستجوی ناوگان اگر چند ناوگان بود باید اول نگاه کند که بعد از مبدا شهر است یا ناوگان
            // اگر شهر بود و بعدش ناوگان بود یعنی ترتیب به این صورت است که: شهر مقصد بعد ناوگان
            // اگر ناوگان بود بعدش شهر یعنی ترتیب به این صورت است : ناوگان و شهر
            // اگر بعد از شهرها پشت سر هم و بعدش ناوگان پشت سر هم بود یعنی برای هر مقصد تمام ناوگان ها لازم است
            foreach ($array as $item)
                if ($item != $origin)
                    if (in_array($item, $fleetsList)) {
                        $firstItemIsFleet = true;
                        break;
                    } else if (in_array($item, $cities))
                        break;

            $fleetDestinations = [];

            if ($firstItemIsFleet) {
                // اول شروع کند ناوگان ها را پیدا کند بعد شهر را
                // تمام ناوگان ها تا رسیدن به شهر دریافت شوند

                $fleet = [];
                foreach ($array as $item) {
                    if (in_array($item, $fleetsList) && !in_array($item, $cities))
                        $fleet[] = $item;
                    else if ((in_array($item, $cities) && $item != $origin)) {
                        $fleetDestinations[] = [
                            'city' => $item,
                            'fleet' => $fleet
                        ];
                        $fleet = [];
                    }
                }
            }

            $allFleets = [];

            foreach ($fleetsList as $key => $fleetItem)
                $fleetsList[$key] = str_replace(' ', '_', $fleetItem);


            foreach ($fleetsList as $fleetItem)
                if (in_array($fleetItem, $array))
                    $allFleets[] = $fleetItem;

            // اول شروع کند ناوگان ها را پیدا کند بعد شهر را
            // تمام ناوگان ها تا رسیدن به شهر دریافت شوند
            if (!$firstItemIsFleet) {
                $city = '';
                $fleet = [];
                foreach ($array as $key => $item) {
                    if (in_array($item, $cities) && $item != $origin && $city == '') {
                        $city = $item;
                    } else if (in_array($item, $fleetsList) && !in_array($item, $cities)) {
                        $fleet[] = $item;
                    } else if (count($array) - 1 == $key || (in_array($item, $cities) && $item != $city)) {
                        $fleetDestinations[] = [
                            'city' => $city,
                            'fleet' => count($fleet) == 0 ? $allFleets : $fleet
                        ];
                        $city = '';

                        $fleet = [];
                        if (in_array($item, $cities))
                            $city = $item;
                    }
                }
            }

            // استخراج وزن
            $weight = $this->getWeight($text);

            $data = [
                'freight' => $freight,
                'fleetDestinationItems' => $fleetDestinations
            ];

            $cargoInfo = [];


            foreach ($data['fleetDestinationItems'] as $fleetDestinationItem) {

                $fleet = '';

                foreach ($fleetDestinationItem['fleet'] as $item)
                    $fleet .= ' ' . $item;


                $cargoInfo[] =
                    'از ' . $origin . "\n" .
                    ' به ' . $fleetDestinationItem['city'] . "\n" .
                    $mobileNumber . "\n" .
                    ' وزن ' . $weight . ' تن ' . "\n" .
                    ' کرایه ' . $data['freight']['freight'] . ' ' . $data['freight']['priceType'] . "\n" .
                    ' ' . $fleet;
            }

            return view('admin.storeCargoForm', compact('cargoInfo', 'originText'));
        } catch (\Exception $exception) {
            return back()->with('danger', 'خطایی رخ داده لطفا اطلاعات وارد شده را بررسی کنید');
        }
    }

    /*******************************************************************************************/
    /*******************************************************************************************/

    // دریافت قیمت
    private function getFreight($text)
    {
        $freight = 0;
        $prefixFreightConditions = array('صافی', 'صاف', 'هرتن', 'کرایه', 'قیمت');
        $postfixFreightConditions = array('صافی', 'صاف', 'هرتن', 'کرایه', 'م', 'میلیون');

        $priceType = '';

        foreach ($prefixFreightConditions as $item) {
            $pattern = "/$item\s+(\d+)/";
            if (preg_match($pattern, $text, $matches)) {
                $freight = $matches[1];
                break;
            }
        }

        if ($freight == 0)
            foreach ($postfixFreightConditions as $item) {
                $pattern = "/(\d+)\s+$item/";
                if (preg_match($pattern, $text, $matches)) {
                    $freight = $matches[1];
                    break;
                }
            }

        if (strpos($text, "[هرتن]"))
            $priceType = "هرتن";
        else if (strpos($text, "[صاف]"))
            $priceType = "صاف";
        else if (strpos($text, "[صافی]"))
            $priceType = "صافی";

        if ($freight == 0)
            $priceType = 'توافقی';
        else
            $freight = $freight < 1000 ? $freight * 1000000 : $freight;

        return [
            'freight' => $freight,
            'priceType' => $priceType
        ];
    }



    public function cargoConvertLists()
    {

        $duplicated = DB::table('cargo_convert_lists')
            ->select('cargo', DB::raw('count(`cargo`) as occurences'))
            ->groupBy('cargo')
            ->having('occurences', '>', 1)
            ->get();

        $duplicatedMessages = DB::table('cargo_convert_lists')
            ->select('message_id', DB::raw('count(`message_id`) as occurences'))
            ->groupBy('message_id')
            ->having('occurences', '>', 1)
            ->get();

        foreach ($duplicated as $duplicate) {
            CargoConvertList::where('cargo', $duplicate->cargo)->delete();
        }
        foreach ($duplicatedMessages as $duplicatedMessage) {
            CargoConvertList::where('message_id', $duplicatedMessage->message_id)->delete();
        }
        return back()->with('success', 'بار تکراری حذف شد');
    }

    // دریافت وزن
    private function getWeight($text)
    {
        $weight = 0;
        $pattern = "/(\d+)\s+تن/";
        if (preg_match($pattern, $text, $matches))
            $weight = $matches[1];

        if ($weight == 0) {
            $pattern = "/وزن\s+(\d+)/";
            if (preg_match($pattern, $text, $matches))
                $weight = $matches[1];
        }
        return $weight;
    }

    // دریافت مبدا
    private function getOrigin(array $array, array $cities = null)
    {
        if ($cities == null)
            $cities = $this->getCitiesList();

        $prefixOriginConditions = array('[از]');
        $postfixOriginConditions = array('[به]');
        $originConditions = false;

        $origin = '';

        for ($i = 0; $i < count($array); $i++) {
            if (in_array($array[$i], $prefixOriginConditions) && !$originConditions)
                $originConditions = true;
            else if ($originConditions)
                if (in_array($array[$i], $cities) && strlen($origin) == 0)
                    return $array[$i];
        }

        if (strlen($origin) == 0) {
            $originConditions = false;
            for ($i = 0; $i < count($array); $i++) {
                if (in_array($array[$i], $postfixOriginConditions) && !$originConditions)
                    $originConditions = true;
                else if (in_array($array[$i], $cities) && !$originConditions && strlen($origin) == 0)
                    return $array[$i];
            }
        }

        if (strlen($origin) == 0) {
            for ($i = 0; $i < count($array); $i++)
                if (strlen($origin) == 0 && in_array($array[$i], $cities))
                    return $array[$i];
        }
        return $origin;
    }

    // دریافت مقصد
    private function getDestination(array $cities, array $array, $origin)
    {
        // شرط مهم مبدا و مقصد نباید یکی باشند
        $prefixDestinationConditions = array('به');
        $postfixDestinationConditions = array('از');
        $destinationConditions = false;

        $destination = '';

        for ($i = 0; $i < count($array); $i++) {
            if (in_array($array[$i], $prefixDestinationConditions) && !$destinationConditions)
                $destinationConditions = true;
            else if ($destinationConditions)
                if (in_array($array[$i], $cities) && strlen($destination) == 0 && $array[$i] != $origin) {
                    $destination = $array[$i];
                    break;
                }
        }

        if (strlen($destination) == 0) {
            $destinationConditions = false;
            for ($i = 0; $i < count($array); $i++) {
                if (in_array($array[$i], $postfixDestinationConditions) && !$destinationConditions)
                    $destinationConditions = true;
                else if (in_array($array[$i], $cities) && !$destinationConditions && strlen($destination) == 0 && $array[$i] != $origin) {
                    $destination = $array[$i];
                    break;
                }
            }
        }

        if (strlen($destination) == 0) {
            for ($i = 0; $i < count($array); $i++)
                if (strlen($destination) == 0 && $array[$i] != $origin && in_array($array[$i], $cities)) {
                    $destination = $array[$i];
                    break;
                }
        }
        return $destination;
    }

    // دریافت شماره تلفن
    private function getMobileNumber($text)
    {
        $pattern = '/0\d{10}/';
        preg_match($pattern, $text, $matches);
        return isset($matches[0]) ? $matches[0] : '';
    }


    /**
     * @param array $array
     * @return array
     */
    private function removeItem(array $array, array $removeItems): array
    {
        $temp = [];
        foreach ($array as $key => $item)
            if (!in_array($item, $removeItems))
                $temp[] = $array[$key];

        return $temp;
    }


    public function storeLoad($data)
    {

        $counter = 0;
        $senderMobileNumber = $data[0]['mobileNumber'];
        try {
            if (BlockPhoneNumber::where('phoneNumber', $senderMobileNumber)->count()) {
                echo 'BlockPhoneNumber';
                return;
            }
        } catch (\Exception $exception) {
        }

        try {

            DB::beginTransaction();

            $load = new Load();
            $load->title = "بدون عنوان";
            $load->weight = $data[0]['weight'];
            $load->width = 0;
            $load->length = 0;
            $load->height = 0;
            $load->loadingAddress = '';
            $load->dischargeAddress = '';
            $load->senderMobileNumber = $senderMobileNumber;
            $load->receiverMobileNumber = '';
            $load->insuranceAmount = 0;
            $load->suggestedPrice = isset($data[0]['freight']['freight']) ? $data[0]['freight']['freight'] : 1;
            $load->marketing_price = 0;
            $load->emergencyPhone = $senderMobileNumber;
            $load->dischargeTime = '';
            $load->fleet_id = $data[0]['fleets'][0];
            $load->load_type_id = 0;
            $load->tenderTimeDuration = 0;
            $load->packing_type_id = 0;
            $load->loadPic = "noImage";
            $load->user_id = auth()->id();
            $load->userType = ROLE_OPERATOR;
            $load->loadMode = 'outerCity';
            $load->loadingHour = 0;
            $load->loadingMinute = 0;
            $load->numOfTrucks = 1;


            $data[0]['origin'] = str_replace('_', ' ', $data[0]['origin']);
            $data[0]['destination'] = str_replace('_', ' ', $data[0]['destination']);


            $load->origin_city_id = $this->getCityId($data[0]['origin']);
            $load->destination_city_id = $this->getCityId($data[0]['destination']);


            $load->fromCity = $this->getCityName($load->origin_city_id);
            $load->toCity = $this->getCityName($load->destination_city_id);

            $load->loadingDate = gregorianDateToPersian(date('Y-m-d', time()), '-');
            $load->time = time();

            try {
                $city = ProvinceCity::find($load->origin_city_id);
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
            $load->description = '';

            $load->priceBased = $data[0]['freight']['priceType'];
            $load->operator_id = auth()->id();
            $load->proposedPriceForDriver = $data[0]['freight']['freight'];
            $load->mobileNumberForCoordination = $data[0]['mobileNumber'];
            $load->storeFor = ROLE_DRIVER;
            $load->status = ON_SELECT_DRIVER;
            $load->deliveryTime = 24;

            $load->urgent = 0;
            $load->save();


            if (isset($load->id) && isset($data[0]['fleets'])) {

                foreach ($data[0]['fleets'] as $item) {

                    $item = str_replace('_', ' ', $item);

                    $fleet_id = Fleet::where('title', $item)->first();
                    if (!isset($fleet_id->id)) {
                        $fleet_id = Fleet::where('title', str_replace('ك', 'ک', $item))->first();
                    }
                    if (!isset($fleet_id->id)) {
                        $fleet_id = Fleet::where('title', str_replace('ي', 'ی', $item))->first();
                    }
                    if (!isset($fleet_id->id)) {
                        $fleet_id = Fleet::where('title', str_replace('ي', 'ی', str_replace('ك', 'ک', $item)))->first();
                    }
                    $persian_date = gregorianDateToPersian(Carbon::now());

                    if (isset($fleet_id->id)) {
                        $fleetLoad = new FleetLoad();
                        $fleetLoad->load_id = $load->id;
                        $fleetLoad->fleet_id = $fleet_id->id;
                        $fleetLoad->numOfFleets = 1;
                        $fleetLoad->userType = $load->userType;
                        $fleetLoad->save();

                        try {
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
                    $backup->marketing_price = 0;
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

                    $counter++;
                } catch (\Exception $e) {
                    Log::emergency("========================= Load Backup ==================================");
                    Log::emergency($e->getMessage());
                    Log::emergency("==============================================================");
                }
            }

            DB::commit();
        } catch (\Exception $exception) {

            DB::rollBack();

            Log::emergency("----------------------ثبت بار جدید-----------------------");
            Log::emergency($exception->getMessage());
            Log::emergency("---------------------------------------------------------");
        }


        return redirect(url('admin/storeCargoConvertForm'))->with('success', $counter . 'بار ثبت شد');
    }


    /*****************************************************************************************************/
    // تمیز کردن متن
    private function cleanText($text)
    {
        $text = str_replace('_', ' ', str_replace('-', ' ', str_replace(',', ' ', $text)));

        $fleetsList = $this->getFleetsList();
        $cities = $this->getCitiesList();

        foreach ($fleetsList as $key => $fleet) {
            $fleetsList[$key] = ' ' . str_replace(' ', '_', $fleet) . ' ';
            $text = str_replace($fleet, ' ' . str_replace(' ', '_', $fleet) . ' ', $text);
        }

        foreach ($cities as $key => $city) {
            $cities[$key] = ' ' . str_replace(' ', '_', $city) . ' ';
            $text = str_replace($city, ' ' . str_replace(' ', '_', $city) . ' ', $text);
        }
        return $text;
    }


    /*******************************************************************************************/
    // کانال ها
    public function channels()
    {

        //        $client = new Client();
        //        $res = $client->request('GET', 'http://5.78.107.150:8000/channels');
        //        dd($res->getBody());
        $result = json_decode(file_get_contents('http://5.78.107.150:8000/channels'), true);
        return view('admin.channels', compact('result'));
    }

    public function removeChannel($channel)
    {
        $client = new Client();
        $res = $client->request('POST', 'http://5.78.107.150:8000/channel/' . $channel . '/delete');
        return back()->with('success', 'کانال ' . $channel . ' حذف شد');
    }

    public function newChannel(Request $request)
    {
        $client = new Client();
        $res = $client->request('POST', 'http://5.78.107.150:8000/channel/' . $request->channelName . '/create');
        return back()->with('success', 'کانال ' . $request->channelName . ' اضافه شد');
    }

    public function extractData(Request $request)
    {
        $text = $request->input('text');

        // حذف فاصله‌های اضافی و نرمال‌سازی متن
        $text = $request->input('text');

        // 1. حذف ایموجی‌ها و کاراکترهای یونیکد خاص
        $text = preg_replace('/[\x{1F600}-\x{1F6FF}\x{1F300}-\x{1F5FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', '', $text);

        // 2. حذف فاصله‌های اضافی و نرمال‌سازی متن
        $text = preg_replace('/\s+/', ' ', $text);
        // دریافت لیست ناوگان‌ها از دیتابیس
        $fleets = DB::table('fleets')->pluck('title', 'id')->toArray();
        $fleetNames = array_values($fleets);
        $fleetPattern = implode('|', array_map('preg_quote', $fleetNames));

        // دریافت لیست شهرها از دیتابیس
        $cities = DB::table('province_cities')->pluck('name', 'id')->toArray();

        // خروجی نهایی
        $allResults = [];

        // بررسی ناوگان و مسیرهای ساده
        if (preg_match('/^(' . $fleetPattern . ')\s+(\S+)\s+(?:ب|به)\s+(\S+)/mu', $text, $fleetMatch)) {
            $fleetName = $fleetMatch[1];
            $fleetId = array_search($fleetName, $fleets);

            // حذف ناوگان از متن برای جستجوی مبدا و مقصد
            $textWithoutFleet = str_replace($fleetName, '', $text);

            // استخراج مسیرها
            if (preg_match_all('/(\S+)\s+(?:ب|به)\s+(\S+)/u', $textWithoutFleet, $routeMatches, PREG_SET_ORDER)) {
                foreach ($routeMatches as $route) {
                    $origin = $route[1] ?? null;
                    $destination = $route[2] ?? null;

                    // اضافه کردن خروجی برای هر مسیر
                    $allResults[] = [
                        'type' => 'الگوی 4+',
                        'vehicle' => $fleetName,
                        'vehicle_id' => array_search($fleetName, $fleets) !== false ? array_search($fleetName, $fleets) : null,
                        'origin' => $origin,
                        'origin_id' => array_search($origin, $cities) ?: null,
                        'destination' => $destination,
                        'destination_id' => array_search($destination, $cities) ?: null,
                    ];
                }
            }
        }

        // الگویی برای شناسایی اگر یک مبدا و چند مقصد داریم
        if (preg_match('/^(' . $fleetPattern . ')\s+(\S+)\s+(?:ب|به)\s+([\S\s]+?)\s*(\d{11})/mu', $text, $fleetMatch)) {
            $fleetName = $fleetMatch[1];
            $fleetId = array_search($fleetName, $fleets);
            $origin = $fleetMatch[2];
            $destinationString = $fleetMatch[3];
            $phone = $fleetMatch[4];

            // پردازش مقاصد مختلف
            $destinations = explode(' ', $destinationString);
            foreach ($destinations as $destination) {
                $allResults[] = [
                    'type' => 'الگوی 4+ چندمسیره',
                    'vehicle' => $fleetName,
                    'vehicle_id' => $fleetId !== false ? $fleetId : null,
                    'origin' => $origin,
                    'origin_id' => array_search($origin, $cities) ?: null,
                    'destination' => $destination,
                    'destination_id' => array_search($destination, $cities) ?: null,
                    'phone' => $phone
                ];
            }
        }

        // بررسی سایر الگوها
        $patterns = [
            // الگوی 1
            [
                'type' => 'الگوی 1',
                'regex' => '/بارگیری\s+(.*?)\s+از\s+(\S+)\s+به\s+(\S+)\s*(\d{11})/su',
                'fields' => ['cargo', 'origin', 'destination', 'phone']
            ],
            // الگوی 2
            [
                'type' => 'الگوی 2',
                'regex' => '/ناوگان[:：]\s*(.*?)\s+مبدا[:：]\s*(.*?)\s+(?:به\s+)?مقصد[:：]\s*(.*?)\s+(\d{11})/su',
                'fields' => ['vehicle', 'origin', 'destination', 'phone']
            ],
            // الگوی 3 - ناوگان، مبدا، مقصد، نوع بار و وزن بار
            [
                'type' => 'الگوی جدید 3',
                'regex' => '/(\S+)\s+[\s\S]*?مبدا[:：]\s*(\S+)\s+مقصد[:：]\s*(\S+)\s+نوع\s+بار[:：]\s*(\S+)\s*(\d{11})(?:\s*(\d{11}))?(?:\s*(\d{11}))?/mu',
                'fields' => ['vehicle', 'origin', 'destination', 'cargo', 'phone']
            ],
            // الگوی 4+ چندمسیره
            [
                'type' => 'الگوی جدید 4',
                'regex' => '/(\S+)\s+(?:از)\s+(\S+)\s+(?:به)\s+(\S+)\s*(\d{11})/mu',
                'fields' => ['vehicle', 'origin', 'destination', 'phone']
            ],
            [
                'type' => 'الگوی 4+ چندمسیره',
                'regex' => '/(\S+)\s+از\s+(\S+)\s+(?:به|ب)\s+([\S\s]+?)\s*(\d{11})/mu',
                'fields' => ['vehicle', 'origin', 'destination', 'phone']
            ],
            // الگوی 5 - بارگیری و تخلیه
            [
                'type' => 'الگوی 5',
                'regex' => '/بارگیری\s+(.*?)\s+از\s+(\S+)\s+به\s+(\S+)\s+بارگیری\s+امروز\s+(.*)\s+تخلیه\s+فردا\s+(.*)/su',
                'fields' => ['cargo', 'origin', 'destination', 'loading_time', 'unloading_time']
            ],
            // الگوی 6 - بار و نوع آن (سبک، سنگین)
            [
                'type' => 'الگوی 6',
                'regex' => '/(\S+)\s+از\s+(\S+)\s+به\s+(\S+)\s+نوع\s+بار\s+(سبک|سنگین)/su',
                'fields' => ['vehicle', 'origin', 'destination', 'cargo_type']
            ],
            // الگوی 7 - تماس و شماره
            [
                'type' => 'الگوی 7',
                'regex' => '/تماس\s+با\s+(\S+)\s+در\s+نوبت\s+مراجعه\s+به\s+(\S+)\s+با\s+شماره\s+(\d{11})/su',
                'fields' => ['contact_person', 'office_location', 'phone']
            ],
            // الگوی 8 - نوع بار و تاریخ
            [
                'type' => 'الگوی 8',
                'regex' => '/(\S+)\s+از\s+(\S+)\s+به\s+(\S+)\s+بار\s+نوع\s+(.+)\s+تاریخ\s+مراجعه\s+(\d{4}-\d{2}-\d{2})/su',
                'fields' => ['vehicle', 'origin', 'destination', 'cargo_type', 'date']
            ],
            // الگوی 9 - نوع بار و زمان تخلیه
            [
                'type' => 'الگوی 9',
                'regex' => '/(\S+)\s+از\s+(\S+)\s+به\s+(\S+)\s+بار\s+نوع\s+(.+)\s+زمان\s+تخلیه\s+(\d{2}:\d{2})/su',
                'fields' => ['vehicle', 'origin', 'destination', 'cargo_type', 'unloading_time']
            ],
            // الگوی 10 - ارسال از مبدأ به مقصد
            [
                'type' => 'الگوی 10',
                'regex' => '/(\S+)\s+از\s+(\S+)\s+به\s+(\S+)\s+بار\s+اندازه\s+(\d+)\s+کیلوگرم/su',
                'fields' => ['vehicle', 'origin', 'destination', 'cargo_weight']
            ],
            // الگوی 11 - زمان بارگیری و تخلیه
            [
                'type' => 'الگوی 11',
                'regex' => '/(\S+)\s+از\s+(\S+)\s+به\s+(\S+)\s+زمان\s+بارگیری\s+(\d{2}:\d{2})\s+زمان\s+تخلیه\s+(\d{2}:\d{2})/su',
                'fields' => ['vehicle', 'origin', 'destination', 'loading_time', 'unloading_time']
            ],
            // الگوی 12 - تماس مستقیم و هماهنگی
            [
                'type' => 'الگوی 12',
                'regex' => '/(\S+)\s+از\s+(\S+)\s+(?:ب|به)\s+(\S+)\s*(\d{11})/mu',
                'fields' => ['vehicle', 'origin', 'destination', 'phone']
            ],
            [
                'type' => 'الگوی 13',
                'regex' => '/(\S+)\s+از\s+(\S+)\s+به\s+(\S+)\s+بارگیری\s+(.+?)\s*(\d{11})/su',
                'fields' => ['vehicle', 'origin', 'destination', 'cargo', 'phone']
            ]
        ];

        // بررسی الگوهای مختلف
        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern['regex'], $text, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $result = ['type' => $pattern['type']];
                    foreach ($pattern['fields'] as $index => $field) {
                        $value = trim($match[$index + 1]);
                        if ($field === 'origin' || $field === 'destination') {
                            $cityId = array_search($value, $cities);
                            $result[$field] = $value;
                            $result[$field . '_id'] = $cityId !== false ? $cityId : null;
                        } elseif ($field === 'vehicle') {
                            $fleetId = array_search($value, $fleets);
                            $result[$field] = $value;
                            $result[$field . '_id'] = $fleetId !== false ? $fleetId : null;
                        } else {
                            $result[$field] = $value;
                        }
                    }
                    $allResults[] = $result;
                }
            }
        }

        // در صورتی که نتیجه‌ای پیدا شد، برگردانده می‌شود
        if (!empty($allResults)) {
            return response()->json($allResults);
        }

        // در صورتی که هیچ الگویی پیدا نشد
        return response()->json(['error' => 'هیچ الگویی شناسایی نشد.']);
    }



    public function channelsData()
    {
        $data = BotTest::orderby('id', 'desc')->paginate(30);
        return view('admin.channelsData', compact('data'));
    }

    // لیست بارهای رد شده
    public function rejectedCargoFromCargoList()
    {
        $cargoList = CargoConvertList::where('rejected', 1)->orderBy('id', 'desc')->paginate(20);
        return view('admin.rejectCargo.index', compact('cargoList'));
    }

    // لیست بارهای تکراری شده
    public function duplicateCargoFromCargoList(Request $request)
    {
        $cargoList = CargoConvertList::select('cargo', 'created_at', 'isBlocked', 'isDuplicate', DB::raw('COUNT(*) as total'))
            ->where(function ($query) {
                $query->where('isBlocked', 1)
                    ->orWhere('isDuplicate', 1);
            })
            ->when($request->cargo !== null, function ($query) use ($request) {
                $query->where('cargo', 'LIKE', '%' . $request->cargo . '%');
            })
            ->when($request->type !== null, function ($query) use ($request) {
                if ($request->type == 'block') {
                    $query->where('isBlocked', 1);
                } elseif ($request->type == 'duplicate') {
                    $query->where('isDuplicate', 1);
                }
            })
            ->groupBy('cargo')
            // ->having('total', '>', 1) // فقط تکراری‌ها
            ->orderByDesc('created_at')
            ->paginate(20);
        // return $cargoList;

        return view('admin.duplicateCargo.index', compact('cargoList'));
    }

    // لیست بارهای رد شده
    public function searchRejectCargo(Request $request)
    {
        $cargoList = CargoConvertList::where('rejected', 1)
            ->where('cargo', 'LIKE', '%' . $request->cargo . '%')
            ->orderBy('id', 'desc')
            ->with('operator')
            ->get();
        // return $cargoList;
        if (count($cargoList) > 0)
            return view('admin.rejectCargo.search', compact('cargoList'));
        else
            return back()->with('danger', 'با مورد نظر یافت نشد');
    }

    public function allRejectedCargoCount()
    {
        $cargoList = CargoConvertList::with('operator')
            ->where('rejected', 1)
            //            ->select('operator_id','persian_date', DB::raw('sum(calls) as countOfCalls'))
            ->get();
        $groupBys = $cargoList->groupBy('operator.lastName');

        return view('admin.rejectedCargoFromCargoListCount', compact('groupBys'));
    }
    public function rejectCargoCount()
    {
        $persian_date = gregorianDateToPersian(date('Y/m/d', time()), '/');
        $rejects = RejectCargoOperator::where('created_at', '>', date('Y-m-d', time()) . ' 00:00:00')->get();
        return view('admin.rejectCargo', compact('rejects', 'persian_date'));
    }

    /********************************************************************************************************/
    // تعیین دسترسی اپراتور ها به بارها براساس ناوگان
    public function operatorCargoListAccess(Request $request, User $user)
    {
        try {

            DB::beginTransaction();

            OperatorCargoListAccess::where('user_id', $user->id)->delete();

            foreach ($request->cargoAccess as $cargoAccess) {
                $OperatorCargoListAccess = new OperatorCargoListAccess();
                $OperatorCargoListAccess->user_id = $user->id;
                $OperatorCargoListAccess->fleet_id = $cargoAccess;
                $OperatorCargoListAccess->save();
            }

            DB::commit();

            return back()->with('success', 'دسترسی اپراتور به بار براساس ناوگان ثبت شد');
        } catch (\Exception $exception) {

            DB::rollBack();
        }

        return back()->with('danger', 'خطا در ثبت دسترسی اپراتور به بار براساس ناوگان!');
    }
}
