<?php

namespace App\Http\Controllers;

use App\Models\CargoConvertList;
use App\Models\Equivalent;
use App\Models\OperatorCargoListAccess;
use App\Models\PrompAi;
use App\Models\Setting;
use App\Models\StoreCargoOperator;
use App\Services\CargoJsonSaver;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProcessingUnitController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        $countOfCargos = CargoConvertList::where('operator_id', 0)
            ->where('isBlocked', 0)
            ->where('isDuplicate', 0)
            ->count();
        $users = UserController::getOnlineAndOfflineUsers();

        // ۱. پیدا کردن باری که قبلاً به اپراتور تخصیص داده شده
        $cargo = CargoConvertList::where([
            ['operator_id', $userId],
            ['processingUnit', 1],
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
                        ['processingUnit', 1],
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
                    ['processingUnit', 1],
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
                        return view('admin.processingUnit.index', compact('cargo', 'countOfCargos', 'users'));
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
                        ['processingUnit', 1],
                        ['isBlocked', 0],
                        ['isDuplicate', 0],
                    ])
                    ->oldest('id')
                    ->first();

                if ($newCargo) {
                    $newCargo->operator_id = $userId;
                    $newCargo->save();
                    $cargo = $newCargo;
                    return view('admin.processingUnit.index', compact('cargo', 'countOfCargos', 'users'));
                }
            }

            // در نهایت بار فعلی رو بده به اپراتور
            $cargo->operator_id = $userId;
            $cargo->save();
            return view('admin.processingUnit.index', compact('cargo', 'countOfCargos', 'users'));
        }

        // ۴. اگر هیج باری نبود → برگرد به داشبورد
        return redirect(url('dashboard'))->with('danger', 'هیچ باری وجود ندارد');
    }
    public function indexVIP()
    {
        $userId = auth()->id();
        $countOfCargos = CargoConvertList::where('operator_id', 0)
            ->where('isBlocked', 0)
            ->where('isDuplicate', 0)
            ->count();
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
        $users = UserController::getOnlineAndOfflineUsers();

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
                        $cargo->save();
                        return view('admin.processingUnit.indexVIP', compact('cargo', 'countOfCargos', 'users'));

                        // return $this->dataConvert($cargo);
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
                    $newCargo->save();
                    return view('admin.processingUnit.indexVIP', compact('cargo', 'countOfCargos', 'users'));

                    // return $this->dataConvert($newCargo);
                }
            }

            // در نهایت بار فعلی رو بده به اپراتور
            $cargo->operator_id = $userId;
            $cargo->save();
            return view('admin.processingUnit.indexVIP', compact('cargo', 'countOfCargos', 'users'));

            // return $this->dataConvert($cargo);
        }

        // ۴. اگر هیج باری نبود → برگرد به داشبورد
        return redirect(url('dashboard'))->with('danger', 'هیچ باری وجود ندارد');
    }
    public function indexVIP2()
    {
        $userId = auth()->id();
        $countOfCargos = CargoConvertList::where('operator_id', 0)
            ->where('isBlocked', 0)
            ->where('isDuplicate', 0)
            ->where('processingUnit', 1)
            ->count();
        // ۱. پیدا کردن باری که قبلاً به اپراتور تخصیص داده شده
        $cargo = CargoConvertList::where([
            ['operator_id', $userId],
            ['processingUnit', 1],
            ['status', 0],
            ['isBlocked', 0],
            ['isDuplicate', 0],
        ])
            ->latest('id')
            ->first();
        $users = UserController::getOnlineAndOfflineUsers();

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
                        ['processingUnit', 1],
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
                    ['processingUnit', 1],
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
                        return view('admin.processingUnit.indexVIPv2', compact('cargo', 'countOfCargos', 'users'));

                        // return $this->dataConvert($cargo);
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
                        ['processingUnit', 1],
                        ['isDuplicate', 0],
                    ])
                    ->oldest('id')
                    ->first();

                if ($newCargo) {
                    $newCargo->operator_id = $userId;
                    $newCargo->save();
                    return view('admin.processingUnit.indexVIPv2', compact('cargo', 'countOfCargos', 'users'));

                    // return $this->dataConvert($newCargo);
                }
            }

            // در نهایت بار فعلی رو بده به اپراتور
            $cargo->operator_id = $userId;
            $cargo->save();
            return view('admin.processingUnit.indexVIPv2', compact('cargo', 'countOfCargos', 'users'));

            // return $this->dataConvert($cargo);
        }

        // ۴. اگر هیج باری نبود → برگرد به داشبورد
        return redirect(url('dashboard'))->with('danger', 'هیچ باری وجود ندارد');
    }

    public function documentSmartCargo()
    {
        $setting = Setting::first();
        $today = Carbon::today();

        $users = PrompAi::whereDate('created_at', $today)->get();


        return view('admin.processingUnit.document', compact('setting', 'users'));
    }

    public function updateDocumentSmartCargo(Request $request, $settingId)
    {
        $setting = Setting::find($settingId);
        $setting->document_smart_cargo = $request->document_smart_cargo;
        $setting->save();
        return true;
    }

    public function convertSmart($cargo)
    {
        $blocks = [];
        $text = $cargo->cargo;

        // شماره تماس‌ها
        preg_match_all('/(09\d{9})/', $text, $phones);
        $phones = $phones[1] ?? [];

        // مبدأ و مقصد
        preg_match_all('/(?:مبدا|از)\s*[:\-]?\s*([\p{Arabic}\s‌]+?)\s*(?:به|⬅️|ب مقصد|مقصد)\s*[:\-]?\s*([\p{Arabic}\s‌()]+)(?=\s|$)/u', $text, $routes, PREG_SET_ORDER);

        // نوع بار
        preg_match_all('/بار[:\-]?\s*([\p{Arabic}\s‌A-Za-z0-9\/]+)(?=\s|$)/u', $text, $cargoMatches);
        $cargoList = $cargoMatches[1] ?? [];

        // ناوگان
        preg_match('/(نیسان|خاور(?: مسقف| روباز)?|کمپرسی|تریل(?:ی|ر)|کفی|ده چرخ|جفت|تک|ترانزیت|کامیون(?:ت| سرپوشیده| روباز)?)/u', $text, $fleetMatch);
        $fleet = isset($fleetMatch[1]) ? $fleetMatch[1] : '—';

        // اصلاح خودکار شهرها (مثلاً نیکشهر → نیک شهر)
        $fixSpacing = function ($str) {
            return preg_replace('/([اآبپتثجچحخدذرزسشصضطظعغفقکگلمنوهی])\s{0,}([شهر|آباد|قند|ستان|ده])/', '$1 $2', trim($str));
        };

        // چند مسیر در متن = چند بلاک
        foreach ($routes as $i => $route) {
            $origin = trim($route[1]);
            $destination = trim($route[2]);
            $origin = $fixSpacing($origin);
            $destination = $fixSpacing($destination);

            $cargo = isset($cargoList[$i]) ? $cargoList[$i] : '—';

            $details = [];

            // وزن / کرایه
            if (preg_match('/(?:وزن|تنی|کرایه)\s*[:\-]?\s*([\d\/,\.]+(?:\s*میلیون)?)/u', $text, $m))
                $details[] = "تنی " . $m[1];

            // بارگیری فوری
            if (preg_match('/(بارگیری\s*(الان|فوری|تا\s*\d+\s*شب))/u', $text, $m))
                $details[] = $m[1];

            // پرداخت کرایه
            if (preg_match('/پرداخت\s*کرایه\s*([\p{Arabic}\d\s]+)/u', $text, $m))
                $details[] = "پرداخت کرایه " . trim($m[1]);

            $cargoTitle = trim($cargo);
            if (!empty($details)) $cargoTitle .= '، ' . implode('، ', $details);

            foreach ($phones as $phone) {
                $blocks[] = "START\n" .
                    "$fleet\n" .
                    "از $origin به $destination\n" .
                    "عنوان بار: $cargoTitle\n" .
                    "$phone\nEND";
            }
        }

        // اگر مسیر مشخص نشد ولی شماره تماس هست
        if (empty($blocks) && !empty($phones)) {
            foreach ($phones as $phone) {
                $blocks[] = "START\n—\n—\nعنوان بار: —\n$phone\nEND";
            }
        }
        $countOfCargos = CargoConvertList::where('operator_id', 0)
            ->where('isBlocked', 0)
            ->where('isDuplicate', 0)
            ->count();
        $users = UserController::getOnlineAndOfflineUsers();
        $clear = implode("\n\n", $blocks);

        return view('admin.processingUnit.index', compact('cargo', 'countOfCargos', 'users', 'clear'));


        // // 🧩 تست با ورودی نمونه
        // $output = extractFreightData($input);
        // echo $output;
    }

    public function processingUnit($cargoId)
    {
        $cargo = CargoConvertList::find($cargoId);
        if ($cargo) {
            $cargo->processingUnit = 1;
            $cargo->operator_id = 0;
            $cargo->status = 0;
            $cargo->save();
            return back()->with('success', 'ارسال شد');
        }
        return back()->with('success', 'ارسال شد');
    }

    public function update(Request $request, $cargoId)
    {
        $cargo = CargoConvertList::find($cargoId);

        if (!$cargo) {
            return back();
        }

        $text = $request->input('cargo');
        preg_match_all('/START\s*(.*?)\s*END/su', $text, $matches);

        if (empty($matches[1])) {
            return response()->json(['message' => 'هیچ داده‌ای یافت نشد.'], 400);
        }

        $contents = array_map('trim', $matches[1]);

        if ($request->automatic == 1) {
            // $dataConvertPlus = new DataConvertPlusController();
            $storedCount = 0;
            foreach ($contents as $clean) {
                $this->analyzeCode($clean);
                $storedCount++;

                // گزارش بار ها بر اساس اپراتور
                $persian_date = gregorianDateToPersian(date('Y/m/d', time()), '/');
                $storeCargoOperator = StoreCargoOperator::firstOrNew([
                    'user_id' => Auth::id(),
                    'persian_date' => $persian_date,
                ]);

                $storeCargoOperator->count = ($storeCargoOperator->count ?? 0) + 1;
                $storeCargoOperator->save();
                // $dataConvertPlus->dataConvert($clean, 1, $cargo->id);
            }
            $cargo = CargoConvertList::find($cargo->id);
            $cargo->status = true;
            $cargo->save();
            return back()->with('success', $storedCount . ' بار ثبت شد');
        }
        $storedCount = 0;
        foreach ($contents as $clean) {
            CargoConvertList::create([
                'cargo_orginal' => $clean,
                'cargo' => $clean,
                'channel' => $cargo->channel,
                'bot_number' => $cargo->bot_number,
                'isProcessingControl' => 1,
            ]);
            $storedCount++;
        }

        $cargo->update([
            'processingUnit' => 0,
            'status' => 1,
        ]);

        return back()->with('success', $storedCount . ' تا ثبت شد');
    }

    public function analyzeCode($text)
    {
        $result = [];
        // Define regex patterns
        $patterns = [
            'fleet'        => "/ناوگان:\\s*([^\n]+)/u",
            'origin'       => "/از:\\s*([^\n]+)/u",
            'destination'  => "/به:\\s*([^\n]+)/u",
            'cargo_title'  => "/عنوان بار:\\s*([^\n]+)/u",
            'phone'        => "/Tell:\\s*(\\d{11})/u"
        ];

        foreach ($patterns as $key => $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $value = trim($matches[1]);

                // Special handling for phone numbers
                if ($key === 'phone') {
                    // فقط اولین شماره ۱۱ رقمی را استخراج کن
                    if (preg_match('/\d{11}/', $value, $m)) {
                        $value = $m[0];
                    }
                }

                $result[$key] = $value;
            } else {
                $result[$key] = null; // fallback if not found
            }
        }
        $request = new Request($result);
        $this->storeFromJson($request);

        // return dd(($result['fleet']));
    }

    public function storeFromJson(Request $request)
    {
        // 1) خواندن payload (JSON واقعی یا فرم)
        $payload = $request->json()->all();
        if (empty($payload)) {
            $payload = $request->all();
        }

        // 2) استخراج items:
        // - اگر data آرایه بود → همون
        // - اگر کل payload آرایه از آبجکت‌ها بود → همون
        // - اگر payload یک آبجکت منفرد بود → تبدیل به آرایهٔ یک‌عضوی
        $items = [];
        if (isset($payload['data']) && is_array($payload['data'])) {
            $items = $payload['data'];
        } elseif (is_array($payload) && $this->isListOfAssoc($payload)) {
            $items = $payload;
        } elseif (is_array($payload)) {
            $items = [$payload];
        }

        // اگر باز هم خالی است، به کاربر بگو
        if (empty($items)) {
            return response()->json([
                'ok' => false,
                'message' => 'No items found. Send either {"data":[...]} or a single JSON object.',
                'hint' => 'Raw body should be a JSON object or data:[objects].',
            ], 422, [], JSON_UNESCAPED_UNICODE);
        }

        // 3) نگاشت کلیدها و نرمال‌سازی متن‌های فارسی
        // $normalized = array_map([$this, 'normalizeIncomingItem'], $items);

        // 4) ذخیره
        $saver = new CargoJsonSaver();
        // return dd($items);
        $result = $saver->saveFromJson($items);
        $storedCount = $result['stored'] ?? 0;

        return $storedCount;
    }

    private function isListOfAssoc(array $arr): bool
    {
        // true اگر آرایه‌ای از آبجکت‌های انجمنی باشد
        $i = 0;
        foreach ($arr as $k => $v) {
            if ($k !== $i) return false; // اندیس‌ها 0..n
            if (!is_array($v)) return false;
            $i++;
        }
        return $i > 0;
    }

    private function normalizeIncomingItem(array $item): array
    {
        // مپ کلیدها: phone -> phoneNumber ، cargo_title -> title
        $map = [
            'phone'        => 'phoneNumber',
            'mobile'       => 'phoneNumber',
            'mobileNumber' => 'phoneNumber',
            'cargo_title'  => 'title',
            'cargoTitle'   => 'title',
            'fleets'       => 'fleet',
            'origins'      => 'origin',
            'destinations' => 'destination',
        ];
        foreach ($map as $from => $to) {
            if (array_key_exists($from, $item) && !array_key_exists($to, $item)) {
                $item[$to] = $item[$from];
            }
        }

        // اسپلیت فارسی برای سه فیلد اصلی در صورت string بودن
        foreach (['fleet', 'origin', 'destination'] as $k) {
            if (isset($item[$k]) && !is_array($item[$k])) {
                $item[$k] = $this->splitPersianList((string)$item[$k], $k);
            }
        }

        // تمیز کردن title/description
        if (isset($item['title']) && is_string($item['title'])) {
            $item['title'] = $this->stripNoise($item['title']);
        }
        if (isset($item['description']) && is_string($item['description'])) {
            $item['description'] = $this->stripNoise($item['description']);
        }

        return $item;
    }


    private function splitPersianList(string $s, string $field): array
    {
        $orig = $s;
        $s = $this->normalizeFa($s);

        // پاک کردن پیشوندهای توضیحی در destination
        if ($field === 'destination') {
            $s = preg_replace('/^(?:مقاصد\s*مختلف\s*شامل|مقاصد\s*شامل|مقاصد|شامل)\s*/u', '', $s);
        }

        // حذف کلمات عمومی
        $s = preg_replace('/\b(?:و\s*حومه|حومه|اطراف|شهرستان)\b/u', ' ', $s);

        // جداکننده‌ها: «،» «,» «/» «|» « و »
        $parts = preg_split('/\s*(?:،|,|\/|\||و)\s*/u', $s) ?: [];

        // فیلتر خالی‌ها
        $parts = array_values(array_filter(array_map('trim', $parts), fn($x) => $x !== ''));

        // پسا-پردازش برای fleet: «تریلی کفی» → «کفی»، «لبه» → «لبه‌دار»
        if ($field === 'fleet') {
            $parts = array_map(function ($t) {
                $t = preg_replace('/^تریلی\s+/u', '', $t);
                if ($t === 'لبه') $t = 'لبه‌دار';
                return $t;
            }, $parts);
        }

        return $parts ?: [$this->normalizeFa($orig)];
    }

    private function stripNoise(string $s): string
    {
        $s = $this->normalizeFa($s);
        // حذف تگ‌های توضیحی رایج
        $s = preg_replace('/\b(?:ظرفیت(?:‌|\s)های\s*مختلف|برای\s*مقاصد\s*متعدد|بارهای\s*جفت\s*و\s*تک)\b/u', '', $s);
        return trim(preg_replace('/\s{2,}/u', ' ', $s));
    }

    private function normalizeFa(string $s): string
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
            '۹' => '9',
        ];
        $s = strtr($s, $map);
        return trim(preg_replace('/[ \t\x{00A0}]+/u', ' ', $s));
    }
}
