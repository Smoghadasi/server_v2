<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoreLoadController extends Controller
{
    /** واژه‌های باری (برای عنوان) که نباید ناوگان حساب شوند */
    private array $cargoWords = [

        'سیب',
        'گلابی',
        'پرتقال',
        'نارنگی',
        'لیمو ترش',
        'گریپ‌فروت',
        'نارنج',
        'هلو',
        'شلیل',
        'آلو',
        'گیلاس',
        'آلبالو',
        'زردآلو',
        'انگور',
        'انبه',
        'موز',
        'آناناس',
        'پاپایا',
        'نارگیل',
        'خرما',
        'انجیر',
        'کشمش',
        'سنجد',
        'تمشک',
        'شاه‌توت',
        'توت‌فرنگی',
        'خرمالو',
        'خیار',
        'خیار چنبر',
        'گوجه فرنگی',
        'بادمجان',
        'فلفل دلمه‌ای',
        'فلفل تند',
        'کدو سبز',
        'کدو حلوایی',
        'کدو تنبل',
        'بامیه',
        'هندوانه',
        'خربزه',
        'طالبی',
        'گرمک',
        'فلفل سبز',
        'فلفل قرمز',
        'فلفل دلمه‌ای رنگی',
        'گندم',
        'جو',
        'ذرت',
        'برنج',
        'علوفه',
        'یونجه',
        'بذر',
        'کود شیمیایی',
        'کود مرغ',
        'کود حیوانی',
        'لبنیات',
        'گوشت و مرغ',
        'خشکبار',
        'آرد',
        'شکر',
        'تخم مرغ',
        'نوشابه',
        'میوه',
        'سبزیجات',
        'صیفی‌جات',
        'نهال',
        'اثاثیه منزل',
        'اثات منزل',
        'مبلمان',
        'کلاف مبل',
        'صندلی',
        'روکش صندلی',
        'وسایل منزل',
        'وسایل اداری',
        'وسایل فروشگاهی',
        'لوازم کابینت',
        'لوازمات',
        'یراق و ابزار آلات',
        'ابزار صنعتی',
        'لوازم آزمایشگاهی',
        'تجهیزات کارگاهی',
        'چادر',
        'پالت',
        'بشکه',
        'کانتینر',
        'دمپایی',
        'پوست گاو',
        'پوست گوسفند',
        'پووست گاو',
        'پووست گوسفند',
        'جعبه خالی',
        'نصف بار',
        'میلگرد',
        'تیرآهن',
        'نبشی',
        'ناودانی',
        'شمش فولادی',
        'تسمه فولادی',
        'مفتول',
        'ورق سیاه',
        'ورق روغنی',
        'ورق گالوانیزه',
        'ورق رنگی',
        'ورق استیل',
        'پروفیل',
        'لوله فولادی',
        'ریل فولادی',
        'توری فلزی',
        'پیچ فولادی',
        'مهره فولادی',
        'قطعات فلزی',
        'قطعات ریختگی',
        'چرخ‌دنده',
        'یاتاقان',
        'آهن',
        'چوب',
        'چوب راش',
        'سنگ',
        'سنگ ساختمانی',
        'کاشی',
        'کاشی و سرامیک',
        'سیمان',
        'گچ',
        'ماسه',
        'شن',
        'بلوک',
        'آجر',
        'پوکه معدنی',
        'ایزوگام',
        'بارگیری ساعت ۲',
        'بارگیری ساعت دو',
        'بارگیری ساعت ۳',
        'بارگیری ساعت سه',
        'بارگیری ساعت ۴',
        'بارگیری ساعت چهار',
        'بارگیری ساعت ۵',
        'بارگیری ساعت پنج',
        'بارگیری ساعت ۶',
        'بارگیری ساعت شش',
        'بارگیری ساعت ۷',
        'بارگیری ساعت هفت',
        'بارگیری ساعت ۸',
        'بارگیری ساعت هشت',
        'بارگیری ساعت ۹',
        'بارگیری ساعت نه',
        'بارگیری ساعت ۱۰',
        'بارگیری ساعت ده',
        'بارگیری ساعت ۱۱',
        'بارگیری ساعت یازده',
        'بارگیری ساعت ۱۲',
        'بارگیری ساعت دوازده',
        'بارگیری ساعت ۱۳',
        'بارگیری ساعت سیزده',
        'بارگیری ساعت ۱۴',
        'بارگیری ساعت چهارده',
        'بارگیری ساعت ۱۵',
        'بارگیری ساعت پانزده',
        'بارگیری ساعت ۱۶',
        'بارگیری ساعت شانزده',
        'بارگیری ساعت ۱۷',
        'بارگیری ساعت هفده',
        'بارگیری ساعت ۱۸',
        'بارگیری ساعت هجده',
        'بارگیری ساعت ۱۹',
        'بارگیری ساعت نوزده',
        'بارگیری ساعت ۲۰',
        'بارگیری ساعت بیست',
        'بارگیری ساعت ۲۱',
        'بارگیری ساعت بیست‌ویک',
        'بارگیری ساعت ۲۲',
        'بارگیری ساعت بیست‌ودو',
        'بارگیری ساعت ۲۳',
        'بارگیری ساعت بیست‌وسه',
        'بارگیری ساعت ۲۴',
        'بارگیری ساعت بیست‌وچهار',
        'بارگیری = ساعت ۲',
        'بارگیری = ساعت دو',
        'بارگیری = ساعت ۳',
        'بارگیری = ساعت سه',
        'بارگیری = ساعت ۴',
        'بارگیری = ساعت چهار',
        'بارگیری = ساعت ۵',
        'بارگیری = ساعت پنج',
        'بارگیری = ساعت ۶',
        'بارگیری = ساعت شش',
        'بارگیری = ساعت ۷',
        'بارگیری = ساعت هفت',
        'بارگیری = ساعت ۸',
        'بارگیری = ساعت هشت',
        'بارگیری = ساعت ۹',
        'بارگیری = ساعت نه',
        'بارگیری = ساعت ۱۰',
        'بارگیری = ساعت ده',
        'بارگیری = ساعت ۱۱',
        'بارگیری = ساعت یازده',
        'بارگیری = ساعت ۱۲',
        'بارگیری = ساعت دوازده',
        'بارگیری = ساعت ۱۳',
        'بارگیری = ساعت سیزده',
        'بارگیری = ساعت ۱۴',
        'بارگیری = ساعت چهارده',
        'بارگیری = ساعت ۱۵',
        'بارگیری = ساعت پانزده',
        'بارگیری = ساعت ۱۶',
        'بارگیری = ساعت شانزده',
        'بارگیری = ساعت ۱۷',
        'بارگیری = ساعت هفده',
        'بارگیری = ساعت ۱۸',
        'بارگیری = ساعت هجده',
        'بارگیری = ساعت ۱۹',
        'بارگیری = ساعت نوزده',
        'بارگیری = ساعت ۲۰',
        'بارگیری = ساعت بیست',
        'بارگیری = ساعت ۲۱',
        'بارگیری = ساعت بیست‌ویک',
        'بارگیری = ساعت ۲۲',
        'بارگیری = ساعت بیست‌ودو',
        'بارگیری = ساعت ۲۳',
        'بارگیری = ساعت بیست‌وسه',
        'بارگیری = ساعت ۲۴',
        'بارگیری = ساعت بیست‌وچهار',
        'بارگیری شنبه',
        'بارگیری یک‌شنبه',
        'بارگیری دوشنبه',
        'بارگیری سه‌شنبه',
        'بارگیری چهارشنبه',
        'بارگیری پنج‌شنبه',
        'بارگیری جمعه',
        'بارگیری امروز صبح',
        'بارگیری امروز بعد از ظهر',
        'بارگیری امروز عصر',
        'بارگیری امروز شب',
        'بارگیری فردا صبح',
        'بارگیری فردا بعد از ظهر',
        'بارگیری فردا عصر',
        'بارگیری فردا شب',
        'بارگیری امشب',
        'آماده بارگیری',
        'بارگیری دوجا',
        'بارگیری سه‌جا',
        'بارگیری چندجا',
        'فوری',
        'فردا بارگیری',
        'چادر الزامی است',
        'وزن بار ۱ کیلو',
        'وزن بار ۲ کیلو',
        'وزن بار ۳ کیلو',
        'وزن بار ۵ کیلو',
        'وزن بار ۱۰ کیلو',
        'وزن بار ۲۰ کیلو',
        'وزن بار ۳۰ کیلو',
        'وزن بار ۵۰ کیلو',
        'وزن بار ۱۰۰ کیلو',
        'وزن بار ۵۰۰ کیلو',
        'وزن بار ۱۰۰۰ کیلو',
        'وزن بار ۲۰۰۰ کیلو',
        'وزن بار ۵۰۰۰ کیلو',
        'وزن بار ۱۰۰۰۰ کیلو',
        'وزن بار ۱ تن',
        'وزن بار ۲ تن',
        'وزن بار ۳ تن',
        'وزن بار ۵ تن',
        'وزن بار ۱۰ تن',
        'وزن بار ۲۰ تن',
        'وزن بار ۳۰ تن',
        'وزن بار ۵۰ تن',
        'وزن بار ۱۰۰ تن',
        'وزن ۱ کیلو',
        'وزن ۲ کیلو',
        'وزن ۳ کیلو',
        'وزن ۵ کیلو',
        'وزن ۱۰ کیلو',
        'وزن ۲۰ کیلو',
        'وزن ۳۰ کیلو',
        'وزن ۵۰ کیلو',
        'وزن ۱۰۰ کیلو',
        'وزن ۵۰۰ کیلو',
        'وزن ۱۰۰۰ کیلو',
        'وزن ۲۰۰۰ کیلو',
        'وزن ۵۰۰۰ کیلو',
        'وزن ۱۰۰۰۰ کیلو',
        'وزن ۱ تن',
        'وزن ۲ تن',
        'وزن ۳ تن',
        'وزن ۵ تن',
        'وزن ۱۰ تن',
        'وزن ۲۰ تن',
        'وزن ۳۰ تن',
        'وزن ۵۰ تن',
        'وزن ۱۰۰ تن'
    ];

    /** نگاشت هم‌ارزی برخی واژه‌ها برای عنوان (مثلا روبار→روباری) */
    private array $titleAliases = [
        'روبار' => 'روباری',
    ];

    public function getLoadFromTel(Request $request)
    {
        // return $request;
        $raw = (string) $request->input('text', '');
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

            foreach ($usedFleetList as $fleetTitle) {
                foreach ($usedOrigins as $originCity) {
                    foreach ($usedDestinations as $destCity) {
                        $record = [
                            'fleet'           => $fleetTitle,
                            'fleet_id'        => $fleetTitle ? ($fleetsByTitle[$fleetTitle] ?? null) : null,
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
}
