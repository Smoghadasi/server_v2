<?php

namespace App\Services;

use App\Http\Controllers\AddressController;
use App\Models\BlockPhoneNumber;
use App\Models\Equivalent;
use App\Models\Fleet;
use App\Models\FleetLoad;
use App\Models\Load;
use App\Models\Owner;
use App\Models\ProvinceCity;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CargoJsonSaver
{
    /** چند دقیقه برای ضد تکرار */
    const DEDUP_MINUTES = 180;

    /** ورودی ممکن است string|array|null باشد → خروجی آرایه‌ی تمیز از استرینگ‌ها */
    private function asArrayOfStrings($value): array
    {

        if (is_array($value)) {
            return array_values(array_unique(array_filter(array_map(fn($v) => trim((string)$v), $value))));
        }
        $v = trim((string)$value);
        return $v !== '' ? [$v] : [];
    }

    /** نرمال‌سازی حروف و ارقام فارسی/عربی */
    private function normalize(string $s): string
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
        $s = preg_replace('/[ \t\x{00A0}]+/u', ' ', trim($s));
        return $s;
    }

    /** نرمال‌سازی موبایل: +98/0098 → 09xxxxxxxxx */
    private function normalizeMobile(?string $raw): ?string
    {
        if (!$raw) return null;
        $raw = $this->normalize($raw);
        // +98 / 0098
        if (preg_match('/^(?:\+?98|0098)\s*(9\d{9})$/', preg_replace('/\D+/', '', $raw), $m)) {
            return '0' . $m[1];
        }
        // 09xxxxxxxxx یا 9xxxxxxxxx
        $digits = preg_replace('/\D+/', '', $raw);
        if (strlen($digits) === 10 && $digits[0] === '9') return '0' . $digits;
        if (strlen($digits) === 11 && str_starts_with($digits, '09')) return $digits;
        // ثابت یا فرمت‌های دیگر را همان برگردان (برای همخوانی با DB شما)
        return $digits ?: null;
    }

    /** گرفتن ID ناوگان از طریق equivalents و سپس fleets؛ ورودی: نام/توکن ناوگان */
    private function resolveFleetId(string $fleetToken): ?array
    {
        $token = $this->normalize($fleetToken);
        if ($token === '') {
            return null;
        }

        // 1) equivalents (type=fleet) → original_word_id
        $eq = Equivalent::where('type', 'fleet')
            ->where('equivalentWord', $token)
            ->pluck('original_word_id')
            ->toArray();
        if (!empty($eq)) {
            return $eq;
        }

        // 2) fleets.title
        $f = Fleet::where('title', $token)
            ->pluck('id')
            ->toArray();

        if (!empty($f)) {
            return $f;
        }

        // 3) fallback: اگر هیچ نتیجه نبود، حالا می‌تونیم کلمات رو جدا کنیم
        $parts = explode(' ', $token);
        $ids = [];
        foreach ($parts as $part) {
            $ids = array_merge(
                $ids,
                Fleet::where('title', 'like', "%{$part}%")
                    ->pluck('id')
                    ->toArray()
            );
        }

        return !empty($ids) ? $ids : null;
    }




    /** والد/فرزند: انتخاب بهترین شهر از چند ID هم‌نام (ترجیحاً leaf) */
    private function pickBestCityId(array $ids): ?int
    {
        if (empty($ids)) return null;

        // parent map: id => parent_id
        $parents = ProvinceCity::pluck('parent_id', 'id')->toArray();

        $isAncestor = function ($a, $b) use (&$parents) {
            $guard = 0;
            $p = $parents[$b] ?? null;
            while ($p && $guard < 20) {
                if ($p == $a) return true;
                $p = $parents[$p] ?? null;
                $guard++;
            }
            return false;
        };

        // حذف هر ID که اجدادِ ID دیگر است → باقی می‌مانند leaf ها
        $candidates = $ids;
        foreach ($ids as $a) {
            foreach ($ids as $b) {
                if ($a === $b) continue;
                if ($isAncestor($a, $b)) {
                    $candidates = array_values(array_filter($candidates, fn($x) => $x !== $a));
                }
            }
        }
        return $candidates[0] ?? $ids[0];
    }

    /** گرفتن یک ID شهر از طریق equivalents و سپس province_cities (فقط cityهای parent_id != 0) */
    private function resolveCityId(string $cityToken): ?int
    {
        $token = $this->normalize($cityToken);
        if ($token === '') return null;
        // 1) equivalents (type=city) → original_word_id (id شهر)
        $eq = Equivalent::where('type', 'city')->where(
            'equivalentWord',
            $token
        )->first();
        if ($eq) return (int)$eq->original_word_id;

        // 2) province_cities exact (فقط شهر/بخش نه استان)
        $rows = ProvinceCity::where('parent_id', '!=', 0)->where('name', $token)->pluck('id')->all();

        if (!empty($rows)) return $this->pickBestCityId($rows);

        // 3) fallback: like
        $rows2 = ProvinceCity::where('parent_id', '!=', 0)->where('name', 'like', "%{$token}%")->pluck('id')->all();
        if (!empty($rows2)) return $this->pickBestCityId($rows2);

        return null;
    }

    /** ضدتکرار: بار اخیر با همان route+fleet+mobile */
    private function isDuplicate(?int $originId, ?int $destId, ?int $fleetId, ?string $mobile): bool
    {
        if (!$mobile || !$originId || !$destId || !$fleetId) return false;

        return Load::query()
            ->where('mobileNumberForCoordination', $mobile)
            ->where('origin_city_id', $originId)
            ->where('destination_city_id', $destId)
            ->where('created_at', '>=', now()->subMinutes(self::DEDUP_MINUTES))
            ->whereHas('fleetLoads', fn($q) => $q->where('fleet_id', $fleetId))
            ->exists();
    }

    /** ذخیره یک load + اتصال fleet_loads (بدون لاجیک اضافه) */
    private function persistLoad(array $payload, int $originId, int $destId, array $fleetIds)
    {
        try {
            return DB::transaction(function () use ($payload, $originId, $destId, $fleetIds) {
                $mobileNumber = $payload['phoneNumber'];
                $load              = new Load();
                $load->title       = $payload['title'] ?? 'بدون عنوان';
                $load->description = $payload['description'] ?? '';
                $load->cargoPattern = ($payload['origin'] ?? '') . ($payload['destination'] ?? '') . ($payload['phoneNumber'] ?? '') . implode(',', $fleetIds);

                $load->mobileNumberForCoordination = $payload['phoneNumber'] ?? null;
                $load->senderMobileNumber          = $payload['phoneNumber'] ?? null;
                $load->emergencyPhone              = $payload['phoneNumber'] ?? null;

                $load->origin_city_id       = $originId;
                $load->destination_city_id  = $destId;
                $load->fromCity = $this->getCityName($load->origin_city_id);
                $load->toCity = $this->getCityName($load->destination_city_id);
                $load->origin_state_id = AddressController::geStateIdFromCityId($load->origin_city_id);

                try {
                    $city = ProvinceCity::where('parent_id', '!=', 0)->find($load->origin_city_id);
                    if (isset($city->id)) {
                        $load->latitude = $city->latitude;
                        $load->longitude = $city->longitude;
                    }
                } catch (\Exception $exception) {
                    // return dd($exception);
                }


                // فیلدهای ساده‌ی متداول شما
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
                $load->loadMode  = 'outerCity';

                // تاریخ‌های شمسی—اگر هلسپر دارید، جایگزین کنید
                $load->date = gregorianDateToPersian(date('Y/m/d', time()), '/');
                $load->dateTime = now()->format('H:i:s');
                $load->loadingDate = gregorianDateToPersian(date('Y-m-d', time()), '-');
                $load->time      = time();

                $load->storeFor = ROLE_DRIVER;
                $load->status = ON_SELECT_DRIVER;
                $load->deliveryTime = 24;
                $load->save();
                foreach ($fleetIds as $fid) {
                    FleetLoad::create([
                        'load_id' => $load->id,
                        'fleet_id' => $fid,
                        'numOfFleets' => 1,
                        'userType' => $load->userType,
                    ]);
                }
                $load->fleets = FleetLoad::join('fleets', 'fleets.id', 'fleet_loads.fleet_id')
                    ->where('fleet_loads.load_id', $load->id)
                    ->select('fleet_id', 'userType', 'suggestedPrice', 'numOfFleets', 'pic', 'title')
                    ->get();

                $load->save();
                // return dd($load);
                return $load->id;
            });
        } catch (\Throwable $th) {
            // return dd($th);
            //throw $th;
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

    /**
     * ذخیره از JSON — هر آیتم می‌تواند چند ناوگان/چند مبدا/چند مقصد داشته باشد.
     * فرمت هر آیتم (نمونه):
     * [
     *   "fleet": "نیسان" | ["نیسان","لبه‌دار"],
     *   "origin": "یزد" | ["یزد","کرمان"],
     *   "destination": "تهران" | ["تهران","قزوین"],
     *   "phoneNumber": "0912xxxxxxx",
     *   "title": "تره‌بار",
     *   "description": "توضیح",
     *   "price": "23 میلیون" // اختیاری؛ فعلاً صرفاً در description قابل استفاده است
     * ]
     */
    public function saveFromJson(array $items, ?int $cargoId = null): array
    {
        $results = [
            'total_items'   => count($items),
            'stored'        => 0,
            'duplicates'    => 0,
            'blocked'       => 0,
            'failed'        => 0,
            'details'       => [], // برای گزارش هر آیتم
        ];
        foreach ($items as $idx => $item) {
            try {
                // 1) آماده‌سازی ورودی
                $fleets   = $this->asArrayOfStrings($item['fleet']);

                $origins  = $this->asArrayOfStrings($item['origin'] ?? $item['origins'] ?? []);
                $dests    = $this->asArrayOfStrings($item['destination'] ?? $item['destinations'] ?? []);
                $mobile   = $this->normalizeMobile($item['phone'] ?? '');
                $title   = $item['cargo_title'] ?? '';

                // بررسی طول شماره موبایل (باید 11 رقم باشد)
                if (!preg_match('/^\d{11}$/', $mobile)) {
                    $results['failed']++;
                    $results['details'][] = ['index' => $idx, 'status' => 'failed', 'reason' => 'failed_phone'];
                    continue;
                }
                // بلاکی؟
                if ($mobile && BlockPhoneNumber::where('phoneNumber', $mobile)->exists()) {
                    $results['blocked']++;
                    $results['details'][] = ['index' => $idx, 'status' => 'blocked', 'reason' => 'blocked_phone'];
                    continue;
                }

                // اگر هیچ fleet/origin/destination نداشت، سعی می‌کنیم حداقل یک‌کدام را پر کنیم
                if (empty($fleets))   $fleets  = [null]; // اجازه ذخیره بدون ناوگان (بعداً هم می‌شود اضافه کرد)
                if (empty($origins))  $origins = [null];
                if (empty($dests))    $dests   = [null];

                // 2) نگاشت ناوگان‌ها به ID
                $fleetIds = [];

                foreach ($fleets as $ft) {
                    if ($ft === null) {
                        continue;
                    }

                    $fid = $this->resolveFleetId($ft);

                    if ($fid === null) {
                        continue;
                    }

                    // اگر آرایه بود، ادغام کن؛ اگر مقدار تکی بود، اضافه کن
                    $fleetIds = array_merge($fleetIds, (array) $fid);
                }

                if (empty($fleetIds)) {
                    $fleetIds = [];
                }

                $onlyIds = Arr::flatten($fleetIds);

                // 3) نگاشت شهرها به ID
                $originIds = [];
                foreach ($origins as $o) {
                    if ($o === null) {
                        $originIds[] = null;
                        continue;
                    }
                    $oid = $this->resolveCityId($o);
                    $originIds[] = $oid;
                }

                $destIds = [];

                foreach ($dests as $d) {
                    if ($d === null) {
                        $destIds[] = null;
                        continue;
                    }
                    $did = $this->resolveCityId($d);
                    $destIds[] = $did;
                }

                // 4) ضرب دکارتی و ذخیره
                $storedAny = false;
                foreach ($onlyIds as $fid) {

                    foreach ($originIds as $oid) {
                        foreach ($destIds as $did) {
                            // اگر هر دو شهر null باشند، ذخیره نکن
                            if (!$oid && !$did) continue;

                            // ضدتکرار
                            if ($this->isDuplicate($oid, $did, $fid, $mobile)) {
                                $results['duplicates']++;
                                $results['details'][] = [
                                    'index' => $idx,
                                    'status' => 'duplicate',
                                    'fleet_id' => $fid,
                                    'origin_id' => $oid,
                                    'destination_id' => $did
                                ];
                                continue;
                            }

                            // بررسی فیلدهای الزامی
                            if (empty($mobile) || empty($oid) || empty($did)) {
                                $results['details'][] = [
                                    'index' => $idx,
                                    'status' => 'failed',
                                    'reason' => 'مبدا، مقصد یا شماره موبایل خالی است',
                                    'fleet_id' => $fid,
                                    'origin_id' => $oid,
                                    'destination_id' => $did
                                ];
                                $results['failed']++;
                                continue; // رد شدن از این آیتم و رفتن به بعدی
                            }

                            // اگر همه چیز اوکی بود
                            $payload = [
                                'title'       => $title ?? 'بدون عنوان',
                                'description' => $item['description'] ?? '',
                                'phoneNumber' => $mobile,
                                'origin'      => is_array($item['origin']) ? implode('، ', $item['origin']) : $item['origin'],
                                'destination' => is_array($item['destination']) ? implode('، ', $item['destination']) : $item['destination'],
                            ];

                            $newId = $this->persistLoad($payload, $oid ?? 0, $did ?? 0, $fid ? [$fid] : []);
                            $storedAny = true;

                            $results['details'][] = [
                                'index' => $idx,
                                'status' => 'stored',
                                'load_id' => $newId,
                                'fleet_id' => $fid,
                                'origin_id' => $oid,
                                'destination_id' => $did
                            ];
                            $results['stored']++;
                        }
                    }
                }

                if (!$storedAny) {
                    $results['failed']++;
                    $results['details'][] =

                        ['index' => $idx, 'status' => 'failed', 'reason' => 'no_valid_combo'];
                }
            } catch (\Throwable $e) {
                $results['failed']++;
                $results['details'][] = ['index' => $idx, 'status' => 'error', 'message' => $e->getMessage()];
            }
        }

        return $results;
    }
}
