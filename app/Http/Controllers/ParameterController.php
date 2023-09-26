<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ParameterController extends Controller
{
    /************************************************************************************************************/
// پارامترهای مربوط به شهرها و استان ها
    /************************************************************************************************************/
    private $arrayOfStates;// آرایه مربوط به لیست استان ها
    private $arrayOfCities;


    // درخواست لیست استان ها
    public function requestStatesList()
    {
        $this->setArrayOfStates();
        return $this->getArrayOfStates();
    }

    public function requestCitiesList(Request $request)
    {
        $state_id = $request->state_id;

        $this->setArrayOfCities($state_id);

        return $this->getArrayOfCities();
    }

    // دریافت و ست کردن استان ها
    public function getArrayOfStates()
    {
        return $this->arrayOfStates;
    }

    // دریافت یک استان
    public function getStateName($state_id)
    {
        if ($state_id == 0)
            return "انتخاب نشده";
        return $this->arrayOfStates[$state_id];
    }

    public function setArrayOfStates()
    {
        $this->arrayOfStates[1] = 'آذربایجان شرقی';
        $this->arrayOfStates[2] = 'آذربایجان غربی';
        $this->arrayOfStates[3] = 'اردبیل';
        $this->arrayOfStates[4] = 'اصفهان';
        $this->arrayOfStates[5] = 'البرز';
        $this->arrayOfStates[6] = 'ایلام';
        $this->arrayOfStates[7] = 'بوشهر';
        $this->arrayOfStates[8] = 'تهران';
        $this->arrayOfStates[9] = 'چهارمحال و بختیاری';
        $this->arrayOfStates[10] = 'خراسان جنوبی';
        $this->arrayOfStates[11] = 'خراسان رضوی';
        $this->arrayOfStates[12] = 'خراسان شمالی';
        $this->arrayOfStates[13] = 'خوزستان';
        $this->arrayOfStates[14] = 'زنجان';
        $this->arrayOfStates[15] = 'سمنان';
        $this->arrayOfStates[16] = 'سیستان و بلوچستان';
        $this->arrayOfStates[17] = 'فارس';
        $this->arrayOfStates[18] = 'قزوین';
        $this->arrayOfStates[19] = 'قم';
        $this->arrayOfStates[20] = 'کردستان';
        $this->arrayOfStates[21] = 'کرمان';
        $this->arrayOfStates[22] = 'کرمانشاه';
        $this->arrayOfStates[23] = 'کهگیلویه و بویراحمد';
        $this->arrayOfStates[24] = 'گلستان';
        $this->arrayOfStates[25] = 'گیلان';
        $this->arrayOfStates[26] = 'لرستان';
        $this->arrayOfStates[27] = 'مازندران';
        $this->arrayOfStates[28] = 'مرکزی';
        $this->arrayOfStates[29] = 'هرمزگان';
        $this->arrayOfStates[30] = 'همدان';
        $this->arrayOfStates[31] = 'یزد';
    }

    // دریافت و ست کردن شهر ها
    public function getArrayOfCities()
    {
        return $this->arrayOfCities;
    }

    // دریافت نام یک شهر
    public function getCityName($state_id, $city_id)
    {
        if ($city_id == 0)
            return "انتخاب نشده";
        $this->setArrayOfCities($state_id);
        return $this->arrayOfCities[$city_id];
    }

    public function setArrayOfCities($state_id)
    {
        if (!$state_id)
            $this->arrayOfCities[1] = "انتخاب نشده";

        $i = 1;

        switch ($state_id) {
            case '1': //  آذربایجان شرغی
                $this->arrayOfCities[$i++] = "اهر";

                $this->arrayOfCities[$i++] = "تبريز";

                $this->arrayOfCities[$i++] = "مراغه";

                $this->arrayOfCities[$i++] = "سراب";

                $this->arrayOfCities[$i++] = "مرند";

                $this->arrayOfCities[$i++] = "ميانه";

                $this->arrayOfCities[$i++] = "هشترود";

                $this->arrayOfCities[$i++] = "بناب";

                $this->arrayOfCities[$i++] = "شبستر";

                $this->arrayOfCities[$i++] = "بستان آباد";

                $this->arrayOfCities[$i++] = "کليبر";

                $this->arrayOfCities[$i++] = "هريس";

                $this->arrayOfCities[$i++] = "جلفا";

                $this->arrayOfCities[$i++] = "ملکان";

                $this->arrayOfCities[$i++] = "آذرشهر";

                $this->arrayOfCities[$i++] = "ورزقان";

                $this->arrayOfCities[$i++] = "عجب شير";

                $this->arrayOfCities[$i++] = "اسکو";

                $this->arrayOfCities[$i++] = "چاراويماق";

                $this->arrayOfCities[$i++] = "خداآفرين";

                break;
            case '2': // آذربایجان غربی
                $this->arrayOfCities[$i++] = "اروميه";

                $this->arrayOfCities[$i++] = "خوئ";

                $this->arrayOfCities[$i++] = "پيرانشهر";

                $this->arrayOfCities[$i++] = "سلماس";

                $this->arrayOfCities[$i++] = "سردشت";

                $this->arrayOfCities[$i++] = "مهاباد";

                $this->arrayOfCities[$i++] = "ماكو";

                $this->arrayOfCities[$i++] = "مياندوآب";

                $this->arrayOfCities[$i++] = "نقده";

                $this->arrayOfCities[$i++] = "بوكان";

                $this->arrayOfCities[$i++] = "شاهين دژ";

                $this->arrayOfCities[$i++] = "پلدشت";

                $this->arrayOfCities[$i++] = "تكاب";

                $this->arrayOfCities[$i++] = "اشنويه";

                $this->arrayOfCities[$i++] = "چالدران";

                $this->arrayOfCities[$i++] = "چايپاره";

                $this->arrayOfCities[$i++] = "شوط";

                break;
            case '3':// اردبیل
                $this->arrayOfCities[$i++] = "اردبيل";

                $this->arrayOfCities[$i++] = "بيله سوار";

                $this->arrayOfCities[$i++] = "خلخال";

                $this->arrayOfCities[$i++] = "مشگين شهر";

                $this->arrayOfCities[$i++] = "گرمي";

                $this->arrayOfCities[$i++] = "پارس آباد";

                $this->arrayOfCities[$i++] = "كوثر";

                $this->arrayOfCities[$i++] = "نمين";

                $this->arrayOfCities[$i++] = "نير";

                $this->arrayOfCities[$i++] = "سرعين";

                break;
            case '4': // اصفهان
                $this->arrayOfCities[$i++] = "اردستان";

                $this->arrayOfCities[$i++] = "اصفهان";

                $this->arrayOfCities[$i++] = "خميني شهر";

                $this->arrayOfCities[$i++] = "خوانسار";

                $this->arrayOfCities[$i++] = "سميرم";

                $this->arrayOfCities[$i++] = "فريدن";

                $this->arrayOfCities[$i++] = "فريدونشهر";

                $this->arrayOfCities[$i++] = "فلاورجان";

                $this->arrayOfCities[$i++] = "شهرضا";

                $this->arrayOfCities[$i++] = "کاشان";

                $this->arrayOfCities[$i++] = "گلپايگان";

                $this->arrayOfCities[$i++] = "لنجان";

                $this->arrayOfCities[$i++] = "نايين";

                $this->arrayOfCities[$i++] = "نجف آباد";

                $this->arrayOfCities[$i++] = "نطنز";

                $this->arrayOfCities[$i++] = "شاهين شهروميمه";

                $this->arrayOfCities[$i++] = "مبارکه";

                $this->arrayOfCities[$i++] = "آران وبيدگل";

                $this->arrayOfCities[$i++] = "برخوار";

                $this->arrayOfCities[$i++] = "تيران وکرون";

                $this->arrayOfCities[$i++] = "چادگان";

                $this->arrayOfCities[$i++] = "دهاقان";

                $this->arrayOfCities[$i++] = "خور و بيابانک";

                $this->arrayOfCities[$i++] = "بو يين و مياندشت";

                break;
            case '5': // البرز
                $this->arrayOfCities[$i++] = "کرج";

                $this->arrayOfCities[$i++] = "ساوجبلاغ";

                $this->arrayOfCities[$i++] = "نظرآباد";

                $this->arrayOfCities[$i++] = "طالقان";

                $this->arrayOfCities[$i++] = "اشتهارد";

                $this->arrayOfCities[$i++] = "فرديس";

                break;
            case '6': // ایلام
                $this->arrayOfCities[$i++] = "ايلام";

                $this->arrayOfCities[$i++] = "دره شهر";

                $this->arrayOfCities[$i++] = "دهلران";

                $this->arrayOfCities[$i++] = "چرداول";

                $this->arrayOfCities[$i++] = "مهران";

                $this->arrayOfCities[$i++] = "آبدانان";

                $this->arrayOfCities[$i++] = "ايوان";

                $this->arrayOfCities[$i++] = "ملكشاهي";

                $this->arrayOfCities[$i++] = "سيروان";

                $this->arrayOfCities[$i++] = "بدره";

                break;
            case '7': // بوشهر
                $this->arrayOfCities[$i++] = "بوشهر";

                $this->arrayOfCities[$i++] = "تنگستان";

                $this->arrayOfCities[$i++] = "دشتستان";

                $this->arrayOfCities[$i++] = "دشتي";

                $this->arrayOfCities[$i++] = "دير";

                $this->arrayOfCities[$i++] = "كنگان";

                $this->arrayOfCities[$i++] = "گناوه";

                $this->arrayOfCities[$i++] = "ديلم";

                $this->arrayOfCities[$i++] = "جم";

                $this->arrayOfCities[$i++] = "عسلويه";

                break;
            case '8': // تهران
                $this->arrayOfCities[$i++] = "تهران";

                $this->arrayOfCities[$i++] = "دماوند";

                $this->arrayOfCities[$i++] = "رئ";

                $this->arrayOfCities[$i++] = "ملارد";

                $this->arrayOfCities[$i++] = "پيشوا";

                $this->arrayOfCities[$i++] = "بهارستان";

                $this->arrayOfCities[$i++] = "پرديس";

                $this->arrayOfCities[$i++] = "قرچک";

                $this->arrayOfCities[$i++] = "شميرانات";

                $this->arrayOfCities[$i++] = "ورامين";

                $this->arrayOfCities[$i++] = "شهريار";

                $this->arrayOfCities[$i++] = "اسلامشهر";

                $this->arrayOfCities[$i++] = "رباطكريم";

                $this->arrayOfCities[$i++] = "پاكدشت";

                $this->arrayOfCities[$i++] = "فيروزكوه";

                $this->arrayOfCities[$i++] = "قدس";

                break;
            case '9': // چهارمحال و بختیاری
                $this->arrayOfCities[$i++] = "بروجن";

                $this->arrayOfCities[$i++] = "شهركرد";

                $this->arrayOfCities[$i++] = "فارسان";

                $this->arrayOfCities[$i++] = "كوهرنگ";

                $this->arrayOfCities[$i++] = "لردگان";

                $this->arrayOfCities[$i++] = "اردل";

                $this->arrayOfCities[$i++] = "كيار";

                $this->arrayOfCities[$i++] = "سامان";

                $this->arrayOfCities[$i++] = "بن";

                break;
            case '10': //خراسان جنوبی
                $this->arrayOfCities[$i++] = "بيرجند";

                $this->arrayOfCities[$i++] = "درميان";

                $this->arrayOfCities[$i++] = "سربيشه";

                $this->arrayOfCities[$i++] = "قائنات";

                $this->arrayOfCities[$i++] = "نهبندان";

                $this->arrayOfCities[$i++] = "سرايان";

                $this->arrayOfCities[$i++] = "فردوس";

                $this->arrayOfCities[$i++] = "بشرويه";

                $this->arrayOfCities[$i++] = "زيرکوه";

                $this->arrayOfCities[$i++] = "خوسف";

                $this->arrayOfCities[$i++] = "طبس";

                break;
            case '11': // خراسان رزوی
                $this->arrayOfCities[$i++] = "تايباد";

                $this->arrayOfCities[$i++] = "تربت حيدريه";

                $this->arrayOfCities[$i++] = "تربت جام";

                $this->arrayOfCities[$i++] = "درگز";

                $this->arrayOfCities[$i++] = "سبزوار";

                $this->arrayOfCities[$i++] = "قوچان";

                $this->arrayOfCities[$i++] = "كاشمر";

                $this->arrayOfCities[$i++] = "گناباد";

                $this->arrayOfCities[$i++] = "مشهد";

                $this->arrayOfCities[$i++] = "نيشابور";

                $this->arrayOfCities[$i++] = "چناران";

                $this->arrayOfCities[$i++] = "خواف";

                $this->arrayOfCities[$i++] = "سرخس";

                $this->arrayOfCities[$i++] = "فريمان";

                $this->arrayOfCities[$i++] = "بردسكن";

                $this->arrayOfCities[$i++] = "رشتخوار";

                $this->arrayOfCities[$i++] = "خليل آباد";

                $this->arrayOfCities[$i++] = "كلات";

                $this->arrayOfCities[$i++] = "مه ولات";

                $this->arrayOfCities[$i++] = "بجستان";

                $this->arrayOfCities[$i++] = "بينالود";

                $this->arrayOfCities[$i++] = "فيروزه";

                $this->arrayOfCities[$i++] = "جغتاي";

                $this->arrayOfCities[$i++] = "زاوه";

                $this->arrayOfCities[$i++] = "جوين";

                $this->arrayOfCities[$i++] = "خوشاب";

                $this->arrayOfCities[$i++] = "باخرز";

                $this->arrayOfCities[$i++] = "داورزن";

                break;
            case '12': // خراسان شمالی
                $this->arrayOfCities[$i++] = "اسفراين";

                $this->arrayOfCities[$i++] = "بجنورد";

                $this->arrayOfCities[$i++] = "جاجرم";

                $this->arrayOfCities[$i++] = "شيروان";

                $this->arrayOfCities[$i++] = "فاروج";

                $this->arrayOfCities[$i++] = "مانه وسملقان";

                $this->arrayOfCities[$i++] = "گرمه";

                $this->arrayOfCities[$i++] = "راز و جرگلان";

                break;
            case '13': // خوزستان
                $this->arrayOfCities[$i++] = "آبادان";

                $this->arrayOfCities[$i++] = "انديمشک";

                $this->arrayOfCities[$i++] = "اهواز";

                $this->arrayOfCities[$i++] = "ايذه";

                $this->arrayOfCities[$i++] = "بندرماهشهر";

                $this->arrayOfCities[$i++] = "بهبهان";

                $this->arrayOfCities[$i++] = "خرمشهر";

                $this->arrayOfCities[$i++] = "دزفول";

                $this->arrayOfCities[$i++] = "دشت آزادگان";

                $this->arrayOfCities[$i++] = "رامهرمز";

                $this->arrayOfCities[$i++] = "شادگان";

                $this->arrayOfCities[$i++] = "شوشتر";

                $this->arrayOfCities[$i++] = "مسجدسليمان";

                $this->arrayOfCities[$i++] = "شوش";

                $this->arrayOfCities[$i++] = "باغ ملک";

                $this->arrayOfCities[$i++] = "اميديه";

                $this->arrayOfCities[$i++] = "لالي";

                $this->arrayOfCities[$i++] = "هنديجان";

                $this->arrayOfCities[$i++] = "رامشير";

                $this->arrayOfCities[$i++] = "انديکا";

                $this->arrayOfCities[$i++] = "هفتگل";

                $this->arrayOfCities[$i++] = "گتوند";

                $this->arrayOfCities[$i++] = "هويزه";

                $this->arrayOfCities[$i++] = "باوي";

                $this->arrayOfCities[$i++] = "حميديه";

                $this->arrayOfCities[$i++] = "آغاجاري";

                $this->arrayOfCities[$i++] = "کارون";

                break;
            case '14': // زنجان
                $this->arrayOfCities[$i++] = "ابهر";

                $this->arrayOfCities[$i++] = "خدابنده";

                $this->arrayOfCities[$i++] = "زنجان";

                $this->arrayOfCities[$i++] = "ايجرود";

                $this->arrayOfCities[$i++] = "خرمدره";

                $this->arrayOfCities[$i++] = "طارم";

                $this->arrayOfCities[$i++] = "ماهنشان";

                $this->arrayOfCities[$i++] = "سلطانيه";

                break;
            case '15': // سمنان
                $this->arrayOfCities[$i++] = "دامغان";

                $this->arrayOfCities[$i++] = "سمنان";

                $this->arrayOfCities[$i++] = "شاهرود";

                $this->arrayOfCities[$i++] = "گرمسار";

                $this->arrayOfCities[$i++] = "مهدئ شهر";

                $this->arrayOfCities[$i++] = "آرادان";

                $this->arrayOfCities[$i++] = "ميامي";

                $this->arrayOfCities[$i++] = "سرخه";

                break;
            case '16': // سیستان و بلوچستان
                $this->arrayOfCities[$i++] = "ايرانشهر";

                $this->arrayOfCities[$i++] = "چابهار";

                $this->arrayOfCities[$i++] = "خاش";

                $this->arrayOfCities[$i++] = "زابل";

                $this->arrayOfCities[$i++] = "زاهدان";

                $this->arrayOfCities[$i++] = "سراوان";

                $this->arrayOfCities[$i++] = "نيك شهر";

                $this->arrayOfCities[$i++] = "سرباز";

                $this->arrayOfCities[$i++] = "كنارك";

                $this->arrayOfCities[$i++] = "زهك";

                $this->arrayOfCities[$i++] = "هيرمند";

                $this->arrayOfCities[$i++] = "دلگان";

                $this->arrayOfCities[$i++] = "مهرستان";

                $this->arrayOfCities[$i++] = "سيب و سوران";

                $this->arrayOfCities[$i++] = "نيمروز";

                $this->arrayOfCities[$i++] = "هامون";

                $this->arrayOfCities[$i++] = "ميرجاوه";

                $this->arrayOfCities[$i++] = "قصرقند";

                $this->arrayOfCities[$i++] = "فنوج";

                break;
            case '17': // فارس
                $this->arrayOfCities[$i++] = "آباده";

                $this->arrayOfCities[$i++] = "استهبان";

                $this->arrayOfCities[$i++] = "اقليد";

                $this->arrayOfCities[$i++] = "جهرم";

                $this->arrayOfCities[$i++] = "داراب";

                $this->arrayOfCities[$i++] = "سپيدان";

                $this->arrayOfCities[$i++] = "شيراز";

                $this->arrayOfCities[$i++] = "فسا";

                $this->arrayOfCities[$i++] = "فيروزآباد";

                $this->arrayOfCities[$i++] = "کازرون";

                $this->arrayOfCities[$i++] = "لارستان";

                $this->arrayOfCities[$i++] = "مرودشت";

                $this->arrayOfCities[$i++] = "ممسني";

                $this->arrayOfCities[$i++] = "ني ريز";

                $this->arrayOfCities[$i++] = "لامرد";

                $this->arrayOfCities[$i++] = "بوانات";

                $this->arrayOfCities[$i++] = "ارسنجان";

                $this->arrayOfCities[$i++] = "خرم بيد";

                $this->arrayOfCities[$i++] = "زرين دشت";

                $this->arrayOfCities[$i++] = "قيروکارزين";

                $this->arrayOfCities[$i++] = "مهر";

                $this->arrayOfCities[$i++] = "فراشبند";

                $this->arrayOfCities[$i++] = "پاسارگاد";

                $this->arrayOfCities[$i++] = "خنج";

                $this->arrayOfCities[$i++] = "سروستان";

                $this->arrayOfCities[$i++] = "رستم";

                $this->arrayOfCities[$i++] = "گراش";

                $this->arrayOfCities[$i++] = "کوار";

                $this->arrayOfCities[$i++] = "خرامه";

                break;
            case '18': // قزوین
                $this->arrayOfCities[$i++] = "بوئين زهرا";

                $this->arrayOfCities[$i++] = "تاكستان";

                $this->arrayOfCities[$i++] = "قزوين";

                $this->arrayOfCities[$i++] = "آبيك";

                $this->arrayOfCities[$i++] = "آوج";

                $this->arrayOfCities[$i++] = "البرز";

                break;
            case '19': // قم
                $this->arrayOfCities[$i++] = "قم";

                break;
            case '20': // کردستان
                $this->arrayOfCities[$i++] = "بانه";

                $this->arrayOfCities[$i++] = "سقز";

                $this->arrayOfCities[$i++] = "بيجار";

                $this->arrayOfCities[$i++] = "سنندج";

                $this->arrayOfCities[$i++] = "قروه";

                $this->arrayOfCities[$i++] = "مريوان";

                $this->arrayOfCities[$i++] = "ديواندره";

                $this->arrayOfCities[$i++] = "كامياران";

                $this->arrayOfCities[$i++] = "سروآباد";

                $this->arrayOfCities[$i++] = "دهگلان";

                break;
            case '21': // کرمان
                $this->arrayOfCities[$i++] = "بافت";

                $this->arrayOfCities[$i++] = "بم";

                $this->arrayOfCities[$i++] = "جيرفت";

                $this->arrayOfCities[$i++] = "رفسنجان";

                $this->arrayOfCities[$i++] = "زرند";

                $this->arrayOfCities[$i++] = "سيرجان";

                $this->arrayOfCities[$i++] = "شهربابك";

                $this->arrayOfCities[$i++] = "كرمان";

                $this->arrayOfCities[$i++] = "كهنوج";

                $this->arrayOfCities[$i++] = "بردسير";

                $this->arrayOfCities[$i++] = "راور";

                $this->arrayOfCities[$i++] = "عنبرآباد";

                $this->arrayOfCities[$i++] = "منوجان";

                $this->arrayOfCities[$i++] = "كوهبنان";

                $this->arrayOfCities[$i++] = "رودبارجنوب";

                $this->arrayOfCities[$i++] = "قلعه گنج";

                $this->arrayOfCities[$i++] = "ريگان";

                $this->arrayOfCities[$i++] = "رابر";

                $this->arrayOfCities[$i++] = "فهرج";

                $this->arrayOfCities[$i++] = "انار";

                $this->arrayOfCities[$i++] = "نرماشير";

                $this->arrayOfCities[$i++] = "فارياب";

                $this->arrayOfCities[$i++] = "ارزوئيه";

                break;
            case '22': // کرمانشاه
                $this->arrayOfCities[$i++] = "اسلام آبادغرب";

                $this->arrayOfCities[$i++] = "کرمانشاه";

                $this->arrayOfCities[$i++] = "پاوه";

                $this->arrayOfCities[$i++] = "سرپل ذهاب";

                $this->arrayOfCities[$i++] = "سنقر";

                $this->arrayOfCities[$i++] = "قصرشيرين";

                $this->arrayOfCities[$i++] = "کنگاور";

                $this->arrayOfCities[$i++] = "گيلانغرب";

                $this->arrayOfCities[$i++] = "جوانرود";

                $this->arrayOfCities[$i++] = "صحنه";

                $this->arrayOfCities[$i++] = "هرسين";

                $this->arrayOfCities[$i++] = "ثلاث باباجاني";

                $this->arrayOfCities[$i++] = "دالاهو";

                $this->arrayOfCities[$i++] = "روانسر";

                break;
            case '23': // کهگیلویه و بویراحمد
                $this->arrayOfCities[$i++] = "بويراحمد";

                $this->arrayOfCities[$i++] = "كهگيلويه";

                $this->arrayOfCities[$i++] = "گچساران";

                $this->arrayOfCities[$i++] = "دنا";

                $this->arrayOfCities[$i++] = "بهمئي";

                $this->arrayOfCities[$i++] = "چرام";

                $this->arrayOfCities[$i++] = "باشت";

                $this->arrayOfCities[$i++] = "لنده";

                break;
            case '24': // گلستان
                $this->arrayOfCities[$i++] = "بندرگز";

                $this->arrayOfCities[$i++] = "تركمن";

                $this->arrayOfCities[$i++] = "كردكوئ";

                $this->arrayOfCities[$i++] = "گرگان";

                $this->arrayOfCities[$i++] = "علي آباد";

                $this->arrayOfCities[$i++] = "گنبدكاووس";

                $this->arrayOfCities[$i++] = "كلاله";

                $this->arrayOfCities[$i++] = "مينودشت";

                $this->arrayOfCities[$i++] = "آق قلا";

                $this->arrayOfCities[$i++] = "آزادشهر";

                $this->arrayOfCities[$i++] = "راميان";

                $this->arrayOfCities[$i++] = "مراوه تپه";

                $this->arrayOfCities[$i++] = "گميشان";

                $this->arrayOfCities[$i++] = "گاليكش";

                break;
            case '25': //  گیلان
                $this->arrayOfCities[$i++] = "آستارا";

                $this->arrayOfCities[$i++] = "آستانه اشرفيه";

                $this->arrayOfCities[$i++] = "بندرانزلي";

                $this->arrayOfCities[$i++] = "طوالش";

                $this->arrayOfCities[$i++] = "رشت";

                $this->arrayOfCities[$i++] = "رودبار";

                $this->arrayOfCities[$i++] = "رودسر";

                $this->arrayOfCities[$i++] = "لاهيجان";

                $this->arrayOfCities[$i++] = "صومعه سرا";

                $this->arrayOfCities[$i++] = "فومن";

                $this->arrayOfCities[$i++] = "لنگرود";

                $this->arrayOfCities[$i++] = "شفت";

                $this->arrayOfCities[$i++] = "املش";

                $this->arrayOfCities[$i++] = "رضوانشهر";

                $this->arrayOfCities[$i++] = "سياهكل";

                $this->arrayOfCities[$i++] = "ماسال";

                break;
            case '26': // لرستان
                $this->arrayOfCities[$i++] = "اليگودرز";

                $this->arrayOfCities[$i++] = "بروجرد";

                $this->arrayOfCities[$i++] = "خرم آباد";

                $this->arrayOfCities[$i++] = "دلفان";

                $this->arrayOfCities[$i++] = "دورود";

                $this->arrayOfCities[$i++] = "کوهدشت";

                $this->arrayOfCities[$i++] = "ازنا";

                $this->arrayOfCities[$i++] = "پلدختر";

                $this->arrayOfCities[$i++] = "سلسله";

                $this->arrayOfCities[$i++] = "دوره";

                $this->arrayOfCities[$i++] = "رومشکان";

                break;
            case '27': //  مازندران
                $this->arrayOfCities[$i++] = "آمل";

                $this->arrayOfCities[$i++] = "بابل";

                $this->arrayOfCities[$i++] = "بهشهر";

                $this->arrayOfCities[$i++] = "تنكابن";

                $this->arrayOfCities[$i++] = "رامسر";

                $this->arrayOfCities[$i++] = "سارئ";

                $this->arrayOfCities[$i++] = "نور";

                $this->arrayOfCities[$i++] = "نوشهر";

                $this->arrayOfCities[$i++] = "سوادكوه";

                $this->arrayOfCities[$i++] = "قائم شهر";

                $this->arrayOfCities[$i++] = "نكا";

                $this->arrayOfCities[$i++] = "چالوس";

                $this->arrayOfCities[$i++] = "بابلسر";

                $this->arrayOfCities[$i++] = "محمودآباد";

                $this->arrayOfCities[$i++] = "جويبار";

                $this->arrayOfCities[$i++] = "سوادکوه شمالي";

                $this->arrayOfCities[$i++] = "گلوگاه";

                $this->arrayOfCities[$i++] = "فريدونكنار";

                $this->arrayOfCities[$i++] = "عباس آباد";

                $this->arrayOfCities[$i++] = "مياندورود";

                $this->arrayOfCities[$i++] = "سيمرغ";

                $this->arrayOfCities[$i++] = "کلاردشت";

                break;
            case '28': //  مرکزی
                $this->arrayOfCities[$i++] = "اراک";

                $this->arrayOfCities[$i++] = "دليجان";

                $this->arrayOfCities[$i++] = "آشتيان";

                $this->arrayOfCities[$i++] = "تفرش";

                $this->arrayOfCities[$i++] = "خمين";

                $this->arrayOfCities[$i++] = "ساوه";

                $this->arrayOfCities[$i++] = "شازند";

                $this->arrayOfCities[$i++] = "محلات";

                $this->arrayOfCities[$i++] = "خنداب";

                $this->arrayOfCities[$i++] = "کميجان";

                $this->arrayOfCities[$i++] = "فراهان";

                $this->arrayOfCities[$i++] = "زرنديه";

                break;
            case '29': // هرمزگان
                $this->arrayOfCities[$i++] = "ابوموسي";

                $this->arrayOfCities[$i++] = "بندرعباس";

                $this->arrayOfCities[$i++] = "بندرلنگه";

                $this->arrayOfCities[$i++] = "قشم";

                $this->arrayOfCities[$i++] = "ميناب";

                $this->arrayOfCities[$i++] = "جاسك";

                $this->arrayOfCities[$i++] = "رودان";

                $this->arrayOfCities[$i++] = "حاجي اباد";

                $this->arrayOfCities[$i++] = "بستك";

                $this->arrayOfCities[$i++] = "خمير";

                $this->arrayOfCities[$i++] = "پارسيان";

                $this->arrayOfCities[$i++] = "سيريك";

                $this->arrayOfCities[$i++] = "بشاگرد";

                break;
            case '30': // همدان
                $this->arrayOfCities[$i++] = "تويسركان";

                $this->arrayOfCities[$i++] = "ملاير";

                $this->arrayOfCities[$i++] = "نهاوند";

                $this->arrayOfCities[$i++] = "همدان";

                $this->arrayOfCities[$i++] = "اسدآباد";

                $this->arrayOfCities[$i++] = "بهار";

                $this->arrayOfCities[$i++] = "كبودرآهنگ";

                $this->arrayOfCities[$i++] = "رزن";

                $this->arrayOfCities[$i++] = "فامنين";

                break;
            case '31': // یزد
                $this->arrayOfCities[$i++] = "اردكان";

                $this->arrayOfCities[$i++] = "بافق";

                $this->arrayOfCities[$i++] = "تفت";

                $this->arrayOfCities[$i++] = "مهريز";

                $this->arrayOfCities[$i++] = "يزد";

                $this->arrayOfCities[$i++] = "ميبد";

                $this->arrayOfCities[$i++] = "ابركوه";

                $this->arrayOfCities[$i++] = "اشکذر";

                $this->arrayOfCities[$i++] = "خاتم";

                $this->arrayOfCities[$i++] = "بهاباد";

                break;
        }
    }


    /************************************************************************************************************/
    //پارارمترهای جنسیت
    /************************************************************************************************************/
    private $gender;

    public function getGender()
    {
        return $this->gender;
    }

    public function setGender($gender)
    {
        switch ($gender) {
            case 1:
                $this->gender = "خانم";
                break;
            case 2:
                $this->gender = "آقا";
                break;
            default:
                $this->gender = "انتخاب نشده";
        }
    }


    /************************************************************************************************************/
    //تحصیلات
    /************************************************************************************************************/

    private $degreeOfEducation;


    public function getDegreeOfEducation()
    {
        return $this->degreeOfEducation;
    }

    public function setArrayDegreeOfEducation()
    {
        $this->degreeOfEducation[1] = "زیر دیپلم";
        $this->degreeOfEducation[2] = "دیپلم";
        $this->degreeOfEducation[3] = "فوق دیپلم";
        $this->degreeOfEducation[4] = "لیسانس";
        $this->degreeOfEducation[5] = "فوق لیسانس";
        $this->degreeOfEducation[6] = "دکتری";
        $this->degreeOfEducation[7] = "دیگر";

    }

    public function setDegreeOfEducation($degreeOfEducation)
    {
        switch ($degreeOfEducation) {
            case 1:
                $this->degreeOfEducation = "زیر دیپلم";
                break;
            case 2:
                $this->degreeOfEducation = "دیپلم";
                break;
            case 3:
                $this->degreeOfEducation = "فوق دیپلم";
                break;
            case 4:
                $this->degreeOfEducation = "لیسانس";
                break;
            case 5:
                $this->degreeOfEducation = "فوق لیسانس";
                break;
            case 6:
                $this->degreeOfEducation = "دکتری";
                break;
            case 7:
                $this->degreeOfEducation = "دیگر";
                break;
            default:
                $this->degreeOfEducation = "انتخاب نشده";
        }

    }


    /************************************************************************************************************/
    // شغل
    /************************************************************************************************************/

    private $job;

    public function getJob()
    {
        return $this->job;
    }

    public function setArrayOfJob()
    {

        $this->job[1] = "آزاد";
        $this->job[2] = "کارمند";
        $this->job[3] = "هنرمند";
        $this->job[4] = "خانه دار";
        $this->job[5] = "دانش آموز";
        $this->job[6] = "دانشجو";
        $this->job[7] = "دیگر";
    }

    public function setJob($job)
    {
        switch ($job) {
            case 1:
                $this->job = "آزاد";
                break;
            case 2:
                $this->job = "کارمند";
                break;
            case 3:
                $this->job = "هنرمند";
                break;
            case 4:
                $this->job = "خانه دار";
                break;
            case 5:
                $this->job = "دانش آموز";
                break;
            case 6:
                $this->job = "دانشجو";
                break;
            case 7:
                $this->job = "دیگر";
                break;
            default:
                $this->job = "انتخاب نشده";
        }

    }

    /************************************************************************************************************/
    // وضعیت تاهل
    /************************************************************************************************************/

    private $maritalStatus;

    public function getMaritalStatus()
    {
        return $this->maritalStatus;
    }

    public function setMaritalStatus($maritalStatus)
    {
        switch ($maritalStatus) {
            case 1:
                $this->maritalStatus = "مجرد";
                break;
            case 2:
                $this->maritalStatus = "متاهل";
                break;
            default:
                $this->maritalStatus = "انتخاب نشده";
        }

    }



    /************************************************************************************************************/
    // رنگ مورد علاقه
    /************************************************************************************************************/

    private $popularColor;

    public function getPopularColor()
    {
        return $this->popularColor;
    }

    public function setArrayOfPopularColor()
    {

        $this->popularColor[1] = "سفید";
        $this->popularColor[2] = "آبی";
        $this->popularColor[3] = "سبز";
        $this->popularColor[4] = "زرد";
        $this->popularColor[5] = "قرمز";
        $this->popularColor[6] = "نارنجی";
        $this->popularColor[7] = "بنفش";
        $this->popularColor[8] = "قهوه ای";
        $this->popularColor[9] = "خاکستری";
        $this->popularColor[10] = "سیاه";


    }

    public function setPopularColor($popularColor)
    {
        switch ($popularColor) {
            case 1:
                $this->popularColor = "سفید";
                break;
            case 2:
                $this->popularColor = "آبی";
                break;
            case 3:
                $this->popularColor = "سبز";
                break;
            case 4:
                $this->popularColor = "زرد";
                break;
            case 5:
                $this->popularColor = "قرمز";
                break;
            case 6:
                $this->popularColor = "نارنجی";
                break;
            case 7:
                $this->popularColor = "بنفش";
                break;
            case 8:
                $this->popularColor = "قهوه ای";
                break;
            case 9:
                $this->popularColor = "خاکستری";
                break;
            case 10:
                $this->popularColor = "سیاه";
                break;
            default:
                $this->popularColor = "انتخاب نشده";
        }
    }


    /***********************************************************************************************************
     * ********************************************************************************************************
     *********************************************************************************************************/
    private $startReagentCode = 101837;

    public function createReagentCode($user_id)
    {
        return ($user_id + $this->startReagentCode);
    }

    public function getUserIdFromReagentCode($reagentCode)
    {
        return ($reagentCode - $this->startReagentCode);
    }


    /***********************************************************************************************************
     * ********************************************************************************************************
     *********************************************************************************************************/
//
//    public function getfleetsList()
//    {
//        return [
//            1=>[
//                11=>'',
//                12=>'',
//                13=>'',
//                14=>'',
//                15=>'',
//                16=>''
//            ],
//            2=>[
//                21=>[],
//                22=>[]
//            ],
//            3=>[
//
//            ],
//        ];
//    }


    public static function convertNumbers($srting)
    {
        $en_num = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
        $fa_num = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');
        return str_replace($fa_num, $en_num, $srting);
    }
}
