<?php

namespace App\Http\Controllers;

use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use phpDocumentor\Reflection\Types\This;

class DateController extends Controller
{

    // دریافت تاریخ خورشیدی
    public static function getDate()
    {
        $date = date("Y/m/d");
        $date = explode('/', $date);

        $gy = $date[0];
        $gm = $date[1];
        $gd = $date[2];
        $mod = '/';

        $g_d_m = array(0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334);
        if ($gy > 1600) {
            $jy = 979;
            $gy -= 1600;
        } else {
            $jy = 0;
            $gy -= 621;
        }
        $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
        $days = (365 * $gy) + ((int)(($gy2 + 3) / 4)) - ((int)(($gy2 + 99) / 100)) + ((int)(($gy2 + 399) / 400)) - 80 + $gd + $g_d_m[$gm - 1];
        $jy += 33 * ((int)($days / 12053));
        $days %= 12053;
        $jy += 4 * ((int)($days / 1461));
        $days %= 1461;
        if ($days > 365) {
            $jy += (int)(($days - 1) / 365);
            $days = ($days - 1) % 365;
        }
        $jm = ($days < 186) ? 1 + (int)($days / 31) : 7 + (int)(($days - 186) / 30);
        $jd = 1 + (($days < 186) ? ($days % 31) : (($days - 186) % 30));
        return ($mod == '') ? array($jy, $jm, $jd) : $jy . $mod . $jm . $mod . $jd;
    }

    // دریافت دقیقه از زمان ثبت تا زمان حاضر
    public static function getMinuteFromCreateRowToPresent($startTime)
    {
        $endTime = date("Y-m-d H:i:s");
        $to_time = strtotime($startTime);
        $from_time = strtotime($endTime);
        return ((int)round(abs($to_time - $from_time) / 60, 2));

    }

    // دریافت ثانیه از زمان ثبت تا زمان حاضر
    public static function getSecondFromCreateRowToPresent($startTime)
    {
        $endTime = date("Y-m-d H:i:s");
        $to_time = strtotime($startTime);
        $from_time = strtotime($endTime);
        return ((int)round(abs($to_time - $from_time), 2));

    }

    // دریافت ساعت فعلی سرور
    public static function getCurrentHour()
    {
        $date = new DateTime("now", new DateTimeZone("Asia/Tehran"));
        echo (int)$date->format("H");
    }

    public static function create_a_date($day)
    {

        $date = date("Y-m-d H:i:s", time());
        $date = date_create($date);
        date_sub($date, date_interval_create_from_date_string($day . " days"));
        return date_format($date, "Y-m-d H:i:s");
    }

    public function gregorian_to_jalali($gy, $gm, $gd, $mod = '')
    {
        $g_d_m = array(0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334);
        if ($gy > 1600) {
            $jy = 979;
            $gy -= 1600;
        } else {
            $jy = 0;
            $gy -= 621;
        }
        $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
        $days = (365 * $gy) + ((int)(($gy2 + 3) / 4)) - ((int)(($gy2 + 99) / 100)) + ((int)(($gy2 + 399) / 400)) - 80 + $gd + $g_d_m[$gm - 1];
        $jy += 33 * ((int)($days / 12053));
        $days %= 12053;
        $jy += 4 * ((int)($days / 1461));
        $days %= 1461;
        if ($days > 365) {
            $jy += (int)(($days - 1) / 365);
            $days = ($days - 1) % 365;
        }
        $jm = ($days < 186) ? 1 + (int)($days / 31) : 7 + (int)(($days - 186) / 30);
        $jd = 1 + (($days < 186) ? ($days % 31) : (($days - 186) % 30));
        return ($mod == '') ? array($jy, ($jm < 10 ? '0' . $jm : $jm), ($jd < 10 ? '0' . $jd : $jd)) : $jy . $mod . ($jm < 10 ? '0' . $jm : $jm) . $mod . ($jd < 10 ? '0' . $jd : $jd);
    }

    public function jalali_to_gregorian($jy, $jm, $jd, $mod = '')
    {
        if ($jy > 979) {
            $gy = 1600;
            $jy -= 979;
        } else {
            $gy = 621;
        }
        $days = (365 * $jy) + (((int)($jy / 33)) * 8) + ((int)((($jy % 33) + 3) / 4)) + 78 + $jd + (($jm < 7) ? ($jm - 1) * 31 : (($jm - 7) * 30) + 186);
        $gy += 400 * ((int)($days / 146097));
        $days %= 146097;
        if ($days > 36524) {
            $gy += 100 * ((int)(--$days / 36524));
            $days %= 36524;
            if ($days >= 365) $days++;
        }
        $gy += 4 * ((int)($days / 1461));
        $days %= 1461;
        if ($days > 365) {
            $gy += (int)(($days - 1) / 365);
            $days = ($days - 1) % 365;
        }
        $gd = $days + 1;
        foreach (array(0, 31, (($gy % 4 == 0 and $gy % 100 != 0) or ($gy % 400 == 0)) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31) as $gm => $v) {
            if ($gd <= $v) break;
            $gd -= $v;
        }
        return ($mod == '') ? array($gy, ($gm<10?"0".$gm:$gm), $gd) : $gy . $mod . ($gm < 10 ? "0" . $gm : $gm) . $mod . $gd;
    }

    public static function createPersianDate()
    {
        $date = explode('-', date("Y-m-d"));
        return (new self)->gregorian_to_jalali($date[0], $date[1], $date[2], '-');

    }
}
