<?php

namespace App\Http\Controllers;

use App\Models\CargoConvertList;
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
                return view('admin.processingUnit.index', compact('cargo', 'countOfCargos', 'users'));
            }

            // در نهایت بار فعلی رو بده به اپراتور
            $cargo->operator_id = $userId;
            $cargo->save();
            return view('admin.processingUnit.index', compact('cargo', 'countOfCargos', 'users'));
        }

        // ۴. اگر هیج باری نبود → برگرد به داشبورد
        return redirect(url('dashboard'))->with('danger', 'هیچ باری وجود ندارد');
    }

    public function processingUnit(CargoConvertList $cargo)
    {
        $cargo->processingUnit = 1;
        $cargo->save();
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
            ]);

            $results[] = $item;
        }
        $cargo->processingUnit = 0;
        $cargo->status = 1;
        $cargo->save();

        return back()->with('success', 'ثبت شد');
    }
}
