<?php

namespace App\Http\Controllers;

use App\Models\Bearing;
use App\Models\BlockPhoneNumber;
use App\Models\BotTest;
use App\Models\CargoCanvertList;
use App\Models\CargoConvertList;
use App\Models\City;
use App\Models\Customer;
use App\Models\DateOfCargoDeclaration;
use App\Models\Dictionary;
use App\Models\DriverCallCount;
use App\Models\DriverCallReport;
use App\Models\Fleet;
use App\Models\FleetLoad;
use App\Models\Load;
use App\Models\LoadBackup;
use App\Models\CargoReportByFleet;
use App\Models\OperatorCargoListAccess;
use App\Models\RejectCargoOperator;
use App\Models\Tender;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserActivityReport;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PHPUnit\Exception;


class DataConvertController extends Controller
{
    const SINGLE_ORIGIN_SINGLE_DESTINATION = 1;
    const SINGLE_ORIGIN_MULTI_DESTINATION = 2;
    const MULTI_ORIGIN_MULTI_DESTINATION = 3;
    const  EVERY_LINE_ONE_CARGO = 4;
    const SINGLE_FLEET = 1;
    const MULTI_FLEET = 2;

    public function storeCargoInformation(Request $request)
    {
        try {
            if (CargoConvertList::where('cargo', $request->cargo)->count() == 0) {
                $cargoConvertList = new CargoConvertList();
                $cargoConvertList->cargo = $request->cargo;
                $cargoConvertList->save();
                return back()->with('success', 'ذخیره شد');
            }
            return back()->with('success', 'اطلاعات ارسال شده تکراری بود!');
        } catch (Exception $exception) {
        }
        return back()->with('danger', 'ذخیره انجام نشد');
    }

    public function removeCargoFromCargoList(CargoConvertList $cargo)
    {
        $cargo->status = 1;
        $cargo->rejected = 1;
        $cargo->save();
        $persian_date = gregorianDateToPersian(date('Y/m/d', time()), '/');
        $rejectCargo = RejectCargoOperator::where('user_id', auth()->id())
            ->where('persian_date', $persian_date)
            ->first();
        if (isset($rejectCargo->id)) {
            $rejectCargo->count += 1;
            $rejectCargo->save();
        } else {
            $rejectCargo = new RejectCargoOperator();
            $rejectCargo->persian_date = $persian_date;
            $rejectCargo->count = 1;
            $rejectCargo->user_id = auth()->id();
            $rejectCargo->save();
        }
        return back();
    }

    public function updateCargoInfo(CargoConvertList $cargo, Request $request)
    {
        $cargo->cargo = $request->cargo;
        $cargo->save();
        return back()->with('success', 'ویرایش شد');
    }

    public function cargoConvertLists()
    {
        $duplicates = DB::table('cargo_convert_lists')
            ->select('cargo', DB::raw('COUNT(*) as `count`'))
            ->groupBy('cargo')
            ->havingRaw('COUNT(*) > 1')
            ->get();
        CargoConvertList::where('cargo', "[5/14،‏ 14:17] نادر اینانلو: 🌹سلام‌خسته‌نباشید🌹\nصادرات000👇👇👇👇\nعسلویه👈👈👈آمبارلی\nبندرامام👈👈👈قبضه\n\nعسلویه👈👈👈ارنکوی\nعسلویه👈👈👈مرسین\nتهران👈👈👈قبضه\nتهران👈👈👈آمبارلی\n\n\nبناب👈👈👈آنتپ\nبناب👈👈👈شانلی‌اروفا\nجلفا👈👈👈اسکندرون\nتهران👈👈👈اسکندرون\nاصفهان👈👈👈اسکندرون\n\n\nواردات👎👎👎\n\nآنتپ👈👈👈تعداد\n\nمرسین‌👈👈👈تعداد\n\n\n🇹🇷🇹🇷🇹🇷🇹🇷🇮🇷🇮🇷🇮🇷\nاسماعیلی\n09123153376\n09126773543عینالو\n09361766726واتساپ")
            ->delete();
        CargoConvertList::where('cargo', "*سلام  خدمت رانندگان محترم🌹*\n\n*بارگیری های هیدروکربن(تانکری) :*\n\n\n\n*🌎*قروه به ماهیرود(روغن پایه) تنی:2میلیون با انعام*\n\n\n*🌎*بندرامام به اربیل تنی:1800 با انعام 6 میلیون*\n\n\n*🌎*شیراز به اشتهارد تنی:800*\n\n\n*📞 شاهرخ فتحی :*\n*09935815052*\n*📞  محمد فتحی:*\n*09910756382*")
            ->delete();
        CargoConvertList::where('cargo', "/با سلام به کاربران عزیز: \n1_فرم گروه رو تکمیل فرمایید. \n2_مخاطبین خود رو اد کنید\n3_برای ارسال فرم باید کپی کنید، هدایت نکنید\n4_فرم ناقص نباشد که حذف می شود. \n5_در طول روز فقط یک بار فرم ارسال فرمایید. \n6_اگر مشکلی در ارسال فرم داریدب پی وی خادمانتان مراجعه فرمایید\n7_کاربران مزاحم را جهت حذف معرفی کنید. \nبا تشکر التماس دعا🌹🙏\n\n09024127372\nقاسمی بیرجندی")
            ->delete();
        CargoConvertList::where('cargo', "• کاربر 09936266084 -  6338790855\n\n• برای ارسال پیام باید عضو کانال گروه باشید ،\n\n• لطفا با استفاده از دکمه زیر در کانال عضو شوید !")
            ->delete();
        CargoConvertList::where('cargo', "• کاربر 09936266084 با آیدی عددی 6338790855\n\n• شما برای چت کردن باید 40 نفر را اضافه کنید.\n\n● در حال حاضر شما  0  نفر اضافه کرده‌اید.")
            ->delete();
        CargoConvertList::where('cargo', "‌‌3//.❤️باسلام.. یزد.. .میبد .اردکان بارگیری🚛\n❤️❤️❤️❤️❤️❤️❤️❤️❤️❤️❤️❤️❤️\n....توجه ....توجه ...تاریخ 65مهر ماه  🚛🚛🚛\n\n\n\n.تریلر....‌ تریلر 🚛🚛🚛\n\n\nچغادک 26تن 4عدد\n\n\nکرمانشاه 26500\n\nچابهار27\n\nزاهدان 265\n\nسراوان 26\n\nتاکستان فقط کفی265\n\nبابل265\n\n\nتک \n\nکلاردشت \n\n\n\n09130416067\n\n\n 09135712678‌‌")
            ->delete();
        CargoConvertList::where('cargo', "‌‌3//.❤️باسلام.. یزد.. .میبد .اردکان بارگیری🚛\n❤️❤️❤️❤️❤️❤️❤️❤️❤️❤️❤️❤️❤️\n....توجه ....توجه ...تاریخ 65مهر ماه  🚛🚛🚛\n\n\n\n.تریلر....‌ تریلر 🚛🚛🚛\n\n\nچغادک 26تن 4عدد\n\n\nکرمانشاه 26500\n\nچابهار27\n\nزاهدان 265\n\nسراوان 26\n\nتاکستان فقط کفی265\n\nبابل265\n\n\nتک \n\nکلاردشت \n\n\n\n09130416067\n\n\n 09135712678‌‌")
            ->delete();
        return $duplicates;
    }
    public function finalApprovalAndStoreCargo()
    {

        $cargo = CargoConvertList::where([
            ['operator_id', auth()->id()],
            ['status', 0]
        ])
            ->orderby('id', 'asc')
            ->first();


        //        if (!isset($cargo->id)) {
        //            $operatorCargoListAccess = OperatorCargoListAccess::where('user_id', auth()->id())
        //                ->select('fleet_id')
        //                ->pluck('fleet_id')
        //                ->toArray();
        //
        //            if (count($operatorCargoListAccess)) {
        //
        //                $dictionary = Dictionary::where('type', 'fleet')
        //                    ->whereIn('original_word_id', $operatorCargoListAccess)
        //                    ->select('equivalentWord')
        //                    ->pluck('equivalentWord')
        //                    ->toArray();
        //
        //                $conditions = [];
        //
        //                foreach ($dictionary as $item) {
        //                    $conditions[] = ['cargo', 'LIKE', '%' . $item . '%'];
        //
        //                    $cargo = CargoConvertList::where(function ($q) use ($conditions) {
        //                        return $q->where('operator_id', 0)
        //                            ->orwhere($conditions);
        //                    })
        //                        ->orderby('id', 'asc')
        //                        ->first();
        //
        //                    if (isset($cargo->id))
        //                        break;
        //                }
        //
        //            }
        //
        //            if (!isset($cargo->id))
        //                $cargo = CargoConvertList::where('operator_id', 0)->orderby('id', 'asc')->first();
        //        }


        if (!isset($cargo->id)) {
            $operatorCargoListAccess = OperatorCargoListAccess::where('user_id', auth()->id())
                ->select('fleet_id')
                ->pluck('fleet_id')
                ->toArray();

            if (count($operatorCargoListAccess)) {

                $dictionary = Dictionary::where('type', 'fleet')
                    ->whereIn('original_word_id', $operatorCargoListAccess)
                    ->select('equivalentWord')
                    ->pluck('equivalentWord')
                    ->toArray();
                $conditions = [];

                foreach ($dictionary as $item) {
                    $conditions[] = ['cargo', 'LIKE', '%' . $item . '%'];

                    $cargo = CargoConvertList::where(function ($q) use ($conditions) {
                        return $q->orwhere($conditions);
                    })
                        ->where('operator_id', 0)
                        ->orderby('id', 'asc')
                        ->first();

                    if (isset($cargo->id))
                        break;
                }
            }

            if (!isset($cargo->id))
                $cargo = CargoConvertList::where('operator_id', 0)->orderby('id', 'asc')->first();
        }

        //        if (!isset($cargo->id))
        //            $cargo = CargoConvertList::where('operator_id', 0)->orderby('id', 'asc')->first();

        if (isset($cargo->id)) {
            $cargo->operator_id = auth()->id();
            $cargo->save();
            return $this->dataConvert($cargo);
        }


        return redirect(url('dashboard'))->with('danger', 'هیچ باری وجود ندارد');
    }

    // دریافت لیست ناوگان
    private function getFleetsList()
    {
        $fleets = Fleet::where('parent_id', '>', 0)->pluck('title')->toArray();
        for ($i = 0; $i < count($fleets); $i++)
            $fleets[$i] = $this->replaceToPersianAlphabet($fleets[$i]);

        return $fleets;
    }

    // دریافت لیست شهرها
    private function getCitiesList()
    {
        $cities = City::select('name')->pluck('name')->toArray();
        for ($i = 0; $i < count($cities); $i++)
            $cities[$i] = $this->replaceToPersianAlphabet($cities[$i]);
        return $cities;
    }

    // دریافت لیست کلمات مهم در شناسایی رابطه ها
    private function getExtraWords()
    {
        return [
            '[از]', '[به]', '[مبدا]', '[مقصد]', '[صافی]', '[صاف]', '[بارگیری]', '[تخلیه]',
            '[هرتن]', '[به_ازای_هرتن]', '[به_ازاء_هرتن]', '[هر_تن]', '[به_ازای_هر_تن]',
            '[به_ازاء_هر_تن]', '[کرایه]', '[قیمت]', '[م]', '[میلیون]', '[تومن]', '[تن]',
        ];
    }

    public function dataConvert($cargo)
    {
        $prefixFreightConditions = array('صافی', 'صاف', 'هرتن', 'کرایه', 'قیمت');
        $postfixFreightConditions = array('صافی', 'صاف', 'هرتن', 'کرایه', 'م', 'میلیون');
        $originalText = $cargo->cargo;
        $fleetsList = $this->getFleetsList();
        $citiesList = $this->getCitiesList();
        $extraWords = $this->getExtraWords();
        $originWords = $this->getOriginWords();
        $equivalentWords = $this->getEquivalentWords();
        $cleanedText = $this->getCleanedText($cargo->cargo, $fleetsList, $citiesList, $equivalentWords, $originWords, $extraWords, $prefixFreightConditions, $postfixFreightConditions);

        $cargoList = [];
        $currentOrigin = -1;
        $originPrefixWord = false;
        $originPostfixWord = false;
        $cityName = '';
        $isOrigin = false;

        $firstCity = ''; // اگر هیچ مبدا پیدا نشده اولی شهر مبدا است
        $origins = [];
        $destinations = [];
        $fleets = [];

        $phoneNumbers = [];
        $phoneNumber = '';
        //        return $cleanedText;

        foreach ($cleanedText as $key => $item)
            if (preg_match("/^[0]{1}\d{10}$/", $item))
                $phoneNumbers[] = [
                    'phoneNumber' => $item,
                    'key' => $key
                ];

        //        foreach ($phoneNumbers as $item)
        //            if (strlen($phoneNumber) == 0 && preg_match("/^[0]{1}[9]{1}\d{9}$/", $item['phoneNumber'])) {
        //                $phoneNumber = $item['phoneNumber'];
        //                break;
        //            }
        //
        //        if (strlen($phoneNumber) == 0 && isset($phoneNumbers[0]['phoneNumber']))
        //            $phoneNumber = $phoneNumbers[0]['phoneNumber'];

        $freight = 0;
        $priceType = '';

        //        foreach ($cleanedText as $key => $item) {
        //            if ($item == '[صافی]' || $item == '[صاف]' || $item == '[هرتن]' || $item == '[کرایه]' || $item == '[قیمت]')
        //                if (isset($cleanedText[$key + 1]))
        //                    if (is_numeric($cleanedText[$key - 1]))
        //                        $freight = $cleanedText[$key + 1];
        //
        //            if ($freight == 0)
        //                if ($item == '[صافی]' || $item == '[صاف]' || $item == '[هرتن]' || $item == '[کرایه]' || $item == '[م]' || $item == '[میلیون]')
        //                    if (isset($cleanedText[$key - 1]))
        //                        if (is_numeric($cleanedText[$key - 1]))
        //                            $freight = $cleanedText[$key - 1];
        //
        //            if ($freight > 0)
        //                break;
        //
        //        }
        //
        //        if ($freight == 0)
        //            $priceType = 'توافقی';
        //        else {
        //            if ($item == '[صافی]' || $item == '[صاف]')
        //                $priceType = 'بصورت صافی';
        //            else if ($item == '[هرتن]')
        //                $priceType = 'به ازای هرتن';
        //            else
        //                $priceType = 'صافی';
        //
        //            $freight = $freight < 1000 ? $freight * 1000000 : $freight;
        //
        //        }


        foreach ($cleanedText as $key => $item) {


            if (in_array($item, $fleetsList)) {
                if (isset($cleanedText[$key - 1]))
                    if ($cleanedText[$key - 1] == '[_]')
                        $fleets = [];
                $fleets[$item] = $item;
            }

            if (in_array($item, $citiesList) == true && strlen($firstCity) == 0)
                $firstCity = $item;

            if ($originPostfixWord && strlen($cityName) && $isOrigin) {
                $origins[] = $cityName;
                $currentOrigin = $key;
                $destinations = [];
                //                $cargoList[$currentOrigin]['originName'] = $cityName;
                $originName = $cityName;
                $cityName = '';
            }

            if (in_array($item, $citiesList) == true) {
                $cityName = $item;
            }

            if ($originPrefixWord && strlen($cityName) && $isOrigin) {
                $currentOrigin = $key;
                //                $cargoList[$currentOrigin]['originName'] = $cityName;
                $originName = $cityName;
                $origins[] = $cityName;
                $destinations = [];
                $cityName = '';
            }

            if (in_array($item, ['[از]', '[مبدا]', '[بارگیری]']) == true) {
                $originPrefixWord = true;
                $originPostfixWord = false;
                $isOrigin = true;
            } else if (in_array($item, ['[به]', '[مقصد]', '[تخلیه]']) == true && strlen($cityName)) {
                $originPostfixWord = true;
                $originPrefixWord = false;
                $isOrigin = true;
            } else
                $isOrigin = false;

            $cargoPhoneNumber = '';
            foreach ($phoneNumbers as $phoneNumberItem)
                if ($key < $phoneNumberItem['key']) {
                    $cargoPhoneNumber = $phoneNumberItem['phoneNumber'];
                    break;
                }

            if ($cargoPhoneNumber == '')
                $cargoPhoneNumber = $phoneNumber;

            if ($isOrigin == false && in_array($item, $origins) == false && in_array($item, $citiesList)) {
                if (isset($cleanedText[$key + 1]) && !in_array($cleanedText[$key + 1], ['[به]', '[مقصد]', '[تخلیه]'])) {
                    $destinations[$currentOrigin][] = $item;
                    if ($currentOrigin > -1) {
                        $cargoList[] = [
                            'origin' => $originName,
                            'destination' => $item,
                            'fleets' => $fleets,
                            'mobileNumber' => $cargoPhoneNumber,
                            'freight' => 0,
                            'priceType' => 'توافقی'
                            //                            'freight' => $freight,
                            //                            'priceType' => $priceType
                        ];
                    }
                }
            }
        }

        $countOfCargos = CargoConvertList::where('operator_id', 0)->count();


        $users = UserController::getOnlineAndOfflineUsers();

        return view('admin.storeCargoForm', compact('cargoList', 'originalText', 'cargo', 'countOfCargos', 'users'));
    }

    // جابجایی حروف فارسی با حروف عربی
    private function replaceToPersianAlphabet($text)
    {
        return str_replace('أ', 'ا', str_replace('ي', 'ی', str_replace('ك', 'ک', convertFaNumberToEn($text))));
    }

    // تمیز کردن متن
    private function getCleanedText($text, &$fleetsList, &$citiesList, array $equivalentWords, array &$originWords, array &$extraWords, $prefixFreightConditions, $postfixFreightConditions): array
    {
        // جایگیزی حروف فارسی به جای عربی
        $text = $this->replaceToPersianAlphabet($text);


        $text = str_replace("-", " ", $text);
        $text = str_replace("*", " ", $text);
        $text = str_replace(".", " ", $text);
        $text = str_replace("_", " ", $text);
        $text = str_replace("/", "-", $text);
        $pattern = "/[0-9]{4}-[0-9]{2}-[0-9]{2}/";
        $text = preg_replace($pattern, "", $text);
        $text = str_replace("-", ".", $text);


        $text = str_replace(["\n", "\r"], ' _ ', $text);

        // جای گزینی حروف اضافه با جای خالی
        $text = preg_replace('/[^_.آ-ی0-9]+/u', ' ', $text);

        // جدا کردن اعداد و حرف از هم
        $text = preg_replace('/(\d+\.?\d*)/', ' $0 ', $text);


        // یکی کردن اسامی چند کلمه ای با اضافه کردن "_"
        for ($i = 0; $i < count($fleetsList); $i++) {
            $mergeWords = str_replace(' ', '_', $fleetsList[$i]);
            $text = str_replace($fleetsList[$i], $mergeWords, $text);
            $fleetsList[$i] = '[' . $mergeWords . ']';
        }

        for ($i = 0; $i < count($citiesList); $i++) {
            $mergeWords = str_replace(' ', '_', $citiesList[$i]);
            $text = str_replace($citiesList[$i], $mergeWords, $text);
            $citiesList[$i] = '[' . $mergeWords . ']';
        }

        // اول ناوگان دو کلمه ای را جستجو و یک کلمه ای شود
        for ($i = 0; $i < count($equivalentWords); $i++) {
            if (count(explode(' ', $equivalentWords[$i])) > 1)
                $text = str_replace($equivalentWords[$i], $originWords[$i], $text);
        }

        // تمام کلمات بین [] قرار گیرند
        $text = explode(' ', $text);

        $newText = '';
        for ($i = 0; $i < count($text); $i++)
            if (strlen($text[$i]) && $text[$i] != ' ' && !(isset($text[$i - 1]) && ($text[$i - 1] == "[_]" || $text[$i - 1] == "_") && $text[$i] == "_")) {
                $text[$i] = is_numeric($text[$i]) || $text[$i] == '.' ? $text[$i] : '[' . $text[$i] . ']';
                $newText .= ' ' . $text[$i];
            }

        for ($i = 0; $i < count($originWords); $i++)
            $originWords[$i] = '[' . $originWords[$i] . ']';

        // جایگزی کلمات معادل
        for ($i = 0; $i < count($equivalentWords); $i++) {
            $originWordsList = '';
            foreach ($equivalentWords as $key => $eqw)
                if ($eqw == $equivalentWords[$i] && isset($originWords[$key]))
                    $originWordsList .= ' ' . $originWords[$key];
            $newText = str_replace('[' . $equivalentWords[$i] . ']', $originWordsList, $newText);
        }

        $newText = explode(' ', $newText);

        // حذف حروف اضافه
        $cleanText = [];
        for ($i = 0; $i < count($newText); $i++)
            if (isset($newText[$i]) && (in_array($newText[$i], $fleetsList) || in_array($newText[$i], $citiesList) || in_array($newText[$i], $extraWords) || in_array($newText[$i], $prefixFreightConditions) || in_array($newText[$i], $postfixFreightConditions) || is_numeric($newText[$i]) || $newText[$i] == '.' || $newText[$i] == '[_]'))
                $cleanText[] = $newText[$i];

        return $cleanText;
    }

    // دریافت لیست کلمات اصلی
    private function getOriginWords()
    {
        $dictionary = Dictionary::get()->pluck('originalWord');
        $array = [];
        foreach ($dictionary as $item)
            $array[] = str_replace(' ', '_', $item);

        return $array;
    }

    // دریافت لیست کلمات معادل
    private function getEquivalentWords(): array
    {
        return Dictionary::get()->pluck('equivalentWord')->toArray();
    }

    // فرم ثبت بار (بررسی و ثبت)
    public function storeCargoConvertForm()
    {
        $countOfCargos = CargoConvertList::where('operator_id', 0)->count();

        return view('admin.storeCargoConvertForm', compact('countOfCargos'));
    }

    // ذخیره دسته ای بارها
    public function storeMultiCargo(Request $request, CargoConvertList $cargo)
    {

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

        $counter = 0;


        foreach ($request->key as $key) {
            $origin = "origin_" . $key;
            $destination = "destination_" . $key;
            $mobileNumber = "mobileNumber_" . $key;
            $fleets = "fleets_" . $key;
            $freight = "freight_" . $key;
            $priceType = "priceType_" . $key;
            $title = "title_" . $key;
            try {

                foreach ($request->$fleets as $fleet) {
                    $this->storeCargo($request->$origin, $request->$destination, $request->$mobileNumber, $fleet, $request->$freight, $request->$priceType, $request->$title, $counter);
                }
            } catch (\Exception $exception) {
                Log::emergency("storeMultiCargo : " . $exception->getMessage());
            }
        }

        $cargo->status = true;
        $cargo->save();
        return back()->with('success', $counter . 'بار ثبت شد');
    }

    // ذخیره بار
    public function storeCargo($origin, $destination, $mobileNumber, $fleet, $freight, $priceType, $title, &$counter)
    {
        if (!strlen(trim($origin)) || $origin == null || $origin == 'null' || !strlen(trim($destination)) || $destination == null || $destination == 'null' || !strlen($fleet) || !strlen($mobileNumber))
            return;

        $freight = convertFaNumberToEn(str_replace(',', '', $freight));


        $cargoPattern = '';

        try {
            $cargoPattern = $origin . $destination . $mobileNumber . $fleet;
            if (
                BlockPhoneNumber::where('phoneNumber', $mobileNumber)->count() ||
                Load::where([['cargoPattern', $cargoPattern], ['created_at', '>', date('Y-m-d h:i:s', strtotime('-180 minute', time()))]])->count()
            ) {
                return;
            }
        } catch (\Exception $exception) {
            Log::emergency("---------------------------------------------------------------------------");
            Log::emergency("خطای جستجوی تکراری");
            Log::emergency($exception->getMessage());
            Log::emergency("---------------------------------------------------------------------------");
            return;
        }

        try {

            DB::beginTransaction();
            $load = new Load();
            $load->title = strlen($title) == 0 ? 'بدون عنوان' : $title;
            $load->weight = 0;
            $load->width = 0;
            $load->length = 0;
            $load->height = 0;
            $load->loadingAddress = '';
            $load->dischargeAddress = '';
            $load->senderMobileNumber = $mobileNumber;
            $load->receiverMobileNumber = '';
            $load->insuranceAmount = 0;
            $load->suggestedPrice = 0;
            $load->marketing_price = 0;
            $load->emergencyPhone = $mobileNumber;
            $load->dischargeTime = '';
            $load->fleet_id = 0;
            $load->load_type_id = 0;
            $load->tenderTimeDuration = 0;
            $load->packing_type_id = 0;
            $load->loadPic = "noImage";
            $load->user_id = auth()->id();
            $load->userType = ROLE_OPERATOR;
            $load->loadMode = 'outerCity';
            $load->loadingHour = 0;
            $load->loadingMinute = 0;
            $load->numOfTrucks = 1;
            $load->cargoPattern = $cargoPattern;

            $origin = str_replace('_', ' ', str_replace('[', '', str_replace(']', '', $origin)));
            $destination = str_replace('_', ' ', str_replace('[', '', str_replace(']', '', $destination)));


            $load->origin_city_id = $this->getCityId($origin);
            $load->destination_city_id = $this->getCityId($destination);


            $load->fromCity = $this->getCityName($load->origin_city_id);
            $load->toCity = $this->getCityName($load->destination_city_id);

            $load->loadingDate = gregorianDateToPersian(date('Y-m-d', time()), '-');
            $load->time = time();

            try {
                $city = City::find($load->origin_city_id);
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
            $load->description = '';

            $load->priceBased = $priceType;
            $load->operator_id = auth()->id();
            $load->proposedPriceForDriver = $freight;
            $load->suggestedPrice = $freight;
            $load->mobileNumberForCoordination = $mobileNumber;
            $load->storeFor = ROLE_DRIVER;
            $load->status = ON_SELECT_DRIVER;
            $load->deliveryTime = 24;

            $load->urgent = 0;
            $load->save();

            if (isset($load->id)) {


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

                    $load->save();
                } catch (\Exception $exception) {
                    Log::emergency("---------------------------------------------------------");
                    Log::emergency($exception->getMessage());
                    Log::emergency("---------------------------------------------------------");
                }

                try {
                    $backup = new LoadBackup();
                    $backup->id = $load->id;
                    $backup->title = $load->title;
                    $backup->weight = $load->weight;
                    $backup->width = $load->width;
                    $backup->length = $load->length;
                    $backup->height = $load->height;
                    $backup->loadingAddress = $load->loadingAddress;
                    $backup->dischargeAddress = $load->dischargeAddress;
                    $backup->senderMobileNumber = $load->senderMobileNumber;
                    $backup->receiverMobileNumber = $load->receiverMobileNumber;
                    $backup->insuranceAmount = $load->insuranceAmount;
                    $backup->marketing_price = 0;
                    $backup->emergencyPhone = $load->emergencyPhone;
                    $backup->dischargeTime = $load->dischargeTime;
                    $backup->fleet_id = $load->fleet_id;
                    $backup->load_type_id = $load->load_type_id;
                    $backup->tenderTimeDuration = $load->tenderTimeDuration;
                    $backup->packing_type_id = $load->packing_type_id;
                    $backup->loadPic = $load->loadPic;
                    $backup->user_id = $load->user_id;
                    $backup->loadMode = $load->loadMode;
                    $backup->loadingHour = $load->loadingHour;
                    $backup->loadingMinute = $load->loadingMinute;
                    $backup->numOfTrucks = $load->numOfTrucks;
                    $backup->origin_city_id = $load->origin_city_id;
                    $backup->destination_city_id = $load->destination_city_id;
                    $backup->fromCity = $load->fromCity;
                    $backup->toCity = $load->toCity;
                    $backup->loadingDate = $load->loadingDate;
                    $backup->time = $load->time;
                    $backup->latitude = $load->latitude;
                    $backup->longitude = $load->longitude;
                    $backup->weightPerTruck = $load->weightPerTruck;
                    $backup->bulk = $load->bulk;
                    $backup->dangerousProducts = $load->dangerousProducts;
                    $backup->origin_state_id = $load->origin_state_id;
                    $backup->description = $load->description;
                    $backup->priceBased = $load->priceBased;
                    $backup->bearing_id = $load->bearing_id;
                    $backup->proposedPriceForDriver = $load->proposedPriceForDriver;
                    $backup->suggestedPrice = $load->suggestedPrice;
                    $backup->operator_id = $load->operator_id;
                    $backup->userType = $load->userType;
                    $backup->origin_longitude = $load->origin_longitude;
                    $backup->destination_longitude = $load->destination_longitude;
                    $backup->mobileNumberForCoordination = $load->mobileNumberForCoordination;
                    $backup->storeFor = $load->storeFor;
                    $backup->status = $load->status;
                    $backup->fleets = $load->fleets;
                    $backup->deliveryTime = $load->deliveryTime;
                    $backup->save();
                } catch (\Exception $e) {

                    Log::emergency("========================= Load Backup ==================================");
                    Log::emergency($e->getMessage());
                    Log::emergency("==============================================================");
                }
            }

            $counter++;

            DB::commit();
        } catch (\Exception $exception) {

            DB::rollBack();

            Log::emergency("----------------------ثبت بار جدید-----------------------");
            Log::emergency($exception->getMessage());
            Log::emergency("---------------------------------------------------------");
        }
    }

    private function getCityId($cityName)
    {
        try {
            $city = City::where('name', $cityName)->select('id')->first();
            if (!isset($city->id)) {
                $city = City::where('name', str_replace('ک', 'ك', $cityName))->select('id')->first();
            }
            if (!isset($city->id)) {
                $city = City::where('name', str_replace('ی', 'ي', $cityName))->select('id')->first();
            }
            if (!isset($city->id)) {
                $city = City::where('name', str_replace('ی', 'ي', str_replace('ک', 'ك', $cityName)))->select('id')->first();
            }
            if (isset($city->id))
                return $city->id;
        } catch (\Exception $e) {
        }


        return 0;
    }

    private function getCityName($city_id)
    {
        try {
            $city = City::where('id', $city_id)->select('name', 'state as province')->first();
            if (isset($city->name))
                return $city->province . ', ' . $city->name;
        } catch (\Exception $e) {
        }
        return '';
    }

    /***************************************************************************************************/
    // دیکشنری کلمات معادل در ثبت بار
    public function dictionary()
    {
        $cities = City::all();
        $fleets = Fleet::where('parent_id', '>', 0)->get();

        $dictionary = Dictionary::paginate(300);

        return view('admin.dictionary', compact('cities', 'fleets', 'dictionary'));
    }

    public function addWordToDictionary(Request $request)
    {
        try {
            $original_word_id = $request->type == 'city' ? $request->city_id : $request->fleet_id;
            if (Dictionary::where([
                ['equivalentWord', $request->equivalentWord],
                ['type', $request->type],
                ['original_word_id', $original_word_id],
            ])->count() > 0)
                return back()->with('danger', 'کلمه اصلی، کلمه معادل و دسته تکراری است');

            if (strlen($request->equivalentWord)) {
                $dictionary = new Dictionary();
                $dictionary->type = $request->type;
                $dictionary->original_word_id = $original_word_id;
                $dictionary->equivalentWord = $request->equivalentWord;
                $dictionary->save();

                return back()->with('success', 'کلمه مورد نظر ثبت شد');
            }
        } catch (\Exception $exception) {
        }

        return back()->with('danger', 'خطا در ذخیره');
    }

    public function removeDictionaryWord(Dictionary $dictionary)
    {
        $dictionary->delete();
        return back()->with('success', ' کلمه ' . $dictionary->equivalentWord . ' حذف شد ');
    }

    /**************************************************************************************************/
    // یک مبدا و یک مقصد
    /**************************************************************************************************/

    public function singleOriginSingleDestination($text)
    {
        // الگورتیم تغییر کند و از روی آرایه جستجو کند،
        // همچنین شهرها، ناوگان و کلماتی شبیه به "هر تن" که دوکلمه از هم جدا هستند ابتدا بررسی و بین آنها علامت _ "Underline" گذاشته شود
        // کلمات همسان هم دسته بندی و مشخص شود

        try {

            $weight = 0;
            $freight = 0;
            $originText = str_replace(array("\r", "\n"), '<br>', $text);;

            $text = str_replace(array("\r", "\n"), ' ', $text);
            $array = explode(' ', $text);

            $array = $this->removeItem($array, ['', ' ']);
            $cities = $this->getCitiesList();


            $origin = $this->getOrigin($array);
            $destination = $this->getDestination($array, $cities, $origin);

            // دریافت شماره تلفن
            $mobileNumber = $this->getMobileNumber($text);
            $array = $this->removeItem($array, [$mobileNumber]);
            $text = str_replace($mobileNumber, '', $text);


            // استخراج وزن
            $weight = $this->getWeight($text);

            $text = str_replace($weight, '', $text);

            $freight = $this->getFreight($text);

            $fleetsList = $this->getFleetsList();

            $fleets = [];
            foreach ($fleetsList as $key => $fleet) {
                $text = str_replace($fleet, str_replace(' ', '_', $fleet), $text);
                $fleetsList[$key] = str_replace($fleet, str_replace(' ', '_', $fleet), str_replace('ک', 'ك', str_replace('ی', 'ي', $fleet)));
            }
            $array = explode(' ', $text);
            $array = $this->removeItem($array, ['', ' ']);

            foreach ($fleetsList as $fleet) {
                if (in_array($fleet, $array))
                    $fleets[] = $fleet;
            }

            $data[] = [
                'origin' => $origin,
                'destination' => $destination,
                'weight' => $weight,
                'mobileNumber' => $mobileNumber,
                'freight' => $freight,
                'fleets' => $fleets
            ];

            return $data;
        } catch (\Exception $exception) {
            return back()->with('danger', 'خطایی رخ داده لطفا اطلاعات وارد شده را بررسی کنید');
        }
    }

    /**************************************************************************************************/
    // هر خط یک بار
    /**************************************************************************************************/

    public function everyLineIsOneCargo($texts)
    {

        try {

            $originText = str_replace(array("\r", "\n"), '<br>', $texts);;

            $texts = $this->cleanText(convertFaNumberToEn($texts));

            $mobileNumber = $this->getMobileNumber($texts);

            $texts = explode("\n", $texts);

            $cargoInfo = [];

            $cities = $this->getCitiesList();

            foreach ($texts as $text) {

                $origin = '';
                $destination = '';

                $text = str_replace(array("\r", "\n"), ' ', $text);


                $array = $this->removeItem(explode(' ', $text), ['', ' ']);
                foreach ($array as $item) {
                    if (in_array($item, $cities))
                        if ($origin == '')
                            $origin = $item;
                        else if ($destination == '')
                            $destination = $item;
                }

                $fleet = '';


                if (strlen($origin) > 0 && strlen($destination) > 0)
                    $cargoInfo[] =
                        'از ' . $origin . "\n" .
                        ' به ' . $destination . "\n" .
                        $mobileNumber . "\n" .
                        ' وزن 0' . "\n" .
                        ' کرایه توافقی' . "\n" .
                        ' ' . $fleet;
            }

            return $cargoInfo;

            return view('admin.storeCargoForm', compact('cargoInfo', 'originText'));
        } catch (\Exception $exception) {

            return back()->with('danger', 'خطایی رخ داده لطفا اطلاعات وارد شده را بررسی کنید');
        }
    }


    /**************************************************************************************************/
    // یک مبدا و چند مقصد
    /**************************************************************************************************/

    public function singleOriginMultiDestination($text)
    {
        try {

            $text = convertFaNumberToEn($text);
            $text = str_replace('ک', 'ک', $text);
            $text = str_replace('ی', 'ي', $text);


            $originText = str_replace(array("\r", "\n"), '<br>', $text);;

            $destinations = [];
            $firstItemIsFleet = false;

            $cities = $this->getCitiesList();

            $fleetsList = $this->getFleetsList();

            foreach ($fleetsList as $key => $fleet) {
                $text = str_replace($fleet, ' ' . str_replace(' ', '_', $fleet) . ' ', $text);
                $fleetsList[$key] = str_replace($fleet, str_replace(' ', '_', $fleet), str_replace('ک', 'ك', str_replace('ی', 'ي', $fleet)));
            }

            foreach ($cities as $key => $city) {
                $text = str_replace($city, ' ' . str_replace(' ', '_', $city) . ' ', $text);
                $cities[$key] = str_replace($city, str_replace(' ', '_', $city), $city);
            }


            $text = str_replace(array("\r", "\n"), ' ', $text);

            $array = $this->removeItem(explode(' ', $text), ['', ' ']);

            $origin = $this->getOrigin($array);

            $text = str_replace($origin, '', $text);
            $mobileNumber = $this->getMobileNumber($text);

            $text = str_replace(array("\r", "\n"), ' ', str_replace($mobileNumber, '', $text));

            $array = explode(' ', $text);

            $freight = $this->getFreight($text);

            // زمان جستجوی ناوگان اگر چند ناوگان بود باید اول نگاه کند که بعد از مبدا شهر است یا ناوگان
            // اگر شهر بود و بعدش ناوگان بود یعنی ترتیب به این صورت است که: شهر مقصد بعد ناوگان
            // اگر ناوگان بود بعدش شهر یعنی ترتیب به این صورت است : ناوگان و شهر
            // اگر بعد از شهرها پشت سر هم و بعدش ناوگان پشت سر هم بود یعنی برای هر مقصد تمام ناوگان ها لازم است
            foreach ($array as $item)
                if ($item != $origin)
                    if (in_array($item, $fleetsList)) {
                        $firstItemIsFleet = true;
                        break;
                    } else if (in_array($item, $cities))
                        break;

            $fleetDestinations = [];

            if ($firstItemIsFleet) {
                // اول شروع کند ناوگان ها را پیدا کند بعد شهر را
                // تمام ناوگان ها تا رسیدن به شهر دریافت شوند

                $fleet = [];
                foreach ($array as $item) {
                    if (in_array($item, $fleetsList) && !in_array($item, $cities))
                        $fleet[] = $item;
                    else if ((in_array($item, $cities) && $item != $origin)) {
                        $fleetDestinations[] = [
                            'city' => $item,
                            'fleet' => $fleet
                        ];
                        $fleet = [];
                    }
                }
            }

            $allFleets = [];

            foreach ($fleetsList as $key => $fleetItem)
                $fleetsList[$key] = str_replace(' ', '_', $fleetItem);


            foreach ($fleetsList as $fleetItem)
                if (in_array($fleetItem, $array))
                    $allFleets[] = $fleetItem;

            // اول شروع کند ناوگان ها را پیدا کند بعد شهر را
            // تمام ناوگان ها تا رسیدن به شهر دریافت شوند
            if (!$firstItemIsFleet) {
                $city = '';
                $fleet = [];
                foreach ($array as $key => $item) {
                    if (in_array($item, $cities) && $item != $origin && $city == '') {
                        $city = $item;
                    } else if (in_array($item, $fleetsList) && !in_array($item, $cities)) {
                        $fleet[] = $item;
                    } else if (count($array) - 1 == $key || (in_array($item, $cities) && $item != $city)) {
                        $fleetDestinations[] = [
                            'city' => $city,
                            'fleet' => count($fleet) == 0 ? $allFleets : $fleet
                        ];
                        $city = '';

                        $fleet = [];
                        if (in_array($item, $cities))
                            $city = $item;
                    }
                }
            }

            // استخراج وزن
            $weight = $this->getWeight($text);

            $data = [
                'freight' => $freight,
                'fleetDestinationItems' => $fleetDestinations
            ];

            $cargoInfo = [];


            foreach ($data['fleetDestinationItems'] as $fleetDestinationItem) {

                $fleet = '';

                foreach ($fleetDestinationItem['fleet'] as $item)
                    $fleet .= ' ' . $item;


                $cargoInfo[] =
                    'از ' . $origin . "\n" .
                    ' به ' . $fleetDestinationItem['city'] . "\n" .
                    $mobileNumber . "\n" .
                    ' وزن ' . $weight . ' تن ' . "\n" .
                    ' کرایه ' . $data['freight']['freight'] . ' ' . $data['freight']['priceType'] . "\n" .
                    ' ' . $fleet;
            }

            return view('admin.storeCargoForm', compact('cargoInfo', 'originText'));
        } catch (\Exception $exception) {
            return back()->with('danger', 'خطایی رخ داده لطفا اطلاعات وارد شده را بررسی کنید');
        }
    }

    /*******************************************************************************************/
    /*******************************************************************************************/

    // دریافت قیمت
    private function getFreight($text)
    {
        $freight = 0;
        $prefixFreightConditions = array('صافی', 'صاف', 'هرتن', 'کرایه', 'قیمت');
        $postfixFreightConditions = array('صافی', 'صاف', 'هرتن', 'کرایه', 'م', 'میلیون');

        $priceType = '';

        foreach ($prefixFreightConditions as $item) {
            $pattern = "/$item\s+(\d+)/";
            if (preg_match($pattern, $text, $matches)) {
                $freight = $matches[1];
                break;
            }
        }

        if ($freight == 0)
            foreach ($postfixFreightConditions as $item) {
                $pattern = "/(\d+)\s+$item/";
                if (preg_match($pattern, $text, $matches)) {
                    $freight = $matches[1];
                    break;
                }
            }

        if (strpos($text, "[هرتن]"))
            $priceType = "هرتن";
        else if (strpos($text, "[صاف]"))
            $priceType = "صاف";
        else if (strpos($text, "[صافی]"))
            $priceType = "صافی";

        if ($freight == 0)
            $priceType = 'توافقی';
        else
            $freight = $freight < 1000 ? $freight * 1000000 : $freight;

        return [
            'freight' => $freight,
            'priceType' => $priceType
        ];
    }

    // دریافت وزن
    private function getWeight($text)
    {
        $weight = 0;
        $pattern = "/(\d+)\s+تن/";
        if (preg_match($pattern, $text, $matches))
            $weight = $matches[1];

        if ($weight == 0) {
            $pattern = "/وزن\s+(\d+)/";
            if (preg_match($pattern, $text, $matches))
                $weight = $matches[1];
        }
        return $weight;
    }

    // دریافت مبدا
    private function getOrigin(array $array, array $cities = null)
    {
        if ($cities == null)
            $cities = $this->getCitiesList();

        $prefixOriginConditions = array('[از]', '[مبدا]', '[بارگیری]');
        $postfixOriginConditions = array('[به]', '[مقصد]', '[تخلیه]');
        $originConditions = false;

        $origin = '';

        for ($i = 0; $i < count($array); $i++) {
            if (in_array($array[$i], $prefixOriginConditions) && !$originConditions)
                $originConditions = true;
            else if ($originConditions)
                if (in_array($array[$i], $cities) && strlen($origin) == 0)
                    return $array[$i];
        }

        if (strlen($origin) == 0) {
            $originConditions = false;
            for ($i = 0; $i < count($array); $i++) {
                if (in_array($array[$i], $postfixOriginConditions) && !$originConditions)
                    $originConditions = true;
                else if (in_array($array[$i], $cities) && !$originConditions && strlen($origin) == 0)
                    return $array[$i];
            }
        }

        if (strlen($origin) == 0) {
            for ($i = 0; $i < count($array); $i++)
                if (strlen($origin) == 0 && in_array($array[$i], $cities))
                    return $array[$i];
        }
        return $origin;
    }

    // دریافت مقصد
    private function getDestination(array $cities, array $array, $origin)
    {
        // شرط مهم مبدا و مقصد نباید یکی باشند
        $prefixDestinationConditions = array('تخلیه', 'به', 'مقصد');
        $postfixDestinationConditions = array('از');
        $destinationConditions = false;

        $destination = '';

        for ($i = 0; $i < count($array); $i++) {
            if (in_array($array[$i], $prefixDestinationConditions) && !$destinationConditions)
                $destinationConditions = true;
            else if ($destinationConditions)
                if (in_array($array[$i], $cities) && strlen($destination) == 0 && $array[$i] != $origin) {
                    $destination = $array[$i];
                    break;
                }
        }

        if (strlen($destination) == 0) {
            $destinationConditions = false;
            for ($i = 0; $i < count($array); $i++) {
                if (in_array($array[$i], $postfixDestinationConditions) && !$destinationConditions)
                    $destinationConditions = true;
                else if (in_array($array[$i], $cities) && !$destinationConditions && strlen($destination) == 0 && $array[$i] != $origin) {
                    $destination = $array[$i];
                    break;
                }
            }
        }

        if (strlen($destination) == 0) {
            for ($i = 0; $i < count($array); $i++)
                if (strlen($destination) == 0 && $array[$i] != $origin && in_array($array[$i], $cities)) {
                    $destination = $array[$i];
                    break;
                }
        }
        return $destination;
    }

    // دریافت شماره تلفن
    private function getMobileNumber($text)
    {
        $pattern = '/0\d{10}/';
        preg_match($pattern, $text, $matches);
        return isset($matches[0]) ? $matches[0] : '';
    }


    /**
     * @param array $array
     * @return array
     */
    private function removeItem(array $array, array $removeItems): array
    {
        $temp = [];
        foreach ($array as $key => $item)
            if (!in_array($item, $removeItems))
                $temp[] = $array[$key];

        return $temp;
    }


    public function storeLoad($data)
    {

        $counter = 0;
        $senderMobileNumber = $data[0]['mobileNumber'];
        try {
            if (BlockPhoneNumber::where('phoneNumber', $senderMobileNumber)->count()) {
                echo 'BlockPhoneNumber';
                return;
            }
        } catch (\Exception $exception) {
        }

        try {

            DB::beginTransaction();

            $load = new Load();
            $load->title = "بدون عنوان";
            $load->weight = $data[0]['weight'];
            $load->width = 0;
            $load->length = 0;
            $load->height = 0;
            $load->loadingAddress = '';
            $load->dischargeAddress = '';
            $load->senderMobileNumber = $senderMobileNumber;
            $load->receiverMobileNumber = '';
            $load->insuranceAmount = 0;
            $load->suggestedPrice = isset($data[0]['freight']['freight']) ? $data[0]['freight']['freight'] : 1;
            $load->marketing_price = 0;
            $load->emergencyPhone = $senderMobileNumber;
            $load->dischargeTime = '';
            $load->fleet_id = $data[0]['fleets'][0];
            $load->load_type_id = 0;
            $load->tenderTimeDuration = 0;
            $load->packing_type_id = 0;
            $load->loadPic = "noImage";
            $load->user_id = auth()->id();
            $load->userType = ROLE_OPERATOR;
            $load->loadMode = 'outerCity';
            $load->loadingHour = 0;
            $load->loadingMinute = 0;
            $load->numOfTrucks = 1;


            $data[0]['origin'] = str_replace('_', ' ', $data[0]['origin']);
            $data[0]['destination'] = str_replace('_', ' ', $data[0]['destination']);


            $load->origin_city_id = $this->getCityId($data[0]['origin']);
            $load->destination_city_id = $this->getCityId($data[0]['destination']);


            $load->fromCity = $this->getCityName($load->origin_city_id);
            $load->toCity = $this->getCityName($load->destination_city_id);

            $load->loadingDate = gregorianDateToPersian(date('Y-m-d', time()), '-');
            $load->time = time();

            try {
                $city = City::find($load->origin_city_id);
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
            $load->description = '';

            $load->priceBased = $data[0]['freight']['priceType'];
            $load->operator_id = auth()->id();
            $load->proposedPriceForDriver = $data[0]['freight']['freight'];
            $load->mobileNumberForCoordination = $data[0]['mobileNumber'];
            $load->storeFor = ROLE_DRIVER;
            $load->status = ON_SELECT_DRIVER;
            $load->deliveryTime = 24;

            $load->urgent = 0;
            $load->save();


            if (isset($load->id) && isset($data[0]['fleets'])) {

                foreach ($data[0]['fleets'] as $item) {

                    $item = str_replace('_', ' ', $item);

                    $fleet_id = Fleet::where('title', $item)->first();
                    if (!isset($fleet_id->id)) {
                        $fleet_id = Fleet::where('title', str_replace('ك', 'ک', $item))->first();
                    }
                    if (!isset($fleet_id->id)) {
                        $fleet_id = Fleet::where('title', str_replace('ي', 'ی', $item))->first();
                    }
                    if (!isset($fleet_id->id)) {
                        $fleet_id = Fleet::where('title', str_replace('ي', 'ی', str_replace('ك', 'ک', $item)))->first();
                    }
                    $persian_date = gregorianDateToPersian(Carbon::now());

                    if (isset($fleet_id->id)) {
                        $fleetLoad = new FleetLoad();
                        $fleetLoad->load_id = $load->id;
                        $fleetLoad->fleet_id = $fleet_id->id;
                        $fleetLoad->numOfFleets = 1;
                        $fleetLoad->userType = $load->userType;
                        $fleetLoad->save();

                        try {
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
                }

                try {

                    $load->fleets = FleetLoad::join('fleets', 'fleets.id', 'fleet_loads.fleet_id')
                        ->where('fleet_loads.load_id', $load->id)
                        ->select('fleet_id', 'userType', 'suggestedPrice', 'numOfFleets', 'pic', 'title')
                        ->get();

                    $load->save();
                } catch (\Exception $exception) {
                    Log::emergency("---------------------------------------------------------");
                    Log::emergency($exception->getMessage());
                    Log::emergency("---------------------------------------------------------");
                }

                try {
                    $backup = new LoadBackup();
                    $backup->id = $load->id;
                    $backup->title = $load->title;
                    $backup->weight = $load->weight;
                    $backup->width = $load->width;
                    $backup->length = $load->length;
                    $backup->height = $load->height;
                    $backup->loadingAddress = $load->loadingAddress;
                    $backup->dischargeAddress = $load->dischargeAddress;
                    $backup->senderMobileNumber = $load->senderMobileNumber;
                    $backup->receiverMobileNumber = $load->receiverMobileNumber;
                    $backup->insuranceAmount = $load->insuranceAmount;
                    $backup->suggestedPrice = $load->suggestedPrice;
                    $backup->marketing_price = 0;
                    $backup->emergencyPhone = $load->emergencyPhone;
                    $backup->dischargeTime = $load->dischargeTime;
                    $backup->fleet_id = $load->fleet_id;
                    $backup->load_type_id = $load->load_type_id;
                    $backup->tenderTimeDuration = $load->tenderTimeDuration;
                    $backup->packing_type_id = $load->packing_type_id;
                    $backup->loadPic = $load->loadPic;
                    $backup->user_id = $load->user_id;
                    $backup->loadMode = $load->loadMode;
                    $backup->loadingHour = $load->loadingHour;
                    $backup->loadingMinute = $load->loadingMinute;
                    $backup->numOfTrucks = $load->numOfTrucks;
                    $backup->origin_city_id = $load->origin_city_id;
                    $backup->destination_city_id = $load->destination_city_id;
                    $backup->fromCity = $load->fromCity;
                    $backup->toCity = $load->toCity;
                    $backup->loadingDate = $load->loadingDate;
                    $backup->time = $load->time;
                    $backup->latitude = $load->latitude;
                    $backup->longitude = $load->longitude;
                    $backup->weightPerTruck = $load->weightPerTruck;
                    $backup->bulk = $load->bulk;
                    $backup->dangerousProducts = $load->dangerousProducts;
                    $backup->origin_state_id = $load->origin_state_id;
                    $backup->description = $load->description;
                    $backup->priceBased = $load->priceBased;
                    $backup->bearing_id = $load->bearing_id;
                    $backup->proposedPriceForDriver = $load->proposedPriceForDriver;
                    $backup->operator_id = $load->operator_id;
                    $backup->userType = $load->userType;
                    $backup->origin_longitude = $load->origin_longitude;
                    $backup->destination_longitude = $load->destination_longitude;
                    $backup->mobileNumberForCoordination = $load->mobileNumberForCoordination;
                    $backup->storeFor = $load->storeFor;
                    $backup->status = $load->status;
                    $backup->fleets = $load->fleets;
                    $backup->deliveryTime = $load->deliveryTime;
                    $backup->save();

                    $counter++;
                } catch (\Exception $e) {
                    Log::emergency("========================= Load Backup ==================================");
                    Log::emergency($e->getMessage());
                    Log::emergency("==============================================================");
                }
            }

            DB::commit();
        } catch (\Exception $exception) {

            DB::rollBack();

            Log::emergency("----------------------ثبت بار جدید-----------------------");
            Log::emergency($exception->getMessage());
            Log::emergency("---------------------------------------------------------");
        }


        return redirect(url('admin/storeCargoConvertForm'))->with('success', $counter . 'بار ثبت شد');
    }


    /*****************************************************************************************************/
    // تمیز کردن متن
    private function cleanText($text)
    {
        $text = str_replace('_', ' ', str_replace('-', ' ', str_replace(',', ' ', $text)));

        $fleetsList = $this->getFleetsList();
        $cities = $this->getCitiesList();

        foreach ($fleetsList as $key => $fleet) {
            $fleetsList[$key] = ' ' . str_replace(' ', '_', $fleet) . ' ';
            $text = str_replace($fleet, ' ' . str_replace(' ', '_', $fleet) . ' ', $text);
        }

        foreach ($cities as $key => $city) {
            $cities[$key] = ' ' . str_replace(' ', '_', $city) . ' ';
            $text = str_replace($city, ' ' . str_replace(' ', '_', $city) . ' ', $text);
        }
        return $text;
    }


    /*******************************************************************************************/
    // کانال ها
    public function channels()
    {

        //        $client = new Client();
        //        $res = $client->request('GET', 'http://5.78.107.150:8000/channels');
        //        dd($res->getBody());
        $result = json_decode(file_get_contents('http://5.78.107.150:8000/channels'), true);
        return view('admin.channels', compact('result'));
    }

    public function removeChannel($channel)
    {
        $client = new Client();
        $res = $client->request('POST', 'http://5.78.107.150:8000/channel/' . $channel . '/delete');
        return back()->with('success', 'کانال ' . $channel . ' حذف شد');
    }

    public function newChannel(Request $request)
    {
        $client = new Client();
        $res = $client->request('POST', 'http://5.78.107.150:8000/channel/' . $request->channelName . '/create');
        return back()->with('success', 'کانال ' . $request->channelName . ' اضافه شد');
    }

    public function channelsData()
    {
        $data = BotTest::orderby('id', 'desc')->paginate(30);
        return view('admin.channelsData', compact('data'));
    }

    // لیست بارهای رد شده
    public function rejectedCargoFromCargoList()
    {
        $cargoList = CargoConvertList::where('rejected', 1)->orderBy('id', 'desc')->paginate(20);
        return view('admin.rejectedCargoFromCargoList', compact('cargoList'));
    }

    public function allRejectedCargoCount()
    {
        $cargoList = CargoConvertList::with('operator')
            ->where('rejected', 1)
            //            ->select('operator_id','persian_date', DB::raw('sum(calls) as countOfCalls'))
            ->get();
        $groupBys = $cargoList->groupBy('operator.lastName');

        return view('admin.rejectedCargoFromCargoListCount', compact('groupBys'));
    }
    public function rejectCargoCount()
    {
        $persian_date = gregorianDateToPersian(date('Y/m/d', time()), '/');
        $rejects = RejectCargoOperator::where('persian_date', $persian_date)->get();
        return view('admin.rejectCargo', compact('rejects', 'persian_date'));
    }

    /********************************************************************************************************/
    // تعیین دسترسی اپراتور ها به بارها براساس ناوگان
    public function operatorCargoListAccess(Request $request, User $user)
    {
        try {

            DB::beginTransaction();

            OperatorCargoListAccess::where('user_id', $user->id)->delete();

            foreach ($request->cargoAccess as $cargoAccess) {
                $OperatorCargoListAccess = new OperatorCargoListAccess();
                $OperatorCargoListAccess->user_id = $user->id;
                $OperatorCargoListAccess->fleet_id = $cargoAccess;
                $OperatorCargoListAccess->save();
            }

            DB::commit();

            return back()->with('success', 'دسترسی اپراتور به بار براساس ناوگان ثبت شد');
        } catch (\Exception $exception) {

            DB::rollBack();
        }

        return back()->with('danger', 'خطا در ثبت دسترسی اپراتور به بار براساس ناوگان!');
    }
}
