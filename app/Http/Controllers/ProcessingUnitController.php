<?php

namespace App\Http\Controllers;

use App\Models\CargoConvertList;
use App\Models\Setting;
use Illuminate\Http\Request;

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

            // اگر دیکشنری داریم → دنبال اولین باری بگرد که یکی از کلماتش داخل بار هست
            $cargo = CargoConvertList::where([
                ['operator_id', 0],
                ['status', 0],
                ['processingUnit', 1],
                ['isBlocked', 0],
                ['isDuplicate', 0],
            ])
                ->oldest('id')
                ->first();

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
            $cargo->operator_id = $userId;
            $cargo->save();
            // return $this->convertSmart($cargo);
            return view('admin.processingUnit.index', compact('cargo', 'countOfCargos', 'users'));

            // اگر بار فعلی نبود، دنبال بار جدیدی که match کنه
            $cargo = CargoConvertList::where([
                ['operator_id', 0],
                ['status', 0],
                ['isBlocked', 0],
                ['processingUnit', 1],
                ['isDuplicate', 0],
            ])
                ->oldest('id')
                ->first();

            if ($cargo) {
                $cargo->operator_id = $userId;
                $cargo->save();
                // return $this->convertSmart($cargo);
                return view('admin.processingUnit.index', compact('cargo', 'countOfCargos', 'users'));
            }

            // در نهایت بار فعلی رو بده به اپراتور
            $cargo->operator_id = $userId;
            $cargo->save();
            // return $this->convertSmart($cargo->cargo);
            return view('admin.processingUnit.index', compact('cargo', 'countOfCargos', 'users'));
        }

        // ۴. اگر هیج باری نبود → برگرد به داشبورد
        return redirect(url('dashboard'))->with('danger', 'هیچ باری وجود ندارد');
    }

    public function documentSmartCargo()
    {
        $setting = Setting::first();
        return view('admin.processingUnit.document', compact('setting'));
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

    public function update(Request $request, CargoConvertList $cargo)
    {
        // متن کامل از درخواست
        $text = $request->input('cargo');

        // با regex، متن‌های بین START و END را پیدا می‌کنیم
        preg_match_all('/START\s*(.*?)\s*END/su', $text, $matches);

        // اگر هیچ متنی پیدا نشد
        if (empty($matches[1])) {
            return response()->json(['message' => 'هیچ داده‌ای یافت نشد.'], 400);
        }

        foreach ($matches[1] as $content) {
            // تمیز کردن خطوط اضافی
            $clean = trim($content);

            // ساخت رکورد جدید در جدول cargo_convert_list
            $item = CargoConvertList::create([
                'cargo_orginal' => $clean,
                'cargo' => $clean,
                'isProcessingControl' => 1,
            ]);

            $results[] = $item;
        }
        $cargo->processingUnit = 0;
        $cargo->status = 1;
        $cargo->save();

        return back()->with('success', 'ثبت شد');
    }
}
