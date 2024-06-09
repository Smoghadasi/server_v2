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
const API_ACCESS_KEY_OWNER = 'ya29.c.c0AY_VpZjpgxuc4ukOtS2dZellg8MZUKtjWsESTizsw9sF0xluej8ryZFvOqEQE_c6fnu_6Ar03tHBu_1OqgjS005HfETlY28v_4tc1Ixk9hzyor8YPL02FrH7tgNXkgTXi2KLpJ0Ivryei3TUEi3G_HKVKv1Y4H-MEi3JXU1cZH3ijjlGs00ScGcbjh-UBIgiPNnJ70yqxmaKlFuEp-gslRM5iowws2MICQjeHqWFGEsNpzTmW_daQxa_oDAcDNNNUA94QGMU4t1ZLBUYAdpy8_T15DAJ-5jIiaLoQ0dev0IUzAKlBVO1i7bJcPDcxSDsDg0EORQtNP50XGXIMnxLk0fAdvb_olYzdZRVMI7N1Sd73SNV-bptdB4FAgH387CyZ7IWJUhMZ9yqSmFOxgis1X2ZQtQlOoSh98Z9il20ZeI8Y_-8abMnazVdcfMz35ufJs3Bojh7p4-bOez20a8r4UVXlwooOFp4lY7lUQgu_xa8Mrn8ijYw3UFkwlWhYXqs1OX4_ybzJqnwecfM9e6jn3mtzly12dVk7qrOgmzuQjcysu4ZBXbM24n2xX2lj98UerQUW7xQFh14ch7JXdJhFMgsbRlimF6nW7oMF464Vzwtf9FR59k0hpOq4c-0O6p2qMqeXkUhz0gcornz0v0f6OqatlnumnmBimp6J3kWxWZubwelRmZ_z0m8sbefQqibeRajpOp0p-89lznpxXdiSXhY40gqhJFtd051h1aSlMMcbku1kbOgiq0Zlt6Qd7uR9jWSJFOrfrSls0O5-wUqhj-iR5pybZcvr3aVhwksqFq7Fv3kvysRuOVVfkYpOVXZg3Q6qUyaf6bQ07aiv7S3qX60YcmdfhzthV_ixvzWyvM-JIM0k5vSRX_Z5Ux0FhjV6hcSi2YgnVYi62i1Ritb2383drg4O6kYucsoqSoJInXcv4o0Fgwm9kMY91d_yfhgXtsMwktrWvW2fb-FWZQ48lI0jz_-1ZIxe1v-x5hb5rBr-gqg7s6f56u';

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
