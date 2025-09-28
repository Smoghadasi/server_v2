<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CargoConvertList extends Model
{
    //

    public function operator()
    {
        return $this->hasOne(User::class, 'id', 'operator_id');
    }

    public function checkExists()
    {
        // مقدار فعلی
        $current = $this->cargo_orginal;

        // گرفتن تمام رکوردها (غیر از رکورد فعلی)
        $all = CargoConvertList::where('id', '!=', $this->id)->get();

        foreach ($all as $item) {
            $distance = levenshtein(mb_strtolower($current), mb_strtolower($item->cargo_orginal));

            // محاسبه درصد شباهت
            $maxLen = max(strlen($current), strlen($item->cargo_orginal));
            if ($maxLen === 0) {
                continue;
            }
            $similarity = 1 - ($distance / $maxLen); // بین 0 تا 1

            if ($similarity >= 0.96) {
                return 1; // رکورد مشابه پیدا شد
            }
        }

        return 0; // مشابه پیدا نشد
    }
}
