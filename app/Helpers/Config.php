<?php

use App\Http\Controllers\DateController;
use App\Models\SiteOption;

const SUCCESS = 1;
const UN_SUCCESS = 0;

const ACTIVE = 1;
const DE_ACTIVE = 0;

const ACCEPT = 1;
const REJECT = 0;

const THERE_IS_NO_LOAD = 2;

const TENDER = 0;
const IN_TENDER = 1;
const END_TENDER = 2;

const TEN_AUTH_CALLS = 10;
const AUTH_CALLS = 'AuthCalls'; // تماس رایگان
const AUTH_VALIDITY = 'AuthValidity'; // اعتبار رایگان
const AUTH_CARGO = 'AuthCargo'; // بار رایگان

// وضعیت احراز هویت
const DRIVER_AUTH_UN_AUTH = 0;
const DRIVER_AUTH_SILVER_PENDING = 1;
const DRIVER_AUTH_SILVER = 2;
const DRIVER_AUTH_GOLD_PENDING = 3;
const DRIVER_AUTH_GOLD = 4;
const DRIVER_AUTH_STATUS_TITLE = ['انجام نشده', 'سطح نقره ای در حال بررسی', 'سطح نقره ای', 'سطح طلایی در حال بررسی', 'سطح طلایی'];


const NO_ONE = 1;
const YOUR_SELF = 2;
const ANOTHER = 3;

const SELECT_DRIVER = 3;
const DELIVERY = 3;
// بار رد شده
const BEFORE_APPROVAL = -1; // قبل از تایید بار توسط اپراتور
const UNPAID = 0; // پرداخت نشده
const WITHOUT_DRIVER = 1; // مناقصه تمام شده
const WITH_DRIVER = 2; // تایید باربری و منتظر انتخاب راننده
const PAY_FOR_DRIVER = 3; // پرداخت برای انتخاب راننده
const ON_SELECT_DRIVER = 4; // راننده انتخاب شده است

const NO_OPERATOR = 0;

const IRAN_TARABAR_BASE_URL = "https://dashboard.iran-tarabar.ir";

const VACATION_HOUR = 0;
const VACATION_DAY = 1;



const TELL = "02128420609";
// const TELL = "02191097220";
// const TELL = "09184696188";
// const TELL = "08338390328";

//const BANK_NAME = 'بانک ملی';
//const CARD_NUMBER = '6037-9973-5641-6231';
//const BANK_CARD_OWNER = 'محمد محمدی فر';
//const MERCHANT_ID = "ab21993c-5456-4727-9724-8747b8f5da3c"; // بانک ملی (محمد محمدی فر)

// const BANK_NAME = 'بانک ملت';
// const CARD_NUMBER = '6104-3388-0033-6295';
// const BANK_CARD_OWNER = 'شرکت آروین مروارید اردیبهشت';
// const MERCHANT_ID = "16912e8a-a720-4883-8040-9cfd75f7e00f"; // بانک ملت (شرکت آروین مروارید اردیبهشت)

const BANK_NAME = 'بانک ملت';
const CARD_NUMBER = '6104-3388-0045-5178';
const BANK_CARD_OWNER = 'زرین ترابر فناور گستر';
const MERCHANT_ID = "3d859c75-6bdf-433a-a50e-82d614b7a01a"; // بانک ملت (زرین ترابر فناور گستر)

// محدودیت تماس راننده ها در سطوح مختلف
const CALL_LIMIT_FOR_UNAUTH_DRIVERS = 10;
const NUMBER_FOR_CALLS_PAY_DAY_FOR_SILVER_LEVEL_DRIVER = 20;
const NUMBER_FOR_CALLS_PAY_DAY_FOR_GOLD_LEVEL_DRIVER = 50;


const WALLET_INVENTORY_IS_NOT_ENOUGH = 2;

const IS_MEMBER = 1;
const NOT_MEMBER = 2;

const BEARING = 1;
const CUSTOMER = 2;
const DRIVER = 3;
const MARKETER = 4;

const SEND_DRIVER = 5;
const ON_LOADING = 6;
const ON_CARRIAGE = 7;
const DISCHARGE = 8;

const FINISHED = 2;

const INSERT = 1;
const UPDATE = 2;

const STOP_TENDER = 2;

const ROLE_DRIVER = 'driver'; // راننده
const ROLE_TRANSPORTATION_COMPANY = 'transportation_company'; // شرکت حمل و نقل
const ROLE_CARGo_OWNER = 'cargo_owner'; // صاحب بار
const ROLE_CUSTOMER = 'customer'; // صاحب بار
const ROLE_OWNER = 'owner'; // صاحبان بار
const ROLE_ADMIN = 'admin'; // ادمین
const ROLE_OPERATOR = 'operator'; // اپراتور

const API_ACCESS_KEY_USER = 'AAAApyggZl8:APA91bG3uw8zZLi9XRK16x6o9luTeucXldvSx1yOy9jhpe0lRC__qCfA05jsf8BtsUbK4i9bWI2eIlTSwHbqKHHtPFtu3D1BarFh2l4ETO-klvhSkzdnLsU-pr2fYCfWRhBUzuBUfipI';
const API_ACCESS_KEY_TRANSPORTATION_COMPANY = 'AAAAYkHeAws:APA91bHTq3VpvOTsnQpjZjYQzGvU1M3OOPuW26Lw9Zg1yUmSdjw9hfTul6fQ2TVL7XexEwzmOv960dsyeajZbs4yJaOdG0qtQj0UMOtYkI_f3ZuY6xZD1SSmt7eczO9rsVVD6v0WpuEq';
const API_ACCESS_KEY_DRIVER = 'AAAAUSJOUE0:APA91bFUconxnZ5rTt7ChN8GYNgCLeci0nVrV1rJ8-64xED7gZ8RWPL9L_FrfswuGmktXLfQIN1GX0RAUsg1fnqF6glYJh3zJ59pHOEe48Wvhjy22JBgl3s9Ra1t16tH_NOC3-bx-adw';
const API_ACCESS_KEY_OWNER = 'ya29.c.c0AY_VpZgqueHNzB2mqsQOQAhC4MXBFLDsmFFhi0ONcviNjGRkS84MbrI-rPEUwUvDnfkjPJXjdW-y8H6p6DG6iGI25NnUCWEI__QZy815cyt7e-76muBzKFaLmje_Z5scE2qydiU6nN1PtcBAo4VaSc2nQMN6EGmryUw6govOkVzOZUTksBQXYHSsMMuRO3f6xl4OWvEfu5SE4_JdUTyjYuZotpN4wjiZeoOUjp1zaJRcgWhk0OHDJ1wnXOGUvYdw1oEQ07ivyxTf0z5zyXxQSIgLTnj7FF7xrVsS5cf0nqhWZwxihzK63V70AsfznZHPL-NG-L8miHoZ_0rBsZS3URsW592xJnA9ywRX6ozfUynX9m9HiCea6CHv2AN387K2U6yltQsWcfnRwXx65qV_5aalecg0uk9WoiV-Yp98obmw1X6wBbVfxXIVeY7t6QoquuiJIy0d0vo9atUzsxw6hxYaXIylbzhJ0xynw-yixompvao25n3efvtR_Yvn8aUr95xfR0llfi6rejianhffgQRiSkcwdncuSY8qbZ3tknRSavOYFJbqsSz-VMJJeYQXFiBRtJ4eli729Wt0i_WcrZjXS-QZuowVc_cgIzxJ7qyg0xifZ5FpwxSlbqlnOdg_gk2hxM5o3d2IIQVVdRdSd7B5Rxsc4-uV5j1g4tYdwdcV0MXFBB5swqX9ZIMXmw8_y4jgW4mUaRMR-gBk_IFaF8y97sgRByaoW8_FUrwwIZJYf1ccg1OSaVI8dsZSIZ9OzzQva-26ymVkzB0w4JFRhneFJRxiRq7F4olepa_jh1xdV5Ikfithzp7JIWO3R7wk_zOtWMoWFm37d9nsSok4wr6_ZUbdmXX9jv6qt-medqf-7S3Mt_ig7glFvrMuth5Ztb3g2S3wslrnl_rav7IrcVky6syjB-VRMocZ3j8bkI7Q78mqY61yVqft04hxw4bp5yaSmB1fetvhhzlYb_sj0S0fwto9jx10wamrS0cWcxhzi5RpSn9wayx';

const API_ACCESS_KEY_DRIVER_NEW = 'AAAAVKCwftI:APA91bEvMwLYwS_6SmNgm1Wre4FxwNVgvO1mZXw9RcuZpY3qtmglQm3U2-iaTqQFPOhjffDvn3Ax5zkGME6rUgNpA1BI5nYWl7RTF9OhOYKXdMVFgf6wR-i-JlGdOw1BXP5VSYOlioW6';
const  REFRESH_LOAD_INFO_PAGE = 'REFRESH_LOAD_INFO_PAGE';

const TNDER_TIME = 1800;
const DRIVERS_TENDER_TIME = 1800;

const PERCENT = 3;

const REAL_PERSONALITY = "realPersonality";
const LEGAL_PERSONALITY = "legalPersonality";

// const SMS_PANEL = 'Faraz';
const SMS_PANEL = 'SMSIR';


const TRANSPORTATION_COMPANY_MONTHLY_SERVICE_AMOUNT = 60000;
const NUM_OF_FREE_LOADS = 100;
const ONE_MONTH_TIME = 30 * 86400;

const NOTIFICATION_PAY_MONTHLY_CHARGE_SUCCESS = "payMonthlyChargeSuccess";

function isNewLoadAutoAccept()
{
    $siteOption = SiteOption::first();
    return $siteOption->newLoadAutoAccept;
}

// ارسال بار های تلگرام به بار های ثبت شده صاحب بار
function isSendBotLoadOwner()
{
    $siteOption = SiteOption::first();
    return $siteOption->sendBotLoadOwner;
}

function isTransportationCompanyAutoActive()
{
    $siteOption = SiteOption::first();
    return $siteOption->transportationCompanyAutoActive;
}

function isDriverAutoActive()
{
    $siteOption = SiteOption::first();
    return $siteOption->driverAutoActive;
}

const DRIVER_FREE_CALLS = 5;
const DRIVER_FREE_ACCEPT_LOAD = 5;

const CUSTOMER_FREE_DRIVER_CALLS = 3;
const CUSTOMER_FREE_LOADS = 1000;


function getCustomerPackagesInfo()
{
    return [
        'calls' => CUSTOMER_FREE_DRIVER_CALLS,
        'loads' => CUSTOMER_FREE_LOADS,
        'monthly' => [
            'price' => 100000,
            'calls' => CUSTOMER_FREE_DRIVER_CALLS,
            'loads' => CUSTOMER_FREE_LOADS,
        ],
        'trimester' => [
            'price' => 200000,
            'calls' => CUSTOMER_FREE_DRIVER_CALLS,
            'loads' => CUSTOMER_FREE_LOADS,
        ],
        'sixMonths' => [
            'price' => 400000,
            'calls' => CUSTOMER_FREE_DRIVER_CALLS,
            'loads' => CUSTOMER_FREE_LOADS,
        ]
    ];
}

function getCustomerCallPackagesInfo()
{
    return [
        'calls' => CUSTOMER_FREE_DRIVER_CALLS,
        'loads' => CUSTOMER_FREE_LOADS,
        'monthly' => [
            'price' => 79000,
            'calls' => CUSTOMER_FREE_DRIVER_CALLS,
            'loads' => CUSTOMER_FREE_LOADS,
        ],
        'trimester' => [
            'price' => 199000,
            'calls' => CUSTOMER_FREE_DRIVER_CALLS,
            'loads' => CUSTOMER_FREE_LOADS,
        ],
        'sixMonths' => [
            'price' => 399000,
            'calls' => CUSTOMER_FREE_DRIVER_CALLS,
            'loads' => CUSTOMER_FREE_LOADS,
        ]
    ];
}

function getDriverPackagesInfo()
{
    return [
        'result' => true,
        'data' => [
            'calls' => DRIVER_FREE_CALLS,
            'acceptLoads' => DRIVER_FREE_ACCEPT_LOAD,
            'monthly' => [
                'price' => 79000,
                'calls' => DRIVER_FREE_CALLS
            ],
            'trimester' => [
                'price' => 199000,
                'calls' => DRIVER_FREE_CALLS
            ],
            'sixMonths' => [
                'price' => 399000,
                'calls' => DRIVER_FREE_CALLS
            ],
            'payMessage' => ' یا مبلغ اشتراک را به شماره حساب زیر واریز نموده و پس از واریز با شماره '  . TELL . ' تماس حاصل فرمایید. '
        ],
        'message' => ''
    ];
}


const FLEET_CONTROL_PRICE = 5000;
const FLEET_CONTROL_DISCOUNT = 10;
// هزینه های کنترل ناوگان
function getFleetControlPackagesInfo()
{
    return [
        'title' => 'هزینه کنترل ناوگان',
        'price' => FLEET_CONTROL_PRICE,
        'discount' => FLEET_CONTROL_DISCOUNT,
        'payLink' => IRAN_TARABAR_BASE_URL . '/fleetControlPay'
    ];
}

function convertFaNumberToEn($string)
{
    $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    $arabic = ['٩', '٨', '٧', '٦', '٥', '٤', '٣', '٢', '١', '٠'];

    $num = range(0, 9);
    $convertedPersianNums = str_replace($persian, $num, $string);
    $englishNumbersOnly = str_replace($arabic, $num, $convertedPersianNums);

    return $englishNumbersOnly;
}


function convertEnNumberToFa($string)
{
    $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    $arabic = ['٩', '٨', '٧', '٦', '٥', '٤', '٣', '٢', '١', '٠'];

    $num = range(0, 9);
    $convertedPersianNums = str_replace($num, $persian, $string);
    $englishNumbersOnly = str_replace($num, $arabic, $convertedPersianNums);

    return $englishNumbersOnly;
}

function getCurrentWeekSaturdayDate()
{

    $day_of_week = date('l');
    $numOfDay = 0;
    switch ($day_of_week) {
        case 'Monday':
            $numOfDay = 2;
            break;
        case 'Tuesday':
            $numOfDay = 3;
            break;
        case 'Wednesday':
            $numOfDay = 4;
            break;
        case 'Thursday':
            $numOfDay = 5;
            break;
        case 'Friday':
            $numOfDay = 6;
            break;
        case 'Saturday':
            $numOfDay = 0;
            break;
        case 'Sunday':
            $numOfDay = 1;
            break;
    }

    return \date('Y-m-d', strtotime('-' . $numOfDay . ' day', time())) . ' 00:00:00';
}

function persianDateToGregorian($date, $mode = '/')
{
    $date = convertFaNumberToEn($date);
    $date = explode($mode, $date);

    if (isset($date[0]) && isset($date[1]) && isset($date[2])) {
        $cdate = new DateController();
        return $cdate->jalali_to_gregorian($date[0], $date[1], $date[2], $mode);
    }
    return '';
}

function gregorianDateToPersian($date, $mode = '/', $dateAndTime = false)
{
    try {
        if ($dateAndTime) {
            $date = explode(' ', $date);
            $date = $date[0];
        }
    } catch (Exception $exception) {
    }

    $date = convertFaNumberToEn($date);
    $date = explode($mode, $date);

    if (isset($date[0]) && isset($date[1]) && isset($date[2])) {
        $cdate = new DateController();
        return $cdate->gregorian_to_jalali($date[0], $date[1], $date[2], $mode);
    }
    return '';
}

// دریافت تعداد روزی های ماه جاری
function getNumOfCurrentMonthDays()
{
    try {

        $date = explode('/', DateController::getDate());
        if ($date[1] < 7)
            return 31;
        return 30;
    } catch (Exception $exception) {
    }

    return 30;
}
