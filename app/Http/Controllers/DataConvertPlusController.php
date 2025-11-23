<?php

namespace App\Http\Controllers;

use App\Models\BlockPhoneNumber;
use App\Models\CargoConvertList;
use App\Models\CargoReportByFleet;
use App\Models\Equivalent;
use App\Models\Fleet;
use App\Models\FleetLoad;
use App\Models\Load;
use App\Models\LoadOwnerCount;
use App\Models\OperatorCargoListAccess;
use App\Models\Owner;
use App\Models\ProvinceCity;
use App\Models\RejectCargoOperator;
use App\Models\User;
use App\Models\UserActivityReport;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
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
        $userId = auth()->id();

        $agent = new \Jenssegers\Agent\Agent();
        $device = $agent->isMobile() ? "Mobile" : ($agent->isTablet() ? "Tablet" : "Desktop");
        User::whereId($userId)->update([
            'last_active' => now(),
            'device' => $device
        ]);
        $accessDevice = Auth::user()->accessDevice;

        if ($accessDevice !== 'Both' && $accessDevice !== $device) {
            return back()->with('danger', "شما فقط اجازه دسترسی با $accessDevice دارید");
        }

        // ۱. پیدا کردن باری که قبلاً به اپراتور تخصیص داده شده
        $cargo = CargoConvertList::where([
            ['operator_id', $userId],
            ['processingUnit', 0],
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
                        ['processingUnit', 0],
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
                    ['processingUnit', 0],
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
                        $cargo->operator_assigned_at = now();
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
                        ['processingUnit', 0],
                        ['isDuplicate', 0],
                    ])
                    ->oldest('id')
                    ->first();

                if ($newCargo) {
                    $newCargo->operator_id = $userId;
                    $cargo->operator_assigned_at = now();
                    $newCargo->save();
                    return $this->dataConvert($newCargo);
                }
            }

            // در نهایت بار فعلی رو بده به اپراتور
            $cargo->operator_id = $userId;
            $cargo->operator_assigned_at = now();
            $cargo->save();
            return $this->dataConvert($cargo);
        }

        // ۴. اگر هیج باری نبود → برگرد به داشبورد
        return redirect(url('dashboard'))->with('danger', 'هیچ باری وجود ندارد');
    }

    /** واژه‌های باری (برای عنوان) که نباید ناوگان حساب شوند */
    private array $cargoWords = [];

    public function __construct()
    {
        $this->cargoWords = $this->loadCargoWordsFromDb();
    }


    protected function loadCargoWordsFromDb(): array
    {
        $rows = DB::table('load_titles')->pluck('title')->all();

        $norm = array_map(fn($w) => $this->normalizeForLexicon((string) $w), $rows);

        $norm = array_values(array_unique(array_filter($norm)));

        usort($norm, fn($a, $b) => mb_strlen($b, 'UTF-8') <=> mb_strlen($a, 'UTF-8'));

        return $norm;
    }

    protected function normalizeForLexicon(string $s): string
    {
        if (method_exists($this, 'normalizeText')) {
            $s = $this->normalizeText($s);
        }
        $s = preg_replace('/\s+/u', ' ', trim($s));
        $s = str_replace(['ي', 'ك'], ['ی', 'ک'], $s);
        return $s;
    }

    /** نگاشت هم‌ارزی برای عنوان */
    private array $titleAliases = [
        'روبار' => 'روباری',
    ];

    /** ایندکس‌های کمکی برای حذف parent هنگام هم‌خطی */
    private array $cityParentsMap = [];     // id => parent_id
    private array $citiesByNameIndex = [];  // name => a single id (سازگاری قدیمی)
    private array $citiesByNameMulti = [];  // name => [ids...]

    public function testApi(Request $request)
    {
        $raw = (string) $request->input('text', '');
        $raw = trim($raw);
        if ($raw === '') {
            return response()->json(['success' => false, 'message' => 'متن ورودی خالی است.']);
        }

        // 1) نرمال‌سازی
        $text = $this->normalizeText($raw);

        // 2) داده‌های پایه
        $citiesById = DB::table('province_cities')->where('parent_id', '!=', 0)->pluck('name', 'id')->toArray(); // id => name
        $fleetsById = DB::table('fleets')->where('parent_id', '!=', 0)->pluck('title', 'id')->toArray();        // id => title

        // 📌 نقشه parent برای تشخیص سلسله‌مراتب شهر/شهرستان/استان
        $this->cityParentsMap = DB::table('province_cities')->pluck('parent_id', 'id')->toArray(); // id => parent_id

        // ایندکس تک‌ارزشی + چند‌ارزشی
        $this->citiesByNameIndex = [];
        $this->citiesByNameMulti = [];
        foreach ($citiesById as $id => $name) {
            $this->citiesByNameIndex[$name] = $id;
            $this->citiesByNameMulti[$name][] = $id;
        }

        // 3) معادل‌ها (با پشتیبانی از چند ناوگان برای یک کلمه)
        [$cityLexicon, $fleetLexicon] = $this->buildLexicons($citiesById, $fleetsById);

        // 4) الگوها
        $cityTokens  = array_keys($cityLexicon);
        usort($cityTokens, fn($a, $b) => mb_strlen($b) - mb_strlen($a));
        $cityPattern = $cityTokens ? implode('|', array_map('preg_quote', $cityTokens)) : '([آ-ی\s\-]+)';

        $fleetTokens  = array_keys($fleetLexicon);
        usort($fleetTokens, fn($a, $b) => mb_strlen($b) - mb_strlen($a));
        $fleetPattern = $fleetTokens ? implode('|', array_map('preg_quote', $fleetTokens)) : '([آ-ی\s\-]+)';

        // 5) تلفن و حذف از متن
        $firstPhone = $this->extractFirstPhone($text);
        if (!empty($firstPhone)) {
            $digits = preg_replace('/\D+/u', '', $firstPhone);
            $variants = [$digits];
            if (strlen($digits) === 11 && substr($digits, 0, 2) === '09') {
                $variants[] = substr($digits, 1); // 9xxxxxxxxx
            }
            foreach ($variants as $v) {
                if ($v !== '') $text = preg_replace('/' . preg_quote($v, '/') . '/u', ' ', $text);
            }
            $text = $this->squashLines($text);
        }

        // 5.1) پیش‌پردازش زوج‌های «شهر-مرکز استان/استان» در خطوط بدون کلیدواژه
        $text = $this->preprocessRegionalPairs($text, $cityPattern, $cityLexicon);

        // 6) قیمت
        $price = $this->extractPrice($text);

        // 7) سگمنت‌ها بر پایه ناوگان
        $segments = $this->splitByFleets($text, $fleetPattern);

        // 8) کانتکست/مسیرهای سراسری
        $globalExplicitOrigin = $this->extractExplicitOriginFirstCity($text, $cityPattern, $cityLexicon);

        // منبع مبدأ از «تعاونی/اتحادیه ... {شهر}»
        $carrierUnionOrigin   = $this->extractCarrierUnionOrigin($text, $fleetPattern, $cityPattern, $cityLexicon);

        $globalContextOrigin  = $globalExplicitOrigin
            ?: $this->getContextOrigin($text, $cityPattern, $cityLexicon)
            ?: $carrierUnionOrigin
            ?: $this->getPrefaceContextOrigin($text, $fleetPattern, $cityPattern, $cityLexicon);

        $prefaceRoute       = $this->extractPrefaceRoute($text, $fleetPattern, $cityPattern, $cityLexicon);
        $globalTwoCityRoute = $this->extractGlobalTwoCityRoute($text, $cityPattern, $cityLexicon);
        $globalDestinations = $this->extractGlobalDestinations($text, $cityPattern, $cityLexicon);

        // نگاشت معکوس ناوگان برای id (عنوان → id)
        $fleetsByTitle = array_flip($fleetsById);

        $allLoads = [];
        foreach ($segments as $segment) {
            // ناوگان‌های سگمنت (ممکن است چندتا باشد)
            $segmentFleets = $this->findFleetsInSegment($segment, $fleetPattern, $fleetLexicon, $this->cargoWords);

            // 🔒 اگر «ناوگان: ...» صریح داریم، فقط همان‌ها را بگیر
            $explicitFleets = $this->extractExplicitFleetTitles($segment, $fleetPattern, $fleetLexicon);
            if (!empty($explicitFleets)) {
                $segmentFleets = $explicitFleets;
            }

            // مبدأ زمینه‌ای سگمنت
            $contextOrigin =
                $this->getContextOrigin($segment, $cityPattern, $cityLexicon)
                ?: $globalContextOrigin;

            // مبدا/مقصد داخل سگمنت
            $parsed = $this->parseOriginsAndDestinations($segment, $cityPattern, $cityLexicon, $contextOrigin);
            $origins      = $parsed['origins'];
            $destinations = $parsed['destinations'];

            // اگر هنوز خالی است، از «مبدأ صریح» سراسری استفاده کن
            if (empty($origins) && $globalExplicitOrigin) {
                $origins[] = $globalExplicitOrigin;
            }

            // «بهِ آویزان»
            $titleFromDangling = [];
            if ($this->hasDanglingTo($segment)) {
                $beOrigin = $this->originBeforeDanglingTo($segment, $cityPattern, $cityLexicon);
                if ($beOrigin && empty($origins)) $origins[] = $beOrigin;

                $destFromDangling = $this->collectCitiesAfterDanglingTo($segment, $cityPattern, $cityLexicon, $titleFromDangling);
                if (!empty($destFromDangling) && empty($destinations)) $destinations = $destFromDangling;
            }

            // مقصدهای سراسری اگر قبل از ناوگان آمده باشد
            if (empty($destinations) && !empty($globalDestinations)) {
                $destinations = $this->filterParentCities($globalDestinations);
            }

            // اگر مبدا داریم ولی مقصد خالی است → از کل متن مقصدها را استخراج کن
            if (!empty($origins) && empty($destinations)) {
                $parsedAll = $this->parseOriginsAndDestinations($text, $cityPattern, $cityLexicon, $globalContextOrigin);
                if (!empty($parsedAll['destinations'])) {
                    $destinations = $parsedAll['destinations'];
                }
            }

            // عنوان
            $title = $this->extractTitle($segment) ?: $this->extractTitle($text);
            if (!empty($titleFromDangling)) {
                $title = $title ? ($title . '، ' . implode('، ', array_unique($titleFromDangling)))
                    : implode('، ', array_unique($titleFromDangling));
            }
            $title = $this->dedupeAndAliasTitle($title);

            // fallback: اگر سگمنت هیچ شهری ندارد، از کل متن بخوان
            if (empty($origins) && empty($destinations)) {
                $segHasCity = !empty($this->collectCitiesOrdered($segment, $cityPattern, $cityLexicon));
                if (!$segHasCity) {
                    $parsedAll = $this->parseOriginsAndDestinations($text, $cityPattern, $cityLexicon, $globalContextOrigin);
                    $origins = $parsedAll['origins'];
                    $destinations = $parsedAll['destinations'];
                    if (empty($origins) && $globalExplicitOrigin) $origins[] = $globalExplicitOrigin;
                    if (empty($destinations) && !empty($globalDestinations)) $destinations = $this->filterParentCities($globalDestinations);
                    if (empty($origins) && !empty($firstPhone) && preg_match('/^0912/', $firstPhone)) $origins[] = 'تهران';
                }
            }

            // اگر مقصد داریم و مبدا خالی → 0912 ⇒ تهران
            if (empty($origins) && !empty($destinations) && !empty($firstPhone) && preg_match('/^0912/', $firstPhone)) {
                $origins[] = 'تهران';
            }

            // تزریق مسیر پیش‌متن
            if ($prefaceRoute) {
                if (empty($origins) && empty($destinations)) {
                    $origins[]      = $prefaceRoute['origin'];
                    $destinations[] = $prefaceRoute['destination'];
                } elseif (empty($origins) && !empty($destinations)) {
                    $origins[] = $prefaceRoute['origin'];
                } elseif (!empty($origins) && empty($destinations)) {
                    $destinations[] = $prefaceRoute['destination'];
                }
            }

            // تزریق مسیر سراسری دوشهری
            if (!$prefaceRoute && $globalTwoCityRoute) {
                if (empty($origins) && empty($destinations)) {
                    $origins[]      = $globalTwoCityRoute['origin'];
                    $destinations[] = $globalTwoCityRoute['destination'];
                } elseif (empty($origins) && !empty($destinations)) {
                    $origins[] = $globalTwoCityRoute['origin'];
                } elseif (!empty($origins) && empty($destinations)) {
                    $destinations[] = $globalTwoCityRoute['destination'];
                }
            }

            // ✂️ حذف parentهای احتمالی بعد از تمام جمع‌آوری‌ها
            $origins      = $this->filterParentCities($origins);
            $destinations = $this->filterParentCities($destinations);

            if (empty($origins) && empty($destinations) && empty($segmentFleets)) {
                continue;
            }

            $usedFleetList    = $segmentFleets ?: [null];
            $usedOrigins      = $origins ?: [null];
            $usedDestinations = $destinations ?: [null];

            foreach ($usedFleetList as $fleetTitle) {
                foreach ($usedOrigins as $originCity) {
                    foreach ($usedDestinations as $destCity) {
                        $record = [
                            'fleet'           => $fleetTitle,
                            'fleet_id'        => $fleetTitle ? ($fleetsByTitle[$fleetTitle] ?? null) : null,
                            'origin'          => $originCity,
                            'origin_id'       => $this->pickBestCityIdByName($originCity),
                            'destination'     => $destCity,
                            'destination_id'  => $this->pickBestCityIdByName($destCity),
                            'price'           => $price,
                            'title'           => $title,
                            'phoneNumber'     => $firstPhone ?? '',
                            'description'     => $this->makeDescription($fleetTitle, $originCity, $destCity, $title, $price, $raw),
                            'raw'             => $raw,
                        ];
                        $this->pushUniqueLoad($allLoads, $record);
                    }
                }
            }
        }

        // fallback شهر→شهر
        if (empty($allLoads)) {
            $simple = $this->simpleCityToCity($text, $cityPattern, $cityLexicon);
            if ($simple) {
                [$originCity, $destCity] = $simple;

                $fleetTitles = [null];
                if (preg_match("/($fleetPattern)/u", $text, $f)) {
                    // در fallback هم اگر تطابق دقیقا برابر یک عنوان باشد، فقط همان را بگیر
                    $fleetTitles = $this->toCanonicalFleetsStrict($f[1], $fleetLexicon) ?: [null];
                }

                $title = $this->extractTitle($text);

                foreach ($fleetTitles as $fleetTitle) {
                    $this->pushUniqueLoad($allLoads, [
                        'fleet'           => $fleetTitle,
                        'fleet_id'        => $fleetTitle ? ($fleetsByTitle[$fleetTitle] ?? null) : null,
                        'origin'          => $originCity,
                        'origin_id'       => $this->pickBestCityIdByName($originCity),
                        'destination'     => $destCity,
                        'destination_id'  => $this->pickBestCityIdByName($destCity),
                        'price'           => $price,
                        'title'           => $title,
                        'phoneNumber'     => $firstPhone ?? '',
                        'description'     => $this->makeDescription($fleetTitle, $originCity, $destCity, $title, $price, $raw),
                        'raw'             => $raw,
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'data'    => array_values($allLoads),
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function dataConvert($cargo, $isAutomatic = 0, $cargoId = null)
    {
        if ($isAutomatic == 1) {
            $raw = $cargo;
        } else {
            $raw = $cargo->cargo;
        }

        if ($raw === '' || $raw == null) {
            return response()->json(['success' => false, 'message' => 'متن ورودی خالی است.']);
        }

        // 1) نرمال‌سازی
        $text = $this->normalizeText($raw);


        // 2) داده‌های پایه
        $citiesById = DB::table('province_cities')->where('parent_id', '!=', 0)->pluck('name', 'id')->toArray(); // id => name
        $fleetsById = DB::table('fleets')->where('parent_id', '!=', 0)->pluck('title', 'id')->toArray();        // id => title

        // 📌 نقشه parent برای تشخیص سلسله‌مراتب شهر/شهرستان/استان
        $this->cityParentsMap = DB::table('province_cities')->pluck('parent_id', 'id')->toArray(); // id => parent_id

        // ایندکس تک‌ارزشی + چند‌ارزشی
        $this->citiesByNameIndex = [];
        $this->citiesByNameMulti = [];
        foreach ($citiesById as $id => $name) {
            $this->citiesByNameIndex[$name] = $id;
            $this->citiesByNameMulti[$name][] = $id;
        }
        // 3) معادل‌ها (با پشتیبانی از چند ناوگان برای یک کلمه)
        [$cityLexicon, $fleetLexicon] = $this->buildLexicons($citiesById, $fleetsById);

        // 4) الگوها
        $cityTokens  = array_keys($cityLexicon);
        usort($cityTokens, fn($a, $b) => mb_strlen($b) - mb_strlen($a));
        $cityPattern = $cityTokens ? implode('|', array_map('preg_quote', $cityTokens)) : '([آ-ی\s\-]+)';

        $fleetTokens  = array_keys($fleetLexicon);
        usort($fleetTokens, fn($a, $b) => mb_strlen($b) - mb_strlen($a));
        $fleetPattern = $fleetTokens ? implode('|', array_map('preg_quote', $fleetTokens)) : '([آ-ی\s\-]+)';

        // 5) تلفن و حذف از متن
        $firstPhone = $this->extractFirstPhone($text);
        // return dd($firstPhone);
        if (!empty($firstPhone)) {
            $digits = preg_replace('/\D+/u', '', $firstPhone);
            $variants = [$digits];
            if (strlen($digits) === 11 && substr($digits, 0, 2) === '09') {
                $variants[] = substr($digits, 1); // 9xxxxxxxxx
            }
            foreach ($variants as $v) {
                if ($v !== '') $text = preg_replace('/' . preg_quote($v, '/') . '/u', ' ', $text);
            }
            $text = $this->squashLines($text);
        }

        // 5.1) پیش‌پردازش زوج‌های «شهر-مرکز استان/استان» در خطوط بدون کلیدواژه
        $text = $this->preprocessRegionalPairs($text, $cityPattern, $cityLexicon);

        // 6) قیمت
        $price = $this->extractPrice($text);

        // 7) سگمنت‌ها بر پایه ناوگان
        $segments = $this->splitByFleets($text, $fleetPattern);

        // 8) کانتکست/مسیرهای سراسری
        $globalExplicitOrigin = $this->extractExplicitOriginFirstCity($text, $cityPattern, $cityLexicon);

        // منبع مبدأ از «تعاونی/اتحادیه ... {شهر}»
        $carrierUnionOrigin   = $this->extractCarrierUnionOrigin($text, $fleetPattern, $cityPattern, $cityLexicon);

        $globalContextOrigin  = $globalExplicitOrigin
            ?: $this->getContextOrigin($text, $cityPattern, $cityLexicon)
            ?: $carrierUnionOrigin
            ?: $this->getPrefaceContextOrigin($text, $fleetPattern, $cityPattern, $cityLexicon);

        $prefaceRoute       = $this->extractPrefaceRoute($text, $fleetPattern, $cityPattern, $cityLexicon);
        $globalTwoCityRoute = $this->extractGlobalTwoCityRoute($text, $cityPattern, $cityLexicon);
        $globalDestinations = $this->extractGlobalDestinations($text, $cityPattern, $cityLexicon);

        // نگاشت معکوس ناوگان برای id (عنوان → id)
        $fleetsByTitle = array_flip($fleetsById);

        $allLoads = [];
        foreach ($segments as $segment) {
            // ناوگان‌های سگمنت (ممکن است چندتا باشد)
            $segmentFleets = $this->findFleetsInSegment($segment, $fleetPattern, $fleetLexicon, $this->cargoWords);

            // 🔒 اگر «ناوگان: ...» صریح داریم، فقط همان‌ها را بگیر
            $explicitFleets = $this->extractExplicitFleetTitles($segment, $fleetPattern, $fleetLexicon);
            if (!empty($explicitFleets)) {
                $segmentFleets = $explicitFleets;
            }

            // مبدأ زمینه‌ای سگمنت
            $contextOrigin =
                $this->getContextOrigin($segment, $cityPattern, $cityLexicon)
                ?: $globalContextOrigin;

            // مبدا/مقصد داخل سگمنت
            $parsed = $this->parseOriginsAndDestinations($segment, $cityPattern, $cityLexicon, $contextOrigin);
            $origins      = $parsed['origins'];
            $destinations = $parsed['destinations'];

            // اگر هنوز خالی است، از «مبدأ صریح» سراسری استفاده کن
            if (empty($origins) && $globalExplicitOrigin) {
                $origins[] = $globalExplicitOrigin;
            }

            // «بهِ آویزان»
            $titleFromDangling = [];
            if ($this->hasDanglingTo($segment)) {
                $beOrigin = $this->originBeforeDanglingTo($segment, $cityPattern, $cityLexicon);
                if ($beOrigin && empty($origins)) $origins[] = $beOrigin;

                $destFromDangling = $this->collectCitiesAfterDanglingTo($segment, $cityPattern, $cityLexicon, $titleFromDangling);
                if (!empty($destFromDangling) && empty($destinations)) $destinations = $destFromDangling;
            }

            // مقصدهای سراسری اگر قبل از ناوگان آمده باشد
            if (empty($destinations) && !empty($globalDestinations)) {
                $destinations = $this->filterParentCities($globalDestinations);
            }

            // اگر مبدا داریم ولی مقصد خالی است → از کل متن مقصدها را استخراج کن
            if (!empty($origins) && empty($destinations)) {
                $parsedAll = $this->parseOriginsAndDestinations($text, $cityPattern, $cityLexicon, $globalContextOrigin);
                if (!empty($parsedAll['destinations'])) {
                    $destinations = $parsedAll['destinations'];
                }
            }

            // عنوان
            $title = $this->extractTitle($segment) ?: $this->extractTitle($text);
            if (!empty($titleFromDangling)) {
                $title = $title ? ($title . '، ' . implode('، ', array_unique($titleFromDangling)))
                    : implode('، ', array_unique($titleFromDangling));
            }
            $title = $this->dedupeAndAliasTitle($title);

            // fallback: اگر سگمنت هیچ شهری ندارد، از کل متن بخوان
            if (empty($origins) && empty($destinations)) {
                $segHasCity = !empty($this->collectCitiesOrdered($segment, $cityPattern, $cityLexicon));
                if (!$segHasCity) {
                    $parsedAll = $this->parseOriginsAndDestinations($text, $cityPattern, $cityLexicon, $globalContextOrigin);
                    $origins = $parsedAll['origins'];
                    $destinations = $parsedAll['destinations'];
                    if (empty($origins) && $globalExplicitOrigin) $origins[] = $globalExplicitOrigin;
                    if (empty($destinations) && !empty($globalDestinations)) $destinations = $this->filterParentCities($globalDestinations);
                    if (empty($origins) && !empty($firstPhone) && preg_match('/^0912/', $firstPhone)) $origins[] = 'تهران';
                }
            }

            // اگر مقصد داریم و مبدا خالی → 0912 ⇒ تهران
            if (empty($origins) && !empty($destinations) && !empty($firstPhone) && preg_match('/^0912/', $firstPhone)) {
                $origins[] = 'تهران';
            }

            // تزریق مسیر پیش‌متن
            if ($prefaceRoute) {
                if (empty($origins) && empty($destinations)) {
                    $origins[]      = $prefaceRoute['origin'];
                    $destinations[] = $prefaceRoute['destination'];
                } elseif (empty($origins) && !empty($destinations)) {
                    $origins[] = $prefaceRoute['origin'];
                } elseif (!empty($origins) && empty($destinations)) {
                    $destinations[] = $prefaceRoute['destination'];
                }
            }

            // تزریق مسیر سراسری دوشهری
            if (!$prefaceRoute && $globalTwoCityRoute) {
                if (empty($origins) && empty($destinations)) {
                    $origins[]      = $globalTwoCityRoute['origin'];
                    $destinations[] = $globalTwoCityRoute['destination'];
                } elseif (empty($origins) && !empty($destinations)) {
                    $origins[] = $globalTwoCityRoute['origin'];
                } elseif (!empty($origins) && empty($destinations)) {
                    $destinations[] = $globalTwoCityRoute['destination'];
                }
            }

            // ✂️ حذف parentهای احتمالی بعد از تمام جمع‌آوری‌ها
            $origins      = $this->filterParentCities($origins);
            $destinations = $this->filterParentCities($destinations);

            if (empty($origins) && empty($destinations) && empty($segmentFleets)) {
                continue;
            }

            $usedFleetList    = $segmentFleets ?: [null];
            $usedOrigins      = $origins ?: [null];
            $usedDestinations = $destinations ?: [null];

            foreach ($usedFleetList as $fleetTitle) {
                foreach ($usedOrigins as $originCity) {
                    foreach ($usedDestinations as $destCity) {
                        $origins = ProvinceCity::where('name', $originCity)
                            ->where('parent_id', '!=', 0)
                            ->get(['id', 'name', 'parent_id']);
                        $destinations = ProvinceCity::where('name', $destCity)
                            ->where('parent_id', '!=', 0)
                            ->get(['id', 'name', 'parent_id']);

                        // if ($isAutomatic == 0 && $cargo->isProcessingControl == 1) {
                        if (
                            preg_match('/عنوان بار:\s*(.*?)(?:\s*\d{10,}|$)/u', $raw, $matches) ||
                            preg_match('/عنوان بار:\s*(.*?)\s*(?:Tell:|$)/u', $raw, $matches)
                        ) {
                            $titleProccesing = trim($matches[1]);
                        }
                        // }

                        $record = [
                            'fleet'           => $fleetTitle,
                            'fleet_id'        => $fleetTitle ? ($fleetsByTitle[$fleetTitle] ?? null) : null,
                            'origin'          => $originCity,
                            'origin_id'       => $this->pickBestCityIdByName($originCity),
                            'origins'          => $origins,
                            'destinations'          => $destinations,

                            'destination'     => $destCity,
                            'destination_id'  => $this->pickBestCityIdByName($destCity),
                            'price'           => $price,
                            'title'           => $titleProccesing ?? $title,
                            'phoneNumber'     => $firstPhone ?? '',
                            'description'     => $this->makeDescription($fleetTitle, $originCity, $destCity, $title, $price, $raw),
                            'raw'             => $raw,
                        ];
                        $this->pushUniqueLoad($allLoads, $record);
                    }
                }
            }
        }

        // fallback شهر→شهر
        if (empty($allLoads)) {
            $simple = $this->simpleCityToCity($text, $cityPattern, $cityLexicon);
            if ($simple) {
                [$originCity, $destCity] = $simple;

                $fleetTitles = [null];
                if (preg_match("/($fleetPattern)/u", $text, $f)) {
                    // در fallback هم اگر تطابق دقیقا برابر یک عنوان باشد، فقط همان را بگیر
                    $fleetTitles = $this->toCanonicalFleetsStrict($f[1], $fleetLexicon) ?: [null];
                }

                $title = $this->extractTitle($text);
                // if ($isAutomatic == 0 && $cargo->isProcessingControl == 1) {
                if (
                    preg_match('/عنوان بار:\s*(.*?)(?:\s*\d{10,}|$)/u', $raw, $matches) ||
                    preg_match('/عنوان بار:\s*(.*?)\s*(?:Tell:|$)/u', $raw, $matches)
                ) {
                    $titleProccesing = trim($matches[1]);
                }
                // }
                foreach ($fleetTitles as $fleetTitle) {
                    $this->pushUniqueLoad($allLoads, [
                        'fleet'           => $fleetTitle,
                        'fleet_id'        => $fleetTitle ? ($fleetsByTitle[$fleetTitle] ?? null) : null,
                        'origin'          => $originCity,
                        'origin_id'       => $this->pickBestCityIdByName($originCity),
                        'destination'     => $destCity,
                        'destination_id'  => $this->pickBestCityIdByName($destCity),
                        'price'           => $price,
                        'title'           => $titleProccesing ?? $title,
                        'phoneNumber'     => $firstPhone ?? '',
                        'description'     => $this->makeDescription($fleetTitle, $originCity, $destCity, $title, $price, $raw),
                        'raw'             => $raw,
                    ]);
                }
            }
        }

        $uniqueResults = array_values($allLoads);
        $countOfCargos = CargoConvertList::where('operator_id', 0)
            ->where('isBlocked', 0)
            ->where('processingUnit', 0)
            ->where('isDuplicate', 0)
            ->where('status', 0)
            ->count();
        $users = UserController::getOnlineAndOfflineUsers();
        if ($isAutomatic == 1) {
            try {
                foreach ($uniqueResults as $index => $item) {
                    $result['key'][] = (string) $index;

                    $result["title_{$index}"] = trim($item['title']);
                    $result["origin_{$index}"] = $item['origin'];
                    $result["originState_{$index}"] = $item['origins'][0]['parent_id'];
                    $result["destination_{$index}"] = $item['destination'];
                    $result["destinationState_{$index}"] = $item['destinations'][0]['parent_id'];
                    $result["mobileNumber_{$index}"] = $item['phoneNumber'];
                    $result["freight_{$index}"] = $item['price'];
                    $result["priceType_{$index}"] = "توافقی";
                    $result["fleetId_{$index}"] = (string) $item['fleet_id'];
                    $result["fleets_{$index}"] = $item['fleet'];
                    $result["description_{$index}"] = $item['description'];
                }
                $request = new Request($result);
                return $this->storeMultiCargoSmartAuto($request, $cargoId);
            } catch (\Exception $e) {
                // $cargo = CargoConvertList::find($cargoId);
                // $cargo->status = 1;
                // $cargo->rejected = 1;
                // $cargo->processingUnit = 0;
                // $cargo->save();
                // return back();
                //throw $th;
            }
        }
        return view('admin.load.smartCreateCargo', compact('cargo', 'countOfCargos', 'users', 'uniqueResults'));

        // return response()->json($uniqueResults);
    }

    public static function getCountOfCargos()
    {
        return CargoConvertList::where('operator_id', 0)
            ->where('isBlocked', 0)
            ->where('processingUnit', 0)
            ->where('isDuplicate', 0)
            ->where('status', 0)
            ->count();
    }

    public static function getCountOfCargoProcessingUnits()
    {
        return CargoConvertList::where('processingUnit', 1)
            ->where('status', 0)
            ->where('operator_id', 0)
            ->count();
    }

    // ذخیره دسته ای بارها
    public function storeMultiCargoSmart(Request $request, $cargoId)
    {
        $cargo = CargoConvertList::whereId($cargoId)->first();
        if ($cargo === null) {
            return back()->with('error', 'صفر بار ثبت شد');
        }
        try {
            $expiresAt = now()->addMinutes(3);
            $userId = Auth::id();

            Cache::put("user-is-active-$userId", true, $expiresAt);
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
        // return $request;
        $counter = 0;
        foreach ($request->key as $key) {
            $origin = "origin_" . $key;
            $originState = "originState_" . $key;
            $destination = "destination_" . $key;
            $destinationState = "destinationState_" . $key;
            $mobileNumber = "mobileNumber_" . $key;
            $description = "description_" . $key;
            $fleet = "fleets_" . $key;
            $title = "title_" . $key;
            // $freight = "freight_" . $key;
            // $priceType = "priceType_" . $key;
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
                    // $request->$freight,
                    // $request->$priceType,

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
        $cargo->final_submission_at = now();
        $cargo->save();
        return back()->with('success', $counter . 'بار ثبت شد');
    }
    public function storeMultiCargoSmartAuto(Request $request, $cargoId)
    {
        $cargo = CargoConvertList::whereId($cargoId)->first();
        if ($cargo === null) {
            return back()->with('error', 'صفر بار ثبت شد');
        }
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
        // return $request;
        $counter = 0;
        foreach ($request->key as $key) {
            $origin = "origin_" . $key;
            $originState = "originState_" . $key;
            $destination = "destination_" . $key;
            $destinationState = "destinationState_" . $key;
            $mobileNumber = "mobileNumber_" . $key;
            $description = "description_" . $key;
            $fleet = "fleets_" . $key;
            $title = "title_" . $key;
            // $freight = "freight_" . $key;
            // $priceType = "priceType_" . $key;
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
                    // $request->$freight,
                    // $request->$priceType,

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
    }

    public function storeCargoSmart($origin, $originState, $destination, $destinationState, $mobileNumber, $description, $fleet, $title, &$counter, $cargoId)
    {

        if (!strlen(trim($origin)) || $origin == null || $origin == 'null' || !strlen(trim($destination)) || $destination == null || $destination == 'null' || !strlen($fleet) || !strlen($mobileNumber))
            return;


        $cargoPattern = '';

        try {
            $cargoPattern = $origin . $destination . $mobileNumber . $fleet;

            if (
                BlockPhoneNumber::where('phoneNumber', $mobileNumber)
                    ->where(function ($query) {
                        $query->where('type', 'operator')
                            ->orWhere('type', 'both');
                    })->exists()
            ) {
                return;
            }
            Load::where('cargoPattern', $cargoPattern)
                ->where('created_at', '>', now()->subMinutes(180))
                ->delete();
            // $loadDpl = Load::where('cargoPattern', $cargoPattern)->where('created_at', '>', now()->subMinutes(180))->first();
            // if ($loadDpl) {
            //     $loadDpl->delete();
            //     // $loadDpl->created_at = now();
            //     // $loadDpl->updated_at = now();
            //     // $loadDpl->loadingDate = gregorianDateToPersian(date('Y-m-d', time()), '-');
            //     // $loadDpl->time = time();
            //     // $loadDpl->date = gregorianDateToPersian(date('Y/m/d', time()), '/');
            //     // $loadDpl->dateTime = now()->format('H:i:s');
            //     // $loadDpl->save();
            //     // return;
            // }
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
            ];
            $loadDuplicate = Load::where($conditions)
                ->whereHas('fleetLoads', function ($q) use ($fleet_id) {
                    $q->where('fleet_id', $fleet_id->id);
                })
                ->where('operator_id', '>', 0)
                ->first();

            $loadDuplicateOwnerBot = Load::where($conditions)
                ->whereHas('fleetLoads', function ($q) use ($fleet_id) {
                    $q->where('fleet_id', $fleet_id->id);
                })
                ->where('userType', 'owner')
                // ->where('isBot', 1)
                ->first();
            if ($loadDuplicate || $loadDuplicateOwnerBot) {
                collect([$loadDuplicate, $loadDuplicateOwnerBot])
                    ->filter()
                    ->each(fn($duplicate) => $duplicate->delete());
            }
            $load->save();

            if (isset($load->id)) {
                $counter++;

                if (isset($fleet_id->id)) {
                    $fleetLoad = new FleetLoad();
                    $fleetLoad->load_id = $load->id;
                    $fleetLoad->fleet_id = $fleet_id->id;
                    $fleetLoad->numOfFleets = 1;
                    $fleetLoad->userType = $load->userType;
                    $fleetLoad->save();

                    $persian_date = gregorianDateToPersian(date('Y/m/d', time()), '/');
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

                try {

                    $load->fleets = FleetLoad::join('fleets', 'fleets.id', 'fleet_loads.fleet_id')
                        ->where('fleet_loads.load_id', $load->id)
                        ->select('fleet_id', 'userType', 'suggestedPrice', 'numOfFleets', 'pic', 'title')
                        ->get();

                    $load->save();

                    // }
                } catch (\Exception $exception) {
                    Log::emergency("---------------------------------------------------------");
                    Log::emergency($exception->getMessage());
                    Log::emergency("---------------------------------------------------------");
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

                try {
                    if ($load->operator_id > 0) {
                        $toDay = gregorianDateToPersian(date('Y/m/d'), '/');
                        $isFirstLoad = DB::table('load_owner_counts as loc1')
                            ->select('loc1.mobileNumber')
                            ->where('loc1.mobileNumber', $mobileNumber)
                            ->where('loc1.persian_date', $toDay)
                            ->whereNotExists(function ($query) use ($toDay, $mobileNumber) {
                                $query->select(DB::raw(1))
                                    ->from('load_owner_counts as loc2')
                                    ->whereColumn('loc2.mobileNumber', 'loc1.mobileNumber')
                                    ->where('loc2.mobileNumber', $mobileNumber)
                                    ->where('loc2.persian_date', '<>', $toDay); // فقط روزهای دیگر
                            })
                            ->first();
                        if ($isFirstLoad) {
                            $load->title = "⚠ توجه: در صورت درخواست کمیسیون، با پشتیبانی ایران ترابر هماهنگ باشید.";
                            $load->save();

                            // $checkLoadDeleted = Load::onlyTrashed()
                            //     ->where('mobileNumberForCoordination', $mobileNumber)
                            //     ->first();

                            // if (Carbon::parse($checkLoadDeleted->deleted_at)->diffInHours(now()) < 6) {
                            //     $load->forceDelete(); // حذف کامل
                            // }
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning($e->getMessage());
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

    // ---------------------- Lexicon (equivalents) ----------------------

    /**
     * cityLexicon: token => ['id'=>, 'name'=>]
     * fleetLexicon: token => list of ['id'=>, 'title'=>]  (چندناوگانی)
     */
    private function buildLexicons(array $citiesById, array $fleetsById): array
    {
        $cityLexicon  = [];
        $fleetLexicon = [];

        // شهرها (رسمی)
        foreach ($citiesById as $id => $name) {
            $cityLexicon[$this->normalizeLexeme($name)] = ['id' => $id, 'name' => $name];
        }
        // معادل شهر
        $equivCities = DB::table('equivalents')
            ->where('type', 'city')
            ->select('original_word_id', 'equivalentWord')
            ->get();
        foreach ($equivCities as $row) {
            $canonName = $citiesById[$row->original_word_id] ?? null;
            if (!$canonName) continue;
            $eq = $this->normalizeLexeme($row->equivalentWord);
            if ($eq !== '') $cityLexicon[$eq] = ['id' => $row->original_word_id, 'name' => $canonName];
        }

        // ناوگان‌ها (چندناوگانی)
        foreach ($fleetsById as $id => $title) {
            $token = $this->normalizeLexeme($title);
            if (!isset($fleetLexicon[$token])) $fleetLexicon[$token] = [];
            $fleetLexicon[$token][$title] = ['id' => $id, 'title' => $title];
        }
        $equivFleets = DB::table('equivalents')
            ->where('type', 'fleet')
            ->select('original_word_id', 'equivalentWord')
            ->get();
        foreach ($equivFleets as $row) {
            $canonTitle = $fleetsById[$row->original_word_id] ?? null;
            if (!$canonTitle) continue;
            $eq = $this->normalizeLexeme($row->equivalentWord);
            if ($eq === '') continue;
            if (!isset($fleetLexicon[$eq])) $fleetLexicon[$eq] = [];
            $fleetLexicon[$eq][$canonTitle] = ['id' => $row->original_word_id, 'title' => $canonTitle];
        }
        // collapse به آرایهٔ عددی
        foreach ($fleetLexicon as $k => $byTitle) {
            $fleetLexicon[$k] = array_values($byTitle);
        }

        return [$cityLexicon, $fleetLexicon];
    }

    private function normalizeLexeme(string $s): string
    {
        $map = [
            'ي' => 'ی',
            'ك' => 'ک',
            'ۀ' => 'ه',
            "\x{200c}" => ' ',
            '٠' => '0',
            '١' => '1',
            '٢' => '2',
            '٣' => '3',
            '٤' => '4',
            '٥' => '5',
            '٦' => '6',
            '٧' => '7',
            '٨' => '8',
            '٩' => '9',
            '۰' => '0',
            '۱' => '1',
            '۲' => '2',
            '۳' => '3',
            '۴' => '4',
            '۵' => '5',
            '۶' => '6',
            '۷' => '7',
            '۸' => '8',
            '۹' => '9'
        ];
        $s = strtr($s, $map);
        $s = preg_replace('/[^\PC\s]/u', ' ', $s);
        $s = preg_replace('/(?<=\d)(?=\p{L})|(?<=\p{L})(?=\d)/u', ' ', $s);
        return preg_replace('/[ \t\x{00A0}]+/u', ' ', trim($s));
    }

    private function toCanonicalCity(string $matched, array $cityLexicon): ?string
    {
        $norm = $this->normalizeLexeme($matched);
        return $cityLexicon[$norm]['name'] ?? null;
    }

    /** تبدیل یک کلید ناوگان به فهرست ناوگان‌های هم‌معنا (چندگانه) - حالت پیش‌فرض (ممکن است چندتا برگرداند) */
    private function toCanonicalFleets(?string $matched, array $fleetLexicon): array
    {
        if ($matched === null) return [];
        $norm = $this->normalizeLexeme($matched);
        if (isset($fleetLexicon[$norm])) {
            $out = [];
            foreach ($fleetLexicon[$norm] as $row) {
                if (!in_array($row['title'], $out, true)) $out[] = $row['title'];
            }
            return $out;
        }
        return [$matched];
    }

    /** تبدیل ناوگان با سخت‌گیری: اگر متن دقیقاً برابر یک عنوان رسمی باشد، فقط همان را برگردان */
    private function toCanonicalFleetsStrict(?string $matched, array $fleetLexicon): array
    {
        if ($matched === null) return [];
        $norm = $this->normalizeLexeme($matched);
        if (!isset($fleetLexicon[$norm])) return [$matched];

        // اگر یکی از عناوین رسمی دقیقاً همین توکن است، فقط همان را برگردان
        foreach ($fleetLexicon[$norm] as $row) {
            if ($this->normalizeLexeme($row['title']) === $norm) {
                return [$row['title']];
            }
        }
        // وگرنه مثل قبل عمل کن
        return $this->toCanonicalFleets($matched, $fleetLexicon);
    }

    // ---------------------- Helpers ----------------------

    private function normalizeText(string $text): string
    {
        $map = [
            'ي' => 'ی',
            'ك' => 'ک',
            'ۀ' => 'ه',
            "\x{200c}" => ' ',
            '٠' => '0',
            '١' => '1',
            '٢' => '2',
            '٣' => '3',
            '٤' => '4',
            '٥' => '5',
            '٦' => '6',
            '٧' => '7',
            '٨' => '8',
            '٩' => '9',
            '۰' => '0',
            '۱' => '1',
            '۲' => '2',
            '۳' => '3',
            '۴' => '4',
            '۵' => '5',
            '۶' => '6',
            '۷' => '7',
            '۸' => '8',
            '۹' => '9'
        ];
        $text = strtr($text, $map);
        // حذف URL و ایموجی و علائم غیرمتنی
        $text = preg_replace('~https?://\S+~u', ' ', $text);
        $text = preg_replace('/[^\PC\s]/u', ' ', $text);
        $text = preg_replace('/:[a-z0-9_]+:/iu', ' ', $text);
        $text = preg_replace('/(?<=\d)(?=\p{L})|(?<=\p{L})(?=\d)/u', ' ', $text);
        return $this->squashLines($text);
    }

    private function squashLines(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $lines = explode("\n", $text);
        foreach ($lines as &$line) $line = preg_replace('/[ \t\x{00A0}]+/u', ' ', trim($line));
        unset($line);
        $text = implode("\n", $lines);
        $text = preg_replace("/\n{2,}/u", "\n", $text);
        return trim($text);
    }

    /**
     * پیش‌پردازش زوج‌های «شهر-استان/مرکز استان» در خطوط بدون کلیدواژه مسیر.
     * الگوهای A - B / A , B / A / B و A(B) → به A تقلیل می‌یابد.
     */
    private function preprocessRegionalPairs(string $text, string $cityPattern, array $cityLexicon): string
    {
        $lines = preg_split("/\n/u", $text);
        $out = [];

        foreach ($lines as $line) {
            $t = trim($line);
            if ($t === '') {
                $out[] = $t;
                continue;
            }

            $hasKW = preg_match('/\b(?:مبدا|مبدأ|بارگیری|از|به|تا|ب|تخلیه|مقصد|مقصدها)\b/u', $t);

            if (!$hasKW) {
                // A - B  /  A / B  /  A , B
                $rePair = '/\b([\p{L}\s]+?)\b\s*(?:[-–—\/,]\s*)\b([\p{L}\s]+?)\b/u';

                $t = preg_replace_callback($rePair, function ($m) use ($cityLexicon) {
                    $a = $this->toCanonicalCity(trim($m[1]), $cityLexicon);
                    $b = $this->toCanonicalCity(trim($m[2]), $cityLexicon);
                    // Keep one city if both are valid
                    if ($a && $b) {
                        return $a;
                    }
                    return $m[0];
                }, $t);

                // A(B) → A   — safer version (no massive regex)
                $reParen = '/\b([\p{L}\s]+?)\s*\(\s*([\p{L}\s]+?)\s*\)/u';
                $t = preg_replace_callback($reParen, function ($m) use ($cityLexicon) {
                    $a = $this->toCanonicalCity(trim($m[1]), $cityLexicon);
                    $b = $this->toCanonicalCity(trim($m[2]), $cityLexicon);
                    // Keep A only if both are recognized cities
                    if ($a && $b) {
                        return $a;
                    }
                    return $m[0];
                }, $t);
            }


            $out[] = $t;
        }

        return implode("\n", $out);
    }

    /** اولین شهر (با حذف parentهای هم‌خطی) بعد از {مبدا|مبدأ|بارگیری|از} در کل متن */
    private function extractExplicitOriginFirstCity(string $text, string $cityPattern, array $cityLexicon): ?string
    {
        if (preg_match_all('/\b(?:مبدا|مبدأ|بارگیری|از)\b[^\n]*/u', $text, $matches)) {
            foreach ($matches[0] as $line) {
                $tail = $line;
                if (preg_match('/:(.*)$/u', $tail, $cm)) $tail = $cm[1];

                // ✅ به‌جای فقط داخل پرانتز، کل payload بررسی می‌شود
                $cities = $this->collectCitiesInText($tail, $cityPattern, $cityLexicon);

                $cities = $this->filterParentCities($cities);
                if (!empty($cities)) return $cities[0];
            }
        }
        return null;
    }

    private function extractFirstPhone(string $text): ?string
    {
        $text = $this->normalizeDigits($text);

        // +98 یا 0098 → خروجی نرمال با 09 در ابتدا
        if (preg_match('/(?<!\d)(?:\+?98|0098)(?:[\s\-]?\d){10}(?!\d)/u', $text, $m98)) {
            $digits = preg_replace('/\D+/u', '', $m98[0]); // 98 9xxxxxxxxx
            $rest   = substr($digits, 2);
            if (strlen($rest) >= 10 && $rest[0] === '9') {
                return '0' . substr($rest, 0, 10);
            }
        }

        // موبایل ایران با یا بدون صفر ابتدایى (مرزبندی دقیق)
        if (preg_match('/(?<!\d)0?9(?:[\s\-]?\d){9}(?!\d)/u', $text, $m)) {
            $digits = preg_replace('/\D+/u', '', $m[0]); // 9xxxxxxxxx یا 09xxxxxxxxx
            if (strlen($digits) === 10 && $digits[0] === '9') return '0' . $digits;
            if (strlen($digits) === 11 && substr($digits, 0, 2) === '09') return $digits;
        }

        // تلفن ثابت ایران (که با 09 شروع نمی‌شود)
        if (preg_match('/(?<!\d)0(?!9)(?:[\s\-]?\d){9,11}(?!\d)/u', $text, $m2)) {
            $digits = preg_replace('/\D+/u', '', $m2[0]);
            if (strlen($digits) >= 10 && strlen($digits) <= 12) return $digits;
        }

        // بک‌آپ‌ها با مرزبندی
        if (preg_match('/(?<!\d)0\d{9,11}(?!\d)/u', $text, $m3)) return $m3[0];
        if (preg_match('/(?<!\d)9\d{9}(?!\d)/u', $text, $m4))   return '0' . $m4[0];

        return null;
    }


    private function normalizeDigits(string $text): string
    {
        $fa = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $en = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        return str_replace($fa, $en, $text);
    }

    private function extractPrice(string $text): ?string
    {
        // استانداردسازی واحد
        $canon = function ($u) {
            $u = trim($u);
            if ($u === 'صاف')   return 'صافی';
            if ($u === 'تومن')  return 'تومان';
            if ($u === 'ملیون') return 'میلیون';
            return $u;
        };

        if (preg_match('/(\d+(?:[.,]\d+)?)\s*(میلیون|ملیون|هزار|تومان|تومن|صاف|صافی)/iu', $text, $m)) {
            return trim($m[1] . ' ' . $canon($m[2]));
        }
        if (preg_match('/\b(توافقی|توافق)\b/u', $text)) return 'توافقی';
        if (preg_match('/(\d+)\s*(صاف|صافی)/u', $text, $m2)) return trim($m2[1] . ' ' . $canon($m2[2]));
        return null;
    }

    private function splitByFleets(string $text, string $fleetPattern): array
    {
        if (trim($fleetPattern) === '') return [$text];

        preg_match_all(
            "/(?:$fleetPattern)(?:\s*(?:و|،|\/|or|>>)?\s*(?:$fleetPattern))*[\s\S]*?(?=(?:$fleetPattern)|$)/u",
            $text,
            $m,
            PREG_SET_ORDER
        );

        $segments = [];
        foreach ($m as $row) {
            $seg = trim($row[0]);
            if ($seg !== '') $segments[] = $seg;
        }
        return empty($segments) ? [$text] : $segments;
    }

    private function findFleetsInSegment(string $segment, string $fleetPattern, array $fleetLexicon, array $cargoWords): array
    {
        if (trim($fleetPattern) === '') return [];
        preg_match_all("/($fleetPattern)/u", $segment, $m);
        $found = $m[1] ?? [];
        $out = [];
        foreach ($found as $f) {
            // حالت معمول: ممکن است چند ناوگان برگردد (معادل‌ها)
            foreach ($this->toCanonicalFleets($f, $fleetLexicon) as $canon) {
                if ($canon && !$this->isCargoWord($canon, $cargoWords) && !in_array($canon, $out, true)) {
                    $out[] = $canon;
                }
            }
        }
        return $out;
    }

    /** استخراج ناوگان از «ناوگان: ...» با سخت‌گیری روی عنوانِ دقیق */
    private function extractExplicitFleetTitles(string $text, string $fleetPattern, array $fleetLexicon): array
    {
        $out = [];
        if (preg_match_all('/\b(?|نوع\s*ناوگان)\b[^\n]*[:：]\s*([^\n]+)/u', $text, $m, PREG_SET_ORDER)) {
            foreach ($m as $row) {
                $payload = trim($row[1]);
                if ($payload === '') continue;
                // سعی کن از خود payload، ناوگان‌ها را match کنی
                if (preg_match_all("/($fleetPattern)/u", $payload, $fm)) {
                    foreach ($fm[1] as $hit) {
                        foreach ($this->toCanonicalFleetsStrict($hit, $fleetLexicon) as $canon) {
                            if (!in_array($canon, $out, true)) $out[] = $canon;
                        }
                    }
                } else {
                    // اگر pattern پیدا نشد، همان رشته را سخت‌گیرانه نگاشت کن
                    foreach ($this->toCanonicalFleetsStrict($payload, $fleetLexicon) as $canon) {
                        if (!in_array($canon, $out, true)) $out[] = $canon;
                    }
                }
            }
        }
        return $out;
    }

    private function isCargoWord(string $token, array $cargoWords): bool
    {
        foreach ($cargoWords as $c) {
            if (mb_strtolower($token) === mb_strtolower($c)) return true;
        }
        return false;
    }

    // مبدأ زمینه‌ای از کلیدواژه‌ها (اولین شهر/شهرهای بعد از کلیدواژه، با حذف parentهای هم‌خطی)
    private function getContextOrigin(string $text, string $cityPattern, array $cityLexicon): ?string
    {
        if (preg_match('/\b(?:مبدا|مبدأ|بارگیری|از)\b(?P<tail>[^\n]*)/u', $text, $m)) {
            $tail = trim($m['tail'] ?? '');
            if ($tail !== '') {
                if (preg_match('/:(.*)$/u', $tail, $cm)) $tail = trim($cm[1]);

                $parts = preg_split('/\b(?:به|تا|ب|تخلیه|مقصد|مقصدها)\b/u', $tail, 2);
                $payload = trim($parts[0] ?? $tail);

                // ✅ کل payload (نه فقط داخل پرانتز)
                $cities = $this->collectCitiesInText($payload, $cityPattern, $cityLexicon);

                $cities = $this->filterParentCities($cities);
                if (!empty($cities)) return $cities[0];
            }
        }
        return null;
    }

    /**
     * مبدأ زمینه‌ای پیش‌متن (اگر قبل از اولین ناوگان فقط «یک شهر» باشد).
     */
    private function getPrefaceContextOrigin(string $text, string $fleetPattern, string $cityPattern, array $cityLexicon): ?string
    {
        if (!preg_match("/($fleetPattern)/u", $text, $fm, PREG_OFFSET_CAPTURE)) return null;
        $pos = $fm[0][1];
        $preface = trim(mb_substr($text, 0, $pos));
        if ($preface === '') return null;

        if (preg_match('/\b(?:مبدا|مبدأ|بارگیری|از|به|تا|ب|تخلیه|مقصد|مقصدها)\b/u', $preface)) return null;

        if (preg_match('/\(([^)]*)\)/u', $preface, $pm)) {
            $inside = $this->collectCitiesInText($pm[1] ?? '', $cityPattern, $cityLexicon);
            $inside = $this->filterParentCities($inside);
            if (count($inside) === 1) return $inside[0];
        }

        $cities = $this->collectCitiesOrdered($preface, $cityPattern, $cityLexicon);
        $cities = $this->filterParentCities($cities);
        if (count($cities) === 1) return $cities[0];

        return null;
    }

    /**
     * استخراج مبدأ از «تعاونی/اتحادیه/انجمن/شرکت ... کامیونداران/رانندگان/حمل‌ونقل {شهر}» در پیش‌متن.
     */
    private function extractCarrierUnionOrigin(string $text, string $fleetPattern, string $cityPattern, array $cityLexicon): ?string
    {
        // پیش‌متن
        $header = $text;
        if (preg_match("/($fleetPattern)/u", $text, $fm, PREG_OFFSET_CAPTURE)) {
            $header = trim(mb_substr($text, 0, $fm[0][1]));
        }
        if ($header === '') return null;

        $org = '(?:تعاونی|اتحادیه|انجمن|شرکت|کانون|پایانه|ترمینال|انبار|باربری|تعاونی\s*شماره\s*\d+)';
        $sect = '(?:\s*(?:حمل\s*و\s*نقل|کامیونداران|رانندگان|بار|باربری|حمل))?';
        $regex = '/\b' . $org . $sect . '\s+(?:ِ|)(' . $cityPattern . ')\b/u';

        if (preg_match($regex, $header, $m)) {
            $city = $this->toCanonicalCity($m[1], $cityLexicon) ?? trim($m[1]);
            return $city ?: null;
        }

        return null;
    }

    // اگر قبل از اولین ناوگان دقیقا دو شهر بود → مسیر پیش‌متن
    private function extractPrefaceRoute(string $text, string $fleetPattern, string $cityPattern, array $cityLexicon): ?array
    {
        if (!preg_match("/($fleetPattern)/u", $text, $fm, PREG_OFFSET_CAPTURE)) return null;
        $pos = $fm[0][1];
        $preface = trim(mb_substr($text, 0, $pos));
        if ($preface === '') return null;

        $cities = $this->collectCitiesOrdered($preface, $cityPattern, $cityLexicon);
        $cities = $this->filterParentCities($cities);
        if (count($cities) === 2) return ['origin' => $cities[0], 'destination' => $cities[1]];
        return null;
    }

    private function extractGlobalTwoCityRoute(string $text, string $cityPattern, array $cityLexicon): ?array
    {
        if (preg_match('/\b(?:مبدا|مبدأ|بارگیری|از)\b/u', $text)) return null;
        $cities = $this->collectCitiesOrdered($text, $cityPattern, $cityLexicon);
        $cities = array_values(array_unique($cities));
        $cities = $this->filterParentCities($cities);
        if (count($cities) === 2) return ['origin' => $cities[0], 'destination' => $cities[1]];
        return null;
    }

    private function collectCitiesOrdered(string $text, string $cityPattern, array $cityLexicon): array
    {
        preg_match_all('/\b(' . $cityPattern . ')\b/u', $text, $m);
        $out = [];
        foreach ($m[1] as $c) {
            $canon = $this->toCanonicalCity($c, $cityLexicon) ?? trim($c);
            if ($canon !== '' && !in_array($canon, $out, true)) $out[] = $canon;
        }
        return $out;
    }

    private function collectCitiesInText(string $text, string $cityPattern, array $cityLexicon): array
    {
        preg_match_all('/\b(' . $cityPattern . ')\b/u', $text, $m);
        $out = [];
        foreach ($m[1] as $c) {
            $canon = $this->toCanonicalCity($c, $cityLexicon) ?? trim($c);
            if ($canon !== '' && !in_array($canon, $out, true)) $out[] = $canon;
        }
        return $out;
    }

    private function hasDanglingTo(string $segment): bool
    {
        foreach (preg_split("/\n+/u", str_replace(["\r\n", "\r"], "\n", $segment)) as $line) {
            $t = trim($line);
            if ($t !== '' && preg_match('/\bبه\s*$/u', $t)) return true;
        }
        return false;
    }

    private function originBeforeDanglingTo(string $segment, string $cityPattern, array $cityLexicon): ?string
    {
        foreach (preg_split("/\n+/u", str_replace(["\r\n", "\r"], "\n", $segment)) as $line) {
            $t = trim($line);
            if ($t === '' || !preg_match('/\bبه\s*$/u', $t)) continue;
            if (preg_match('/\b(' . $cityPattern . ')\b(?=[^\p{L}]*\bبه\s*$)/u', $t, $m)) {
                $canon = $this->toCanonicalCity($m[1], $cityLexicon) ?? trim($m[1]);
                if ($canon) return $canon;
            } else {
                $left = preg_split('/\bبه\s*$/u', $t)[0] ?? '';
                $leftCities = $this->collectCitiesOrdered($left, $cityPattern, $cityLexicon);
                if (!empty($leftCities)) return end($leftCities);
            }
        }
        return null;
    }

    private function collectCitiesAfterDanglingTo(string $segment, string $cityPattern, array $cityLexicon, ?array &$titles = []): array
    {
        $titles = [];
        $destinations = [];
        $after = false;

        $cargoAlt = implode('|', array_map('preg_quote', $this->cargoWords));
        $lines = preg_split("/\n+/u", str_replace(["\r\n", "\r"], "\n", $segment));

        foreach ($lines as $line) {
            $t = trim($line);
            if ($t === '') continue;

            if (!$after) {
                if (preg_match('/\bبه\s*$/u', $t)) $after = true;
                continue;
            }

            $hasPhone = preg_match('/0?9\d{9}/u', $t) || preg_match('/(?<!\d)0\d{9,11}(?!\d)/u', $t) || preg_match('/\+?\s?98(?:[\s\-]?\d){10}/u', $t);
            $hasPrice = preg_match('/\b(\d+(?:[.,]\d+)?)\s*(میلیون|ملیون|هزار|تومان|تومن|صاف|صافی)\b/u', $t) || preg_match('/\b(توافقی|توافق)\b/u', $t);
            $isKW     = preg_match('/\b(?:مبدا|مبدأ|بارگیری|از|به|تا|ب|تخلیه|مقصد|مقصدها)\b/u', $t);
            if ($hasPhone || $hasPrice || $isKW) break;

            try {
                if (preg_match('/^(?:(?P<title>' . $cargoAlt . ')\s+)?(?P<city>' . $cityPattern . ')\b/iu', $t, $mm)) {
                    if (!empty($mm['title'])) $titles[] = $this->aliasTitle($mm['title']);
                    $canon = $this->toCanonicalCity($mm['city'], $cityLexicon) ?? trim($mm['city']);
                    if ($canon !== '' && !in_array($canon, $destinations, true)) $destinations[] = $canon;
                    continue;
                }
            } catch (\Exception $e) {
                // Log::warning($e);
            }


            if (preg_match('/^\s*(?P<city>' . $cityPattern . ')\b/u', $t, $mm2)) {
                $canon = $this->toCanonicalCity($mm2['city'], $cityLexicon) ?? trim($mm2['city']);
                if ($canon !== '' && !in_array($canon, $destinations, true)) $destinations[] = $canon;
            }
        }

        return $this->filterParentCities($destinations);
    }

    private function extractGlobalDestinations(string $text, string $cityPattern, array $cityLexicon): array
    {
        $destinations = [];

        if (preg_match_all('/\b(?:به|تا|ب|مقصد|مقصدها)\b\s*(?:[:：\-–—]\s*)?(?:شهر|استان)?\s*\b(' . $cityPattern . ')\b(?P<tail>[^\n]*)/u', $text, $m, PREG_SET_ORDER)) {
            foreach ($m as $one) {
                $first = $this->toCanonicalCity($one[1], $cityLexicon) ?? trim($one[1]);
                $tail  = trim($one['tail'] ?? '');

                // ✅ هم «اولی» و هم (اگر بود) داخل پرانتز/دنباله را باهم جمع می‌کنیم
                $cities = [$first];
                if ($tail !== '') {
                    $more = $this->collectCitiesInText($tail, $cityPattern, $cityLexicon);
                    foreach ($more as $c) if (!in_array($c, $cities, true)) $cities[] = $c;
                }
                foreach ($this->filterParentCities($cities) as $c) {
                    if (!in_array($c, $destinations, true)) $destinations[] = $c;
                }
            }
        }

        if (preg_match_all('/\bتخلیه\b\s*(?:بار)?\s*(?:[:：\-–—]\s*)?(?:شهر|استان)?\s*\b(' . $cityPattern . ')\b(?P<tail>[^\n]*)/u', $text, $mu, PREG_SET_ORDER)) {
            foreach ($mu as $one) {
                $first = $this->toCanonicalCity($one[1], $cityLexicon) ?? trim($one[1]);
                $tail  = trim($one['tail'] ?? '');
                $cities = [$first];

                if ($tail !== '') {
                    $more = $this->collectCitiesInText($tail, $cityPattern, $cityLexicon);
                    foreach ($more as $c) if (!in_array($c, $cities, true)) $cities[] = $c;
                }

                // «آخرین مقصد» = آخرین شهر بعد از فیلتر والد/فرزند
                $cities = $this->filterParentCities($cities);
                $last   = end($cities);
                if ($last !== '' && !in_array($last, $destinations, true)) $destinations[] = $last;
            }
        }

        return array_values(array_unique(array_filter($this->filterParentCities($destinations))));
    }

    // ---------- منطق اصلی مبدا/مقصد ----------
    private function parseOriginsAndDestinations(string $segment, string $cityPattern, array $cityLexicon, ?string $contextOrigin = null): array
    {
        $origins = [];
        $destinations = [];

        $segmentCitiesAll = $this->collectCitiesOrdered($segment, $cityPattern, $cityLexicon);

        $hasRouteKW = preg_match('/\b(?:مبدا|مبدأ|بارگیری|از|به|تا|ب|تخلیه|مقصد|مقصدها)\b/u', $segment);

        // اگر کلیدواژه مسیر نداریم و دقیقاً یک شهر در سگمنت داریم و contextOrigin هست → contextOrigin → origin , آن شهر → destination
        if (!$hasRouteKW && count($segmentCitiesAll) === 1 && $contextOrigin) {
            return ['origins' => [$contextOrigin], 'destinations' => [$segmentCitiesAll[0]]];
        }

        // 1) مبداهای صریح — همه‌ی شهرها بعد از کلیدواژه (با حذف parent)
        if (preg_match_all('/\b(?P<kw>مبدا|مبدأ|بارگیری|از)\b(?P<tail>[^\n]*)/u', $segment, $mOrigin, PREG_SET_ORDER)) {
            foreach ($mOrigin as $one) {
                $tail = trim($one['tail'] ?? '');
                if ($tail === '') continue;
                if (preg_match('/:(.*)$/u', $tail, $cm)) $tail = trim($cm[1]);

                $parts = preg_split('/\b(?:به|تا|ب|تخلیه|مقصد|مقصدها)\b/u', $tail, 2);
                $payload = trim($parts[0] ?? $tail);

                // ✅ کل payload (نه فقط داخل پرانتز)
                $cities = $this->collectCitiesInText($payload, $cityPattern, $cityLexicon);

                $cities = $this->filterParentCities($cities);
                foreach ($cities as $c) $origins[] = $c;
            }
            $origins = array_values(array_unique(array_filter($origins)));
        }

        // 2) مقصدهای صریح
        if (preg_match_all('/\b(?:به|تا|ب|مقصد|مقصدها)\b\s*(?:[:：\-–—]\s*)?(?:شهر|استان)?\s*\b(' . $cityPattern . ')\b(?P<tail>[^\n]*)/u', $segment, $mDestAll, PREG_SET_ORDER)) {
            foreach ($mDestAll as $one) {
                $first = $this->toCanonicalCity($one[1], $cityLexicon) ?? trim($one[1]);
                $tail  = trim($one['tail'] ?? '');
                $cities = [$first];

                if ($tail !== '') {
                    $more = $this->collectCitiesInText($tail, $cityPattern, $cityLexicon);
                    foreach ($more as $c) if (!in_array($c, $cities, true)) $cities[] = $c;
                }
                foreach ($this->filterParentCities($cities) as $c) $destinations[] = $c;
            }
        }

        // 2.5) زوج‌های آزاد «شهر شهر» در خطوط بدون کلیدواژه (مثال: «کرمان  تهران»)
        foreach ($this->extractLooseCityPairs($segment, $cityPattern, $cityLexicon) as [$o, $d]) {
            if ($o && !in_array($o, $origins, true)) $origins[] = $o;
            if ($d && !in_array($d, $destinations, true)) $destinations[] = $d;
        }

        // بلوک مقصد/مقصدها
        $lines = preg_split("/\n+/u", str_replace(["\r\n", "\r"], "\n", $segment));
        $collectBlockDest = false;
        foreach ($lines as $line) {
            $lineTrim = trim($line);
            if ($lineTrim === '') {
                if ($collectBlockDest) break;
                continue;
            }
            if (preg_match('/^(?:مقصد|مقصدها)$/u', $lineTrim)) {
                $collectBlockDest = true;
                continue;
            }
            if ($collectBlockDest) {
                $hasPhone = preg_match('/0?9\d{9}/u', $lineTrim) || preg_match('/(?<!\d)0\d{9,11}(?!\d)/u', $lineTrim) || preg_match('/\+?\s?98(?:[\s\-]?\d){10}/u', $lineTrim);
                $hasPrice = preg_match('/\b(\d+(?:[.,]\d+)?)\s*(میلیون|ملیون|هزار|تومان|تومن|صاف|صافی)\b/u', $lineTrim) || preg_match('/\b(توافقی|توافق)\b/u', $lineTrim);
                $isKW     = preg_match('/\b(?:مبدا|مبدأ|بارگیری|از|به|تا|ب|تخلیه)\b/u', $lineTrim);
                if ($hasPhone || $hasPrice || $isKW) break;

                if (preg_match('/^\s*\b(' . $cityPattern . ')\b/u', $lineTrim, $mm)) {
                    $canon = $this->toCanonicalCity($mm[1], $cityLexicon) ?? trim($mm[1]);
                    $destinations[] = $canon;
                }
            }
        }

        // یکتا
        $origins      = array_values(array_unique(array_filter($origins)));
        $destinations = array_values(array_unique(array_filter($destinations)));

        // توزیع نقش‌ها
        if (empty($origins) && !empty($destinations)) {
            if (preg_match('/\b(?:به|تا|ب|مقصد|مقصدها|تخلیه)\b/u', $segment, $kw, PREG_OFFSET_CAPTURE)) {
                $left    = substr($segment, 0, $kw[0][1]);
                $leftCities = $this->collectCitiesOrdered($left, $cityPattern, $cityLexicon);
                $leftCities = array_values(array_filter($leftCities, fn($c) => !in_array($c, $destinations, true)));
                if (!empty($leftCities)) $origins[] = end($leftCities);
            }
            if (empty($origins) && $contextOrigin) $origins[] = $contextOrigin;
        } elseif (!empty($origins)) {
            // شهرهای باقی‌مانده در سگمنت که نه مبدا هستند نه مقصد
            $remaining = array_values(array_filter(
                $segmentCitiesAll,
                fn($c) => !in_array($c, $origins, true) && !in_array($c, $destinations, true)
            ));
            // 🚫 حذف والدهای هر مبدا از لیستِ باقی‌مانده (مانند «همدان» کنار «اسدآباد»)
            $remaining = $this->removeOriginAncestorsFromList($remaining, $origins);
            if (!empty($remaining)) $destinations = array_values(array_unique(array_merge($destinations, $remaining)));
        } else {
            if (count($segmentCitiesAll) >= 2) {
                $origins[] = $segmentCitiesAll[0];
                $destinations = array_slice($segmentCitiesAll, 1);
            } elseif (count($segmentCitiesAll) === 1 && !$this->hasDanglingTo($segment)) {
                $destinations[] = $segmentCitiesAll[0];
            }
        }

        // منع همپوشانی + حذف parentهای احتمالی
        $origins      = $this->filterParentCities($origins);
        $destinations = $this->filterParentCities(array_values(array_filter($destinations, fn($d) => !in_array($d, $origins, true))));

        return ['origins' => $origins, 'destinations' => $destinations];
    }

    /**
     * استخراج زوج‌های آزاد «شهر شهر» (یا با جداکننده‌های ساده) در خطوطی که کلیدواژه مسیر ندارند.
     * خروجی: list of [origin, destination]
     * اگر دو شهر parent/child باشند، جفت معتبر تولید نمی‌شود.
     */
    private function extractLooseCityPairs(string $segment, string $cityPattern, array $cityLexicon): array
    {
        $pairs = [];
        $lines = preg_split("/\n+/u", str_replace(["\r\n", "\r"], "\n", $segment));
        foreach ($lines as $line) {
            $t = trim($line);
            if ($t === '') continue;

            // نادیده‌گرفتن خطوط دارای کلیدواژه/قیمت/تلفن
            if (preg_match('/\b(?:مبدا|مبدأ|بارگیری|از|به|تا|ب|تخلیه|مقصد|مقصدها)\b/u', $t)) continue;
            if (preg_match('/\b(\d+(?:[.,]\d+)?)\s*(میلیون|ملیون|هزار|تومان|تومن|صاف|صافی)\b/u', $t)) continue;
            if (preg_match('/0?9(?:[\s\-]?\d){9}/u', $t) || preg_match('/\+?\s?98(?:[\s\-]?\d){10}/u', $t) || preg_match('/(?<!\d)0\d{9,11}(?!\d)/u', $t)) continue;

            // دقیقا دو شهر
            preg_match_all('/\b(' . $cityPattern . ')\b/u', $t, $mm);
            $cities = $mm[1] ?? [];
            if (!empty($cities)) {
                $cities = array_map(fn($x) => $this->toCanonicalCity($x, $cityLexicon) ?? trim($x), $cities);
                $cities = $this->filterParentCities($cities);
            }
            if (count($cities) === 2) {
                [$o, $d] = $cities;
                if ($o !== '' && $d !== '' && $o !== $d) {
                    $pairs[] = [$o, $d];
                }
            }
        }
        // یکتا
        $uniq = [];
        $seen = [];
        foreach ($pairs as [$o, $d]) {
            $k = $o . '>' . $d;
            if (!isset($seen[$k])) {
                $uniq[] = [$o, $d];
                $seen[$k] = true;
            }
        }
        return $uniq;
    }

    // --- عنوان (فقط کلمهٔ کامل)
    private function extractTitle(string $text): ?string
    {
        $parts = [];

        if (preg_match('/^[^\S\n]*بار\b\s*(?:[:：\-–—]\s*)?([^\n]+)/um', $text, $m)) {
            $payload = $this->cleanTitlePayload($m[1]);
            $t = $this->titleFromPayload($payload);
            if ($t) $parts[] = $t;
        }
        if (preg_match('/^[^\S\n]*نوع\s*بار\b\s*[:：]\s*([^\n]+)/um', $text, $m2)) { // ✅ اصلاح \n
            $payload = $this->cleanTitlePayload($m2[1]);
            $t = $this->titleFromPayload($payload);
            if ($t) $parts[] = $t;
        }

        // اگر «فوری» هرجا در متن آمد، به عنوان اضافه کن
        // if (preg_match('/\bفوری\b/u', $text)) {
        //     $parts[] = 'فوری';
        // }

        $title = $parts ? implode('، ', $parts) : $this->titleFromPayload($this->cleanTitlePayload($text));
        return $this->dedupeAndAliasTitle($title);
    }

    private function cleanTitlePayload(string $s): string
    {
        $s = preg_replace('/:[a-z0-9_]+:/iu', ' ', $s);
        $s = preg_replace('/[\[\]\(\)]/u', ' ', $s);
        return preg_replace('/[ \t\x{00A0}]+/u', ' ', trim($s));
    }

    /** فقط کلمات کامل؛ نه زیررشته‌ی داخل کلمات دیگر */
    private function titleFromPayload(string $payload): ?string
    {
        $found = [];
        foreach ($this->cargoWords as $c) {
            $pattern = '/(?<!\p{L})' . preg_quote($c, '/') . '(?!\p{L})/u';
            if (preg_match($pattern, $payload)) {
                $found[] = $this->aliasTitle($c);
            }
        }
        $found = array_values(array_unique($found));
        if (empty($found)) return null;
        return implode('، ', $found);
    }

    private function aliasTitle(string $token): string
    {
        $t = trim($token);
        return $this->titleAliases[$t] ?? $t;
    }

    private function dedupeAndAliasTitle(?string $title): ?string
    {
        if (!$title) return null;
        $parts = array_map('trim', explode('،', $title));
        $norm = [];
        foreach ($parts as $p) {
            if ($p === '') continue;
            $p = $this->aliasTitle($p);
            if (!in_array($p, $norm, true)) $norm[] = $p;
        }
        return $norm ? implode(' ، ', $norm) . ' ' : null;
    }

    private function makeDescription($fleet, $origin, $destination, $title, $price, $raw)
    {
        $randomDescription = [
            'سلام',
            'درود بر شما',
            'عرض سلام',
            'سلام و وقت بخیر',
            'رانندگان عزیز سلام',
            'همکاران محترم سلام',
            'درود بر همکاران عزیز',
            'دوستان گرامی',
            'هم‌سفران جاده سلام',
            'عزیزان زحمت‌کش جاده سلام'
        ];
        $greeting = Arr::random($randomDescription);

        // ✅ به فرمت استاندارد برگردانده شد
        $pieces = [$greeting . ': ' . "\n", 'درخواست حمل'];
        if ($fleet)        $pieces[] = "بار $fleet";
        if ($origin)       $pieces[] = "از $origin";
        if ($destination)  $pieces[] = "به $destination";
        // if ($title)        $pieces[] = "  $title";
        if ($price)        $pieces[] = "کرایه: $price";
        return implode(' ', $pieces);
    }

    private function simpleCityToCity(string $text, string $cityPattern, array $cityLexicon): ?array
    {
        // یک اتصال مسیر پیدا کن (به/تا یا خط تیره)
        if (!preg_match('/\b(?:به|تا)\b|[-–—]/u', $text, $m, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $op = $m[0][0];
        $pos = $m[0][1];

        $left  = trim(mb_substr($text, 0, $pos));
        $right = trim(mb_substr($text, $pos + mb_strlen($op)));

        // از الگوی بزرگ فقط یک‌بار در هر سمت استفاده می‌کنیم
        $leftCities  = $this->collectCitiesOrdered($left,  $cityPattern, $cityLexicon);
        $rightCities = $this->collectCitiesOrdered($right, $cityPattern, $cityLexicon);

        $o = $leftCities ? end($leftCities) : null;   // آخرین شهرِ قبل از اتصال
        $d = $rightCities[0] ?? null;                 // اولین شهرِ بعد از اتصال

        if ($o && $d) {
            [$o, $d] = $this->filterParentCities([$o, $d]);
            if ($o && $d && $o !== $d) return [$o, $d];
        }
        return null;
    }

    private function pushUniqueLoad(array &$loads, array $record): void
    {
        $key = implode('|', [
            $record['fleet'] ?? '',
            $record['origin'] ?? '',
            $record['destination'] ?? '',
            $record['phoneNumber'] ?? '',
            $record['price'] ?? '',
        ]);
        if (!isset($loads[$key])) $loads[$key] = $record;
        else if (empty($loads[$key]['title']) && !empty($record['title'])) $loads[$key] = $record;
    }

    // ---------------------- Parent/Child Filter ----------------------

    /**
     * حذف شهرهای parent وقتی در یک لیستِ هم‌خطی/هم‌منبع کنار child خودشان آمده‌اند.
     * ورودی: لیست نام شهرها با ترتیب ورودی.
     * خروجی: همان لیست بدون والدهایی که اجداد یکی دیگر در همین لیست‌اند. ترتیب حفظ می‌شود.
     */
    private function filterParentCities(array $names): array
    {
        if (count($names) <= 1) return $names;

        // هر نام → لیست idها
        $idsList = [];
        foreach ($names as $i => $name) {
            $idsList[$i] = $this->citiesByNameMulti[$name] ?? [];
        }

        // اگر یکی از idهای A، اجداد یکی از idهای B بود → A حذف
        $removeIdx = [];
        foreach ($idsList as $i => $idsA) {
            foreach ($idsList as $j => $idsB) {
                if ($i === $j || empty($idsA) || empty($idsB)) continue;
                if ($this->isAncestorAny($idsA, $idsB)) {
                    $removeIdx[$i] = true;
                    break;
                }
            }
        }

        // خروجی با حفظ ترتیب و بدون تکرار
        $out = [];
        foreach ($names as $i => $name) if (!isset($removeIdx[$i])) $out[] = $name;
        $uniq = [];
        foreach ($out as $n) if (!in_array($n, $uniq, true)) $uniq[] = $n;
        return $uniq;
    }

    /** آیا یکی از idsA اجداد یکی از idsB است؟ */
    private function isAncestorAny(array $idsA, array $idsB): bool
    {
        foreach ($idsA as $a) {
            foreach ($idsB as $b) {
                if ($this->isAncestor($a, $b)) return true;
            }
        }
        return false;
    }

    /** آیا idA اجداد (والد/والدِ والد/...) idB است؟ */
    private function isAncestor($idA, $idB): bool
    {
        if (!$idA || !$idB) return false;
        $guard = 0;
        $p = $this->cityParentsMap[$idB] ?? null;
        while ($p && $guard < 20) {
            if ($p == $idA) return true;
            $p = $this->cityParentsMap[$p] ?? null;
            $guard++;
        }
        return false;
    }

    /**
     * انتخاب «بهترین» id برای یک نام شهر: ترجیحاً leaf (نه والدِ سایر idهای هم‌نام).
     */
    private function pickBestCityIdByName(?string $name): ?int
    {
        if (!$name) return null;
        $ids = $this->citiesByNameMulti[$name] ?? [];
        if (empty($ids)) return null;
        // حذف اجداد: هر idی که اجداد id دیگری است را کنار بگذار
        $candidates = $ids;
        foreach ($ids as $a) {
            foreach ($ids as $b) {
                if ($a === $b) continue;
                if ($this->isAncestor($a, $b)) {
                    $candidates = array_values(array_filter($candidates, fn($x) => $x !== $a));
                }
            }
        }
        return $candidates[0] ?? $ids[0];
    }

    /**
     * حذف هر شهری از $candidates که اجدادِ حداقل یکی از $origins باشد.
     * (برای جلوگیری از اضافه‌شدنِ استان/والدِ مبدا به‌عنوان مقصد)
     */
    private function removeOriginAncestorsFromList(array $candidates, array $origins): array
    {
        if (empty($candidates) || empty($origins)) return $candidates;

        // نام → لیست idها
        $origIdsList = array_map(fn($name) => $this->citiesByNameMulti[$name] ?? [], $origins);

        $out = [];
        foreach ($candidates as $candName) {
            $candIds = $this->citiesByNameMulti[$candName] ?? [];
            $isAncestorOfAnyOrigin = false;
            foreach ($origIdsList as $origIds) {
                if ($this->isAncestorAny($candIds, $origIds)) {
                    $isAncestorOfAnyOrigin = true;
                    break;
                }
            }
            if (!$isAncestorOfAnyOrigin) $out[] = $candName;
        }
        return $out;
    }
}
