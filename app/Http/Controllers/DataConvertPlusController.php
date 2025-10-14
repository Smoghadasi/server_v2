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
                        return $this->getLoadFromTel($cargo);
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
                    return $this->getLoadFromTel($newCargo);
                }
            }

            // در نهایت بار فعلی رو بده به اپراتور
            $cargo->operator_id = $userId;
            $cargo->save();
            return $this->getLoadFromTel($cargo);
        }

        // ۴. اگر هیج باری نبود → برگرد به داشبورد
        return redirect(url('dashboard'))->with('danger', 'هیچ باری وجود ندارد');
    }

    public function dataConvertSmart($cargo)
    {
        $text = $cargo->cargo;

        // نرمال‌سازی و آماده‌سازی متن
        $normalizedText = str_replace(["\r\n", "\r"], "\n", trim($text));
        $flatText = preg_replace('/\s+/', ' ', $normalizedText);
        $flatText = preg_replace('/\b(کرایه|وزن|بار|تخلیه|نوع بار|کل)\b/u', '', $flatText);
        $lines = array_values(array_filter(array_map('trim', explode("\n", $normalizedText))));

        // کش دیتاهای ثابت
        $fleetMap = cache()->remember('fleet_map', 60, fn() => DB::table('fleets')->pluck('id', 'title')->toArray());
        $cityMap = cache()->remember('city_map', 60, fn() => DB::table('province_cities')->pluck('id', 'name')->toArray());
        $parentCityMap = cache()->remember('parent_city_map', 60, fn() => DB::table('province_cities')->pluck('parent_id', 'name')->toArray());

        $equivalentWordsMap = method_exists($this, 'getEquivalentWords') ? $this->getEquivalentWords() : [];

        // آماده‌سازی لیست ناوگان‌ها
        $fleetTitles = array_unique(array_merge(array_keys($fleetMap), array_keys($equivalentWordsMap)));
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
            $origin = reset($allCities); // اولین شهر را به عنوان مبدا قرار دهید
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
                        $originName = ProvinceCity::where('name', $match[1])
                            ->where('parent_id', '!=', 0)
                            ->get(['id', 'name', 'parent_id']);
                        $destinationName = ProvinceCity::where('name', $match[2])
                            ->where('parent_id', '!=', 0)
                            ->get(['id', 'name', 'parent_id']);
                        $results[] = [
                            'fleet' => $fleet,
                            'fleet_id' => $fleetMap[$fleet] ?? null,
                            'origin' => $match[1], // مبدا
                            'originProvince' => $originName, // مبدا

                            'destination' => $match[2], // مقصد
                            'destinationProvince' => $destinationName, // مقصد
                            'phone' => $phone,
                            'freight' => 0,
                            'priceType' => 'توافقی'

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
                    if ($dest !== $lastOrigin) { // جلوگیری از ثبت مجدد مبدا به عنوان مقصد
                        foreach ($fleets as $fleet) {
                            $originName = ProvinceCity::where('name', $lastOrigin)
                                ->where('parent_id', '!=', 0)
                                ->get(['id', 'name', 'parent_id']);
                            $destinationName = ProvinceCity::where('name', $dest)
                                ->where('parent_id', '!=', 0)
                                ->get(['id', 'name', 'parent_id']);
                            $results[] = [
                                'fleet' => $fleet,
                                'fleet_id' => $fleetMap[$fleet] ?? null,
                                'origin' => $lastOrigin, // مبدا
                                'originProvince' => $originName, // مبدا

                                'destination' => $dest, // مقصد
                                'destinationProvince' => $destinationName, // مقصد
                                'phone' => $phone,
                                'freight' => 0,
                                'priceType' => 'توافقی'

                            ];
                        }
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
                        $originName = ProvinceCity::where('name', $origin)
                            ->where('parent_id', '!=', 0)
                            ->get(['id', 'name', 'parent_id']);
                        $destinationName = ProvinceCity::where('name', $dest)
                            ->get(['id', 'name', 'parent_id']);
                        // return $destinationName;
                        $results[] = [
                            'fleet' => $fleet,
                            'fleet_id' => $fleetMap[$fleet] ?? null,
                            'origin' => $origin, // مبدا
                            'originProvince' => $originName, // مبدا

                            'destination' => $dest,
                            'destinationProvince' => $destinationName ?? null,
                            'phone' => $phone,
                            'freight' => 0,
                            'priceType' => 'توافقی'

                        ];
                    }
                }
            }
        }

        // حذف تکراری‌ها
        $uniqueResults = collect($results)->unique(fn($item) => ($item['fleet'] ?? '') . '-' . $item['origin'] . '-' . $item['destination'] . '-' . ($item['phone'] ?? ''))->values()->all();
        // return $uniqueResults;
        // اطلاعات تکمیلی برای نمایش
        $countOfCargos = CargoConvertList::where('operator_id', 0)->count();
        $users = UserController::getOnlineAndOfflineUsers();

        return view('admin.load.smartCreateCargo', compact('cargo', 'countOfCargos', 'users', 'uniqueResults'));
    }


    /** واژه‌های باری (برای عنوان) که نباید ناوگان حساب شوند */
    private array $cargoWords = [
        'تره بار',
        'اثاثیه',
        'خشکبار',
        'یونجه',
        'کود مرغی',
        'قطعات',
        'آهن',
        'نخود',
        'گوجه',
        'خیار',
        'برنج',
        'پلیسه',
        'لوله',
        'سبک بار',
        'روبار',
        'روباری',
        'کنارباری',
        'چوب',
        'مرکبات',
        'مرکبات سبد',
        'اسپیکر',
        'کف تره باری',
        'چادر'
    ];

    /** نگاشت هم‌ارزی برخی واژه‌ها برای عنوان (مثلا روبار→روباری) */
    private array $titleAliases = [
        'روبار' => 'روباری',
    ];

    public function getLoadFromTel($cargo)
    {
        // return $request;
        $raw = $cargo->cargo;
        // $raw = (string) $request->input('text', '');
        $raw = trim($raw);
        if ($raw === '') {
            return response()->json(['success' => false, 'message' => 'متن ورودی خالی است.']);
        }

        // 1) نرمال‌سازی
        $text = $this->normalizeText($raw);

        // 2) داده‌های پایه از DB
        $citiesById = DB::table('province_cities')->pluck('name', 'id')->toArray(); // id => name
        $fleetsById = DB::table('fleets')->pluck('title', 'id')->toArray();        // id => title

        // 3) معادل‌ها (equivalents)
        [$cityLexicon, $fleetLexicon] = $this->buildLexicons($citiesById, $fleetsById);

        // 4) الگوی رجکس
        $cityTokens  = array_keys($cityLexicon);
        usort($cityTokens, fn($a, $b) => mb_strlen($b) - mb_strlen($a));
        $cityPattern = $cityTokens ? implode('|', array_map('preg_quote', $cityTokens)) : '([آ-ی\s\-]+)';

        $fleetTokens  = array_keys($fleetLexicon);
        usort($fleetTokens, fn($a, $b) => mb_strlen($b) - mb_strlen($a));
        $fleetPattern = $fleetTokens ? implode('|', array_map('preg_quote', $fleetTokens)) : '([آ-ی\s\-]+)';

        // 5) تلفن + حذف از متن (حالا موبایل و ثابت را هر دو می‌گیرد)
        $firstPhone = $this->extractFirstPhone($text);
        if (!empty($firstPhone)) {
            $digits = preg_replace('/\D+/u', '', $firstPhone);
            // اگر موبایل باشد (09xxxxxxxxx) نسخه بدون صفر اول هم حذف می‌شود
            $variants = [$digits];
            if (strlen($digits) === 11 && substr($digits, 0, 2) === '09') {
                $variants[] = substr($digits, 1);
            }
            foreach ($variants as $v) {
                if ($v !== '') {
                    $text = preg_replace('/' . preg_quote($v, '/') . '/u', ' ', $text);
                }
            }
            $text = $this->squashLines($text);
        }

        // 6) قیمت
        $price = $this->extractPrice($text);

        // 7) سگمنت‌بندی فقط بر اساس ناوگان
        $segments = $this->splitByFleets($text, $fleetPattern);

        // 8) زمینه‌های کل متن
        $globalContextOrigin =
            $this->getContextOrigin($text, $cityPattern, $cityLexicon)
            ?: $this->getPrefaceContextOrigin($text, $fleetPattern, $cityPattern, $cityLexicon);

        // مسیر پیش‌متن (اگر قبل از اولین ناوگان دقیقاً دو شهر بیاید)
        $prefaceRoute = $this->extractPrefaceRoute($text, $fleetPattern, $cityPattern, $cityLexicon);

        // مقاصد سراسری
        $globalDestinations = $this->extractGlobalDestinations($text, $cityPattern, $cityLexicon);

        // نگاشت معکوس
        $citiesByName  = array_flip($citiesById);
        $fleetsByTitle = array_flip($fleetsById);

        $allLoads = [];
        foreach ($segments as $segment) {
            // ناوگان‌های همین سگمنت (واژه‌های باری حذف می‌شوند)
            $segmentFleets = $this->findFleetsInSegment($segment, $fleetPattern, $fleetLexicon, $this->cargoWords);

            // مبدأ زمینه‌ای سگمنت
            $contextOrigin =
                $this->getContextOrigin($segment, $cityPattern, $cityLexicon)
                ?: $globalContextOrigin;

            // مبدا/مقصد داخل سگمنت
            $parsed = $this->parseOriginsAndDestinations($segment, $cityPattern, $cityLexicon, $contextOrigin);
            $origins      = $parsed['origins'];
            $destinations = $parsed['destinations'];

            // «بهِ آویزان»
            if ($this->hasDanglingTo($segment)) {
                $beOrigin = $this->originBeforeDanglingTo($segment, $cityPattern, $cityLexicon);
                if ($beOrigin && empty($origins)) $origins[] = $beOrigin;

                $destFromDangling = $this->collectCitiesAfterDanglingTo($segment, $cityPattern, $cityLexicon, $titleFromDangling);
                if (!empty($destFromDangling) && empty($destinations)) {
                    $destinations = $destFromDangling;
                }
            }

            // مقصدهای سراسری در صورت نیاز
            if (empty($destinations) && !empty($globalDestinations)) {
                $destinations = $globalDestinations;
            }

            // عنوان
            $title = $this->extractTitle($segment) ?: $this->extractTitle($text);
            if (!empty($titleFromDangling ?? [])) {
                $title = $title ? ($title . '، ' . implode('، ', array_unique($titleFromDangling))) : implode('، ', array_unique($titleFromDangling));
            }
            $title = $this->dedupeAndAliasTitle($title);

            // fallback روی کل متن (اگر سگمنت فاقد شهر بود)
            if (empty($origins) && empty($destinations)) {
                $segHasCity = !empty($this->collectCitiesOrdered($segment, $cityPattern, $cityLexicon));
                if (!$segHasCity) {
                    $parsedAll = $this->parseOriginsAndDestinations($text, $cityPattern, $cityLexicon, $globalContextOrigin);
                    $origins = $parsedAll['origins'];
                    $destinations = $parsedAll['destinations'];

                    if (empty($destinations) && !empty($globalDestinations)) {
                        $destinations = $globalDestinations;
                    }
                    if (empty($origins) && !empty($firstPhone) && preg_match('/^0912/', $firstPhone)) {
                        $origins[] = 'تهران';
                    }
                }
            }

            // اگر مقصد داریم و مبدا خالی → 0912 ⇒ تهران
            if (empty($origins) && !empty($destinations)) {
                if (!empty($firstPhone) && preg_match('/^0912/', $firstPhone)) {
                    $origins[] = 'تهران';
                }
            }

            // تزریق مسیر پیش‌متن
            if ($prefaceRoute) {
                if (empty($origins) && empty($destinations)) {
                    $origins[] = $prefaceRoute['origin'];
                    $destinations[] = $prefaceRoute['destination'];
                } elseif (empty($origins) && !empty($destinations)) {
                    $origins[] = $prefaceRoute['origin'];
                } elseif (!empty($origins) && empty($destinations)) {
                    $destinations[] = $prefaceRoute['destination'];
                }
            }

            if (empty($origins) && empty($destinations) && empty($segmentFleets)) {
                continue;
            }

            $usedFleetList    = $segmentFleets ?: [null];
            $usedOrigins      = $origins ?: [null];
            $usedDestinations = $destinations ?: [null];

            // $destinationName = ProvinceCity::where('name', $match[2])
            //     ->where('parent_id', '!=', 0)
            //     ->get(['id', 'name', 'parent_id']);

            foreach ($usedFleetList as $fleetTitle) {
                foreach ($usedOrigins as $originCity) {
                    foreach ($usedDestinations as $destCity) {

                        $origins = ProvinceCity::where('name', $originCity)
                            ->where('parent_id', '!=', 0)
                            ->get(['id', 'name', 'parent_id']);
                        $destinations= ProvinceCity::where('name', $destCity)
                            ->where('parent_id', '!=', 0)
                            ->get(['id', 'name', 'parent_id']);

                        $record = [
                            'fleet'           => $fleetTitle,
                            'fleet_id'        => $fleetTitle ? ($fleetsByTitle[$fleetTitle] ?? null) : null,
                            'origins'          => $origins,
                            'destinations'          => $destinations,
                            'origin'          => $originCity,
                            'origin_id'       => $originCity ? ($citiesByName[$originCity] ?? null) : null,
                            'destination'     => $destCity,
                            'destination_id'  => $destCity ? ($citiesByName[$destCity] ?? null) : null,
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
                preg_match("/($fleetPattern)/u", $text, $f);
                $fleetTitle = $f[1] ?? null;
                if ($fleetTitle) $fleetTitle = $this->toCanonicalFleet($fleetTitle, $fleetLexicon);
                $title = $this->extractTitle($text);

                $this->pushUniqueLoad($allLoads, [
                    'fleet'           => $fleetTitle,
                    'fleet_id'        => $fleetTitle ? ($fleetsByTitle[$fleetTitle] ?? null) : null,
                    'origins'          => $origins,
                    'destinations'          => $destinations,
                    'origin'          => $originCity,
                    'origin_id'       => $originCity ? ($citiesByName[$originCity] ?? null) : null,
                    'destination'     => $destCity,
                    'destination_id'  => $destCity ? ($citiesByName[$destCity] ?? null) : null,
                    'price'           => $price,
                    'title'           => $title,
                    'phoneNumber'     => $firstPhone ?? '',
                    'description'     => $this->makeDescription($fleetTitle, $originCity, $destCity, $title, $price, $raw),
                    'raw'             => $raw,
                ]);
            }
        }
        $uniqueResults = array_values($allLoads);
        $countOfCargos = CargoConvertList::where('operator_id', 0)
            ->where('isBlocked', 0)
            ->where('isDuplicate', 0)
            ->count();
        $users = UserController::getOnlineAndOfflineUsers();
        // return $uniqueResults;
        return view('admin.load.smartCreateCargo', compact('cargo', 'countOfCargos', 'users', 'uniqueResults'));

        return response()->json([
            'success' => true,
            'data'    => array_values($allLoads),
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    // ---------------------- Lexicon (equivalents) ----------------------

    private function buildLexicons(array $citiesById, array $fleetsById): array
    {
        $cityLexicon  = [];
        $fleetLexicon = [];

        foreach ($citiesById as $id => $name) {
            $cityLexicon[$this->normalizeLexeme($name)] = ['id' => $id, 'name' => $name];
        }
        foreach ($fleetsById as $id => $title) {
            $fleetLexicon[$this->normalizeLexeme($title)] = ['id' => $id, 'title' => $title];
        }

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

        $equivFleets = DB::table('equivalents')
            ->where('type', 'fleet')
            ->select('original_word_id', 'equivalentWord')
            ->get();
        foreach ($equivFleets as $row) {
            $canonTitle = $fleetsById[$row->original_word_id] ?? null;
            if (!$canonTitle) continue;
            $eq = $this->normalizeLexeme($row->equivalentWord);
            if ($eq !== '') $fleetLexicon[$eq] = ['id' => $row->original_word_id, 'title' => $canonTitle];
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

    private function toCanonicalFleet(?string $matched, array $fleetLexicon): ?string
    {
        if ($matched === null) return null;
        $norm = $this->normalizeLexeme($matched);
        return $fleetLexicon[$norm]['title'] ?? $matched;
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
        $text = preg_replace('/[^\PC\s]/u', ' ', $text);       // حذف علائم غیرمتنی
        $text = preg_replace('/:[a-z0-9_]+:/iu', ' ', $text);  // حذف ایموجی‌های متنی
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
     * استخراج اولین شماره تلفن (موبایل یا ثابت ایران) + نرمال‌سازی:
     * - موبایل: 09xxxxxxxxx (با یا بدون جداکننده‌ها)
     * - ثابت: 0 + 9 تا 11 رقم بعدی (معمولاً مجموع 10 تا 11 رقم)، با یا بدون جداکننده‌ها، اما نه 09...
     */
    private function extractFirstPhone(string $text): ?string
    {
        // 1) موبایل با جداکننده‌های اختیاری
        if (preg_match('/0?9(?:[\s\-]?\d){9}/u', $text, $m)) {
            $digits = preg_replace('/\D+/u', '', $m[0]);
            if (strlen($digits) === 10 && $digits[0] === '9') $digits = '0' . $digits;
            if (strlen($digits) === 11 && substr($digits, 0, 2) === '09') return $digits;
        }

        // 2) تلفن ثابت ایران: 0 + (9 تا 11 رقم) با جداکننده‌های اختیاری، ولی نه شروع با 09
        if (preg_match('/0(?!9)(?:[\s\-]?\d){9,11}/u', $text, $m2)) {
            $digits = preg_replace('/\D+/u', '', $m2[0]);
            if (strlen($digits) >= 10 && strlen($digits) <= 12) return $digits;
        }

        // 3) بک‌آپ: دنباله‌های «0XXXXXXXXXX» یا «XXXXXXXXXX» که با 9 شروع می‌شود (موبایل بدون صفر)
        if (preg_match('/(?<!\d)0\d{9,11}(?!\d)/u', $text, $m3)) {
            return $m3[0];
        }
        if (preg_match('/(?<!\d)9\d{9}(?!\d)/u', $text, $m4)) {
            return '0' . $m4[0];
        }

        return null;
    }

    private function extractPrice(string $text): ?string
    {
        if (preg_match('/(\d+(?:[.,]\d+)?)\s*(میلیون|ملیون|هزار|تومان|تومن|صاف|صافی)/iu', $text, $m)) {
            return trim($m[1] . ' ' . $m[2]);
        }
        if (preg_match('/\b(توافقی|توافق)\b/u', $text)) return 'توافقی';
        if (preg_match('/(\d+)\s*(صاف|صافی)/u', $text, $m2)) return trim($m2[1] . ' ' . $m2[2]);
        return null;
    }

    private function splitByFleets(string $text, string $fleetPattern): array
    {
        if (trim($fleetPattern) === '') return [$text];

        preg_match_all(
            "/(?:$fleetPattern)(?:\s*(?:و|،|\/|or)?\s*(?:$fleetPattern))*[\s\S]*?(?=(?:$fleetPattern)|$)/u",
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

    /** ناوگان‌های سگمنت، با حذف واژه‌های باری */
    private function findFleetsInSegment(string $segment, string $fleetPattern, array $fleetLexicon, array $cargoWords): array
    {
        if (trim($fleetPattern) === '') return [];
        preg_match_all("/($fleetPattern)/u", $segment, $m);
        $found = $m[1] ?? [];
        $out = [];
        foreach ($found as $f) {
            $canon = $this->toCanonicalFleet($f, $fleetLexicon);
            if ($canon && !$this->isCargoWord($canon, $cargoWords) && !in_array($canon, $out, true)) {
                $out[] = $canon;
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

    // مبدأ زمینه‌ای از کلیدواژه‌ها
    private function getContextOrigin(string $text, string $cityPattern, array $cityLexicon): ?string
    {
        if (preg_match('/\b(?:مبدا|مبدأ|بارگیری|از)\b(?P<tail>[^\n]*)/u', $text, $m)) {
            $tail = trim($m['tail'] ?? '');
            if ($tail !== '') {
                if (preg_match('/:(.*)$/u', $tail, $cm)) $tail = trim($cm[1]);
                $parts = preg_split('/\b(?:به|تا|ب|تخلیه|مقصد|مقصدها)\b/u', $tail, 2);
                $payload = trim($parts[0] ?? $tail);
                $cities = $this->collectCitiesInText($payload, $cityPattern, $cityLexicon);
                if (!empty($cities)) return end($cities);
            }
        }
        return null;
    }

    // مبدأ زمینه‌ای از پیش‌متن (برای استان/شهر داخل پرانتز)
    private function getPrefaceContextOrigin(string $text, string $fleetPattern, string $cityPattern, array $cityLexicon): ?string
    {
        if (!preg_match("/($fleetPattern)/u", $text, $fm, PREG_OFFSET_CAPTURE)) return null;
        $pos = $fm[0][1];
        $preface = trim(mb_substr($text, 0, $pos));
        if ($preface === '') return null;
        if (preg_match('/\b(?:مبدا|مبدأ|بارگیری|از|به|تا|ب|تخلیه|مقصد|مقصدها)\b/u', $preface)) return null;

        if (preg_match('/\(([^)]*)\)/u', $preface, $pm)) {
            $inside = $this->collectCitiesInText($pm[1] ?? '', $cityPattern, $cityLexicon);
            if (!empty($inside)) return end($inside);
        }
        return null;
    }

    // مسیر «پیش‌متن» اگر دقیقاً دو شهر قبل از اولین ناوگان باشد
    private function extractPrefaceRoute(string $text, string $fleetPattern, string $cityPattern, array $cityLexicon): ?array
    {
        if (!preg_match("/($fleetPattern)/u", $text, $fm, PREG_OFFSET_CAPTURE)) return null;
        $pos = $fm[0][1];
        $preface = trim(mb_substr($text, 0, $pos));
        if ($preface === '') return null;
        if (preg_match('/\b(?:مبدا|مبدأ|بارگیری|از|به|تا|ب|تخلیه|مقصد|مقصدها)\b/u', $preface)) return null;

        $cities = $this->collectCitiesOrdered($preface, $cityPattern, $cityLexicon);
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

    /** آیا خطی با «… به» در انتهای خط داریم؟ */
    private function hasDanglingTo(string $segment): bool
    {
        foreach (preg_split("/\n+/u", str_replace(["\r\n", "\r"], "\n", $segment)) as $line) {
            $t = trim($line);
            if ($t !== '' && preg_match('/\bبه\s*$/u', $t)) return true;
        }
        return false;
    }

    /** شهر قبل از «به» در همان خط (برای تعیین مبدأ در حالت «بهِ آویزان») */
    private function originBeforeDanglingTo(string $segment, string $cityPattern, array $cityLexicon): ?string
    {
        foreach (preg_split("/\n+/u", str_replace(["\r\n", "\r"], "\n", $segment)) as $line) {
            $t = trim($line);
            if ($t === '' || !preg_match('/\bبه\s*$/u', $t)) continue;
            // آخرین شهرِ قبل از «به»
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

    /**
     * جمع‌آوری مقصدها بعد از خط «… به»
     * خطوطی مثل «[اختیاری: واژهٔ باری] + شهر» تا رسیدن به تلفن/قیمت/کلیدواژهٔ جدید.
     * عنوان‌های باری کشف‌شده در $titles برگردانده می‌شوند.
     */
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

            // توقف بر اساس تلفن/قیمت/کلیدواژهٔ جدید
            $hasPhone = preg_match('/0?9\d{9}/u', $t) || preg_match('/(?<!\d)0\d{9,11}(?!\d)/u', $t);
            $hasPrice = preg_match('/\b(\d+(?:[.,]\d+)?)\s*(میلیون|ملیون|هزار|تومان|تومن|صاف|صافی)\b/u', $t) || preg_match('/\b(توافقی|توافق)\b/u', $t);
            $isKW     = preg_match('/\b(?:مبدا|مبدأ|بارگیری|از|به|تا|ب|تخلیه|مقصد|مقصدها)\b/u', $t);
            if ($hasPhone || $hasPrice || $isKW) break;

            // (اختیاری) واژهٔ باری + شهر
            if (preg_match('/^(?:(?P<title>' . $cargoAlt . ')\s+)?(?P<city>' . $cityPattern . ')\b/iu', $t, $mm)) {
                if (!empty($mm['title'])) $titles[] = $this->aliasTitle($mm['title']);
                $canon = $this->toCanonicalCity($mm['city'], $cityLexicon) ?? trim($mm['city']);
                if ($canon !== '' && !in_array($canon, $destinations, true)) $destinations[] = $canon;
                continue;
            }

            // فقط شهر
            if (preg_match('/^\s*(?P<city>' . $cityPattern . ')\b/u', $t, $mm2)) {
                $canon = $this->toCanonicalCity($mm2['city'], $cityLexicon) ?? trim($mm2['city']);
                if ($canon !== '' && !in_array($canon, $destinations, true)) $destinations[] = $canon;
            }
        }

        return $destinations;
    }

    /** مقاصد سراسری از کل متن (برای وقتی مقصد قبل از ناوگان آمده) */
    private function extractGlobalDestinations(string $text, string $cityPattern, array $cityLexicon): array
    {
        $destinations = [];

        if (preg_match_all('/\b(?:به|تا|ب|مقصد|مقصدها)\b\s*(?:[:：\-–—]\s*)?(?:شهر|استان)?\s*\b(' . $cityPattern . ')\b(?P<tail>[^\n]*)/u', $text, $m, PREG_SET_ORDER)) {
            foreach ($m as $one) {
                $first = $this->toCanonicalCity($one[1], $cityLexicon) ?? trim($one[1]);
                if ($first !== '' && !in_array($first, $destinations, true)) $destinations[] = $first;

                $tail = trim($one['tail'] ?? '');
                if ($tail !== '') {
                    if (preg_match('/\(([^)]*)\)/u', $tail, $pm)) {
                        $inside = $this->collectCitiesInText($pm[1] ?? '', $cityPattern, $cityLexicon);
                        foreach ($inside as $c) if (!in_array($c, $destinations, true)) $destinations[] = $c;
                    } else {
                        $more = $this->collectCitiesInText($tail, $cityPattern, $cityLexicon);
                        foreach ($more as $c) if (!in_array($c, $destinations, true)) $destinations[] = $c;
                    }
                }
            }
        }

        if (preg_match_all('/\bتخلیه\b\s*(?:[:：\-–—]\s*)?(?:شهر|استان)?\s*\b(' . $cityPattern . ')\b(?P<tail>[^\n]*)/u', $text, $mu, PREG_SET_ORDER)) {
            foreach ($mu as $one) {
                $first = $this->toCanonicalCity($one[1], $cityLexicon) ?? trim($one[1]);
                $tail  = trim($one['tail'] ?? '');
                $cities = [$first];
                if ($tail !== '') {
                    if (preg_match('/\(([^)]*)\)/u', $tail, $pm)) {
                        $inside = $this->collectCitiesInText($pm[1] ?? '', $cityPattern, $cityLexicon);
                        if (!empty($inside)) $cities = $inside;
                    } else {
                        $more = $this->collectCitiesInText($tail, $cityPattern, $cityLexicon);
                        if (!empty($more)) $cities = array_merge($cities, $more);
                    }
                }
                $last = end($cities);
                if ($last !== '' && !in_array($last, $destinations, true)) $destinations[] = $last;
            }
        }

        return array_values(array_unique(array_filter($destinations)));
    }

    // استخراج مبدا/مقصد ها (منطق عمومی)
    private function parseOriginsAndDestinations(string $segment, string $cityPattern, array $cityLexicon, ?string $contextOrigin = null): array
    {
        $origins = [];
        $destinations = [];
        $suppressed = [];

        $segmentCitiesAll = $this->collectCitiesOrdered($segment, $cityPattern, $cityLexicon);

        $hasRouteKW = preg_match('/\b(?:مبدا|مبدأ|بارگیری|از|به|تا|ب|تخلیه|مقصد|مقصدها)\b/u', $segment);
        if (!$hasRouteKW && count($segmentCitiesAll) === 1 && $contextOrigin) {
            return ['origins' => [$contextOrigin], 'destinations' => [$segmentCitiesAll[0]]];
        }

        // 1) مبداهای صریح
        if (preg_match_all('/\b(?P<kw>مبدا|مبدأ|بارگیری|از)\b(?P<tail>[^\n]*)/u', $segment, $mOrigin, PREG_SET_ORDER)) {
            foreach ($mOrigin as $one) {
                $kw   = $one['kw'];
                $tail = trim($one['tail'] ?? '');
                if ($tail === '') continue;

                if (preg_match('/:(.*)$/u', $tail, $cm)) $tail = trim($cm[1]);
                $parts = preg_split('/\b(?:به|تا|ب|تخلیه|مقصد|مقصدها)\b/u', $tail, 2);
                $payload = trim($parts[0] ?? $tail);

                $parenCity = null;
                if (preg_match('/\(([^)]*)\)/u', $payload, $pm)) {
                    $inner = $this->collectCitiesInText($pm[1] ?? '', $cityPattern, $cityLexicon);
                    if (!empty($inner)) $parenCity = end($inner);
                }

                $lineCities = $this->collectCitiesInText($payload, $cityPattern, $cityLexicon);

                if ($kw === 'از') {
                    if ($parenCity) {
                        $origins[] = $parenCity;
                        if (!empty($lineCities)) $suppressed = array_values(array_unique(array_merge($suppressed, $lineCities)));
                    } else {
                        $otherCities = array_values(array_filter($segmentCitiesAll, fn($c) => !in_array($c, $lineCities, true)));
                        if (count($lineCities) >= 2 && count($otherCities) >= 1) {
                            $chosen = end($lineCities);
                            $origins[] = $chosen;
                            $toSuppress = array_slice($lineCities, 0, -1);
                            if (!empty($toSuppress)) $suppressed = array_values(array_unique(array_merge($suppressed, $toSuppress)));
                        } elseif (!empty($lineCities)) {
                            $origins[] = $lineCities[0];
                            if (count($lineCities) > 1) foreach (array_slice($lineCities, 1) as $c) $destinations[] = $c;
                        }
                    }
                } else { // مبدا/مبدأ/بارگیری
                    if ($parenCity) {
                        $origins[] = $parenCity;
                        if (!empty($lineCities)) $suppressed = array_values(array_unique(array_merge($suppressed, $lineCities)));
                    } else {
                        if (!empty($lineCities)) {
                            $chosen = end($lineCities);
                            if (count($lineCities) > 1) $suppressed = array_values(array_unique(array_merge($suppressed, array_slice($lineCities, 0, -1))));
                            $origins[] = $chosen;
                        }
                    }
                }
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
                    if (preg_match('/\(([^)]*)\)/u', $tail, $pm)) {
                        $inside = $pm[1] ?? '';
                        $inner = $this->collectCitiesInText($inside, $cityPattern, $cityLexicon);
                        if (!empty($inner)) {
                            $cities = $inner;
                            $suppressed[] = $first;
                        } else {
                            $more = $this->collectCitiesInText($tail, $cityPattern, $cityLexicon);
                            foreach ($more as $c) if (!in_array($c, $cities, true)) $cities[] = $c;
                        }
                    } else {
                        $more = $this->collectCitiesInText($tail, $cityPattern, $cityLexicon);
                        foreach ($more as $c) if (!in_array($c, $cities, true)) $cities[] = $c;
                    }
                }
                foreach ($cities as $c) $destinations[] = $c;
            }
        }

        // 2.1) تخلیه
        if (preg_match_all('/\bتخلیه\b\s*(?:[:：\-–—]\s*)?(?:شهر|استان)?\s*\b(' . $cityPattern . ')\b(?P<tail>[^\n]*)/u', $segment, $mUnload, PREG_SET_ORDER)) {
            foreach ($mUnload as $one) {
                $first = $this->toCanonicalCity($one[1], $cityLexicon) ?? trim($one[1]);
                $tail  = trim($one['tail'] ?? '');
                $cities = [$first];
                if ($tail !== '') {
                    if (preg_match('/\(([^)]*)\)/u', $tail, $pm)) {
                        $inside = $this->collectCitiesInText($pm[1] ?? '', $cityPattern, $cityLexicon);
                        if (!empty($inside)) {
                            $cities = $inside;
                            $suppressed[] = $first;
                        } else {
                            $more = $this->collectCitiesInText($tail, $cityPattern, $cityLexicon);
                            foreach ($more as $c) if (!in_array($c, $cities, true)) $cities[] = $c;
                        }
                    } else {
                        $more = $this->collectCitiesInText($tail, $cityPattern, $cityLexicon);
                        foreach ($more as $c) if (!in_array($c, $cities, true)) $cities[] = $c;
                    }
                }
                $destinations[] = end($cities);
            }
        }

        // بلوک «مقصد/مقصدها»
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
                $hasPhone = preg_match('/0?9\d{9}/u', $lineTrim) || preg_match('/(?<!\d)0\d{9,11}(?!\d)/u', $lineTrim);
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
        $destinations = array_values(array_unique(array_filter($destinations)));
        $suppressed   = array_values(array_unique(array_filter($suppressed)));

        // 3) توزیع نقش مبدا/مقصد
        if (!empty($origins)) {
            $remaining = array_values(array_filter(
                $segmentCitiesAll,
                fn($c) => !in_array($c, $origins, true) && !in_array($c, $suppressed, true)
            ));
            $destinations = array_values(array_unique(array_merge($destinations, $remaining)));
        } else {
            if (!empty($destinations)) {
                if (preg_match('/\b(?:به|تا|ب|مقصد|مقصدها|تخلیه)\b/u', $segment, $kw, PREG_OFFSET_CAPTURE)) {
                    $bytePos = $kw[0][1];
                    $left    = substr($segment, 0, $bytePos);
                    $leftCities = $this->collectCitiesOrdered($left, $cityPattern, $cityLexicon);
                    $leftCities = array_values(array_filter($leftCities, fn($c) => !in_array($c, $suppressed, true)));
                    if (!empty($leftCities)) $origins[] = end($leftCities);
                }
                if (empty($origins) && $contextOrigin) $origins[] = $contextOrigin;
                if (empty($origins)) {
                    $remaining = array_values(array_filter(
                        $segmentCitiesAll,
                        fn($c) => !in_array($c, $destinations, true) && !in_array($c, $suppressed, true)
                    ));
                    if (!empty($remaining)) $origins[] = $remaining[0];
                }
            }
        }

        // fallback‌های نهایی با احتیاط
        $hasDangling = $this->hasDanglingTo($segment);
        if (empty($origins) && empty($destinations) && count($segmentCitiesAll) >= 2) {
            $origins[] = $segmentCitiesAll[0];
            $destinations = array_slice($segmentCitiesAll, 1);
        } elseif (empty($origins) && empty($destinations) && count($segmentCitiesAll) === 1) {
            if ($hasRouteKW && $hasDangling) {
                // در حالت «بهِ آویزان»، شهر تنها را مقصد نگذار
            } else {
                $destinations[] = $segmentCitiesAll[0];
            }
        }

        $origins      = array_values(array_unique(array_filter($origins)));
        $destinations = array_values(array_unique(array_filter($destinations, fn($d) => !in_array($d, $origins, true))));

        return ['origins' => $origins, 'destinations' => $destinations];
    }

    // --- عنوان
    private function extractTitle(string $text): ?string
    {
        if (preg_match('/^[^\S\n]*بار\b\s*(?:[:：\-–—]\s*)?([^\n]+)/um', $text, $m)) {
            $payload = $this->cleanTitlePayload($m[1]);
            $title = $this->titleFromPayload($payload);
            if ($title) return $title;
        }
        if (preg_match('/^[^\S\n]*نوع\s*بار\b\s*[:：]\s*([^\n]+)/um', $text, $m2)) {
            $payload = $this->cleanTitlePayload($m2[1]);
            $title = $this->titleFromPayload($payload);
            if ($title) return $title;
        }
        return $this->titleFromPayload($this->cleanTitlePayload($text));
    }

    private function cleanTitlePayload(string $s): string
    {
        $s = preg_replace('/:[a-z0-9_]+:/iu', ' ', $s);
        $s = preg_replace('/[\[\]\(\)]/u', ' ', $s);
        return preg_replace('/[ \t\x{00A0}]+/u', ' ', trim($s));
    }

    private function titleFromPayload(string $payload): ?string
    {
        $found = [];
        foreach ($this->cargoWords as $c) {
            if (mb_stripos($payload, $c) !== false) $found[] = $this->aliasTitle($c);
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
        return $norm ? implode('، ', $norm) : null;
    }

    private function makeDescription($fleet, $origin, $destination, $title, $price, $raw)
    {
        $pieces = ['درخواست حمل'];
        if ($fleet)        $pieces[] = "با $fleet";
        if ($origin)       $pieces[] = "از $origin";
        if ($destination)  $pieces[] = "به $destination";
        if ($title)        $pieces[] = "برای $title";
        if ($price)        $pieces[] = "کرایه: $price";
        return implode('، ', $pieces);
    }

    private function simpleCityToCity(string $text, string $cityPattern, array $cityLexicon): ?array
    {
        if (preg_match("/\b($cityPattern)\b\s*(?:به|-|تا)\s*\b($cityPattern)\b/u", $text, $m)) {
            $o = $this->toCanonicalCity($m[1], $cityLexicon) ?? trim($m[1]);
            $d = $this->toCanonicalCity($m[2], $cityLexicon) ?? trim($m[2]);
            return [$o, $d];
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
        return back()->with('success', $counter . 'بار ثبت شد');
    }
    public function storeCargoSmart($origin, $originState, $destination, $destinationState, $mobileNumber, $description, $fleet, $title, &$counter, $cargoId)
    {
        // return dd($origin);
        if (!strlen(trim($origin)) || $origin == null || $origin == 'null' || !strlen(trim($destination)) || $destination == null || $destination == 'null' || !strlen($fleet) || !strlen($mobileNumber))
            return;

        substr($mobileNumber, 0, 1) !== '0' ? $mobileNumber = '0' . $mobileNumber : $mobileNumber;

        $cargoPattern = '';

        try {
            $cargoPattern = $origin . $destination . $mobileNumber . $fleet;

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
                ->where('id', $originState)
                ->first();

            $destinationCity = ProvinceCity::where('name', 'like', '%' . $destination)
                ->where('id', $destinationState)
                ->first();
            // return dd($originCity);
            // Log::alert($destinationState);
            // Log::alert($destinationState);

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
            // return dd($load)

            if (isset($load->id)) {

                $counter++;

                if (isset($fleet_id->id)) {
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
