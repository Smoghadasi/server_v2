<?php

namespace App\Http\Controllers;

use App\Jobs\SendNotificationJob;
use App\Models\Bearing;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Payment;
use App\Models\PurchasedFleetControlPackage;
use App\Models\SiteOption;
use App\Models\Transaction;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use SoapClient;
use SoapFault;
use Illuminate\Http\Request;


class PayController extends Controller
{

    // ارسال نوتیفیکیشن
    public function sendNotification($FCM_token, $data, $API_ACCESS_KEY)
    {
        $url = 'https://fcm.googleapis.com/fcm/send';
        $notification = [
            'body' => $data['body'],
            'sound' => true,
        ];
        $fields = array(
            'to' => $FCM_token,
            'notification' => $notification,
            'data' => $data
        );
        $headers = array(
            'Authorization: key=' . $API_ACCESS_KEY,
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        curl_close($ch);
    }

    // پرداخت
    public function pay($amount, $user_id, $userType)
    {
        $CallbackURL = 'http://dashboard.iran-tarabar.com/verify'; // Required

        $client = new SoapClient('https://www.zarinpal.com/pg/services/WebGate/wsdl', ['encoding' => 'UTF-8']);
        $result = $client->PaymentRequest(
            [
                'MerchantID' => MERCHANT_ID,
                'Amount' => $amount,
                'Description' => "ایران ترابر",
                'Email' => "",
                'Mobile' => "",
                'CallbackURL' => $CallbackURL,
            ]
        );
        if ($result->Status == 100) {

            try {

                $transaction = new Transaction();
                $transaction->user_id = $user_id;
                $transaction->userType = $userType;
                $transaction->authority = $result->Authority;
                $transaction->amount = $amount;
                $transaction->save();

                if (isset($transaction->id))
                    return redirect('https://www.zarinpal.com/pg/StartPay/' . $result->Authority);
            } catch (\Exception $exception) {
            }
        }
    }

    public function verify()
    {
        $Authority = $_GET['Authority'];

        $transaction = Transaction::where('authority', $Authority)->first();

        if (isset($transaction->id)) {

            if ($_GET['Status'] == 'OK') {

                $client = new SoapClient('https://www.zarinpal.com/pg/services/WebGate/wsdl', ['encoding' => 'UTF-8']);

                $result = $client->PaymentVerification(
                    [
                        'MerchantID' => MERCHANT_ID,
                        'Authority' => $Authority,
                        'Amount' => $transaction->amount,
                    ]
                );

                try {

                    DB::beginTransaction();

                    if ($result->Status == 100) {
                        $transaction->status = $result->Status;
                        $transaction->RefId = $result->RefID;
                        $transaction->save();

                        switch ($transaction->userType) {
                            case ROLE_TRANSPORTATION_COMPANY:
                                $transportationCompany = Bearing::find($transaction->user_id);
                                $transportationCompany->wallet += $transaction->amount;
                                $transportationCompany->save();
                                break;
                        }
                    } else {
                        $transaction->status = $result->Status;
                        $transaction->save();
                    }

                    DB::commit();

                    $status = $result->Status;
                    $message = $this->getStatusMessage($status);

                    return view('users.PayStatus', compact('message', 'status'));
                } catch (\Exception $exception) {
                    DB::rollBack();
                }
            }
        }
        $status = 0;
        $message = $this->getStatusMessage($status);
        return view('users.PayStatus', compact('message', 'status'));
    }

    private function getStatusMessage($status): string
    {
        switch ($status) {
            case "0":
                return "تراكنش نا موفق ميباشد.";
            case "1":
                return "تراکنش موفق.";
            case "2":
                return "تراکنش موفق";
            case "3":
                return "تراكنش نا موفق ميباشد";
            case "-1":
                return "اطلاعات ارسال شده ناقص است.";
            case "-2":
                return "IP و يا مرچنت كد پذيرنده صحيح نيست";
            case "-3":
                return "با توجه به محدوديت هاي شاپرك امكان پرداخت با رقم درخواست شده ميسر نمي باشد";
            case "-4":
                return "سطح تاييد پذيرنده پايين تر از سطح نقره اي است.";
            case "-11":
                return "درخواست مورد نظر يافت نشد.";
            case "-12":
                return "امكان ويرايش درخواست ميسر نمي باشد.";
            case "-21":
                return "هيچ نوع عمليات مالي براي اين تراكنش يافت نشد";
            case "-22":
                return "تراكنش نا موفق ميباشد";
            case "-1531":
                return "تراكنش ناموفق ميباشد";
            case "-33":
                return "رقم تراكنش با رقم پرداخت شده مطابقت ندارد";
            case "-34":
                return "سقف تقسيم تراكنش از لحاظ تعداد يا رقم عبور نموده است";
            case "-40":
                return "اجازه دسترسي به متد مربوطه وجود ندارد.";
            case "-41":
                return "اطلاعات ارسال شده مربوط به AdditionalData غيرمعتبر ميباشد.";
            case "-42":
                return "مدت زمان معتبر طول عمر شناسه پرداخت بايد بين 30 دقيه تا 45 روز مي باشد.";
            case "-54":
                return "درخواست مورد نظر آرشيو شده است";
            case "100":
                return "تراکنش موفق.";
            case "101":
                return "عمليات پرداخت موفق بوده و قبلا PaymentVerification تراكنش انجام شده است.";
            default:
                return "خطای نامشخص";
        }
    }


    // پرداخت
    public function payMonthlyCharge($user_id, $userType)
    {

        if ($userType == ROLE_TRANSPORTATION_COMPANY)
            $amount = TRANSPORTATION_COMPANY_MONTHLY_SERVICE_AMOUNT;

        $CallbackURL = 'http://dashboard.iran-tarabar.com/verifyMonthlyCharge'; // Required

        $client = new SoapClient('https://www.zarinpal.com/pg/services/WebGate/wsdl', ['encoding' => 'UTF-8']);
        $result = $client->PaymentRequest(
            [
                'MerchantID' => MERCHANT_ID,
                'Amount' => $amount,
                'Description' => "ایران ترابر",
                'Email' => "",
                'Mobile' => "",
                'CallbackURL' => $CallbackURL,
            ]
        );
        if ($result->Status == 100) {

            try {

                $transaction = new Transaction();
                $transaction->user_id = $user_id;
                $transaction->userType = $userType;
                $transaction->authority = $result->Authority;
                $transaction->amount = $amount;
                $transaction->save();

                if (isset($transaction->id))
                    return redirect('https://www.zarinpal.com/pg/StartPay/' . $result->Authority);
            } catch (\Exception $exception) {
            }
        }
    }

    public function verifyMonthlyCharge()
    {

        $Authority = $_GET['Authority'];

        $transaction = Transaction::where('authority', $Authority)->first();

        if (isset($transaction->id)) {

            if ($_GET['Status'] == 'OK') {

                $client = new SoapClient('https://www.zarinpal.com/pg/services/WebGate/wsdl', ['encoding' => 'UTF-8']);

                $result = $client->PaymentVerification(
                    [
                        'MerchantID' => MERCHANT_ID,
                        'Authority' => $Authority,
                        'Amount' => $transaction->amount,
                    ]
                );

                try {

                    DB::beginTransaction();

                    if ($result->Status == 100) {
                        $transaction->status = $result->Status;
                        $transaction->RefId = $result->RefID;
                        $transaction->save();

                        switch ($transaction->userType) {
                            case ROLE_TRANSPORTATION_COMPANY:

                                $transportationCompany = Bearing::find($transaction->user_id);
                                $transportationCompany->validityDate = date("Y-m-d H:i:s", time() + ONE_MONTH_TIME);
                                $transportationCompany->countOfLoadsAfterValidityDate = NUM_OF_FREE_LOADS;
                                $transportationCompany->save();

                                $data = [
                                    'title' => '',
                                    'body' => '',
                                    'notificationType' => NOTIFICATION_PAY_MONTHLY_CHARGE_SUCCESS,
                                ];
                                $this->sendNotification($transportationCompany->FCM_token, $data, API_ACCESS_KEY_TRANSPORTATION_COMPANY);

                                break;
                        }
                    } else {
                        $transaction->status = $result->Status;
                        $transaction->save();
                    }

                    DB::commit();

                    $status = $result->Status;
                    $message = $this->getStatusMessage($status);

                    return view('users.PayStatus', compact('message', 'status'));
                } catch (\Exception $exception) {
                    DB::rollBack();

                    Log::emergency($exception->getMessage());
                }
            }
        }
        $status = 0;
        $message = $this->getStatusMessage($status);
        return view('users.PayStatus', compact('message', 'status'));
    }


    /*****************************************************************************************/
    /*****************************************************************************************/
    /*****************************************************************************************/

    public function bpPayRequest($amount, $user_id, $userType, $callBackUrl = 'callBackUrl')
    {
        $amount = $amount * 10;
        ini_set("soap.wsdl_cache_enabled", "0");

        try {

            $client = new SoapClient('https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl');
            $namespace = 'http://interfaces.core.sw.bps.com/';

            $orderId = (Transaction::count() + 1000);

            $transaction = new Transaction();
            $transaction->user_id = $user_id;
            $transaction->userType = $userType;
            $transaction->orderId = $orderId;
            $transaction->amount = $amount;
            $transaction->save();

            $parameters['terminalId'] = $this->terminal;
            $parameters['userName'] = $this->username;
            $parameters['userPassword'] = $this->password;
            $parameters['orderId'] = $orderId;
            $parameters['amount'] = $amount;
            $parameters['localDate'] = '20091008';
            $parameters['localTime'] = '102003';
            $parameters['additionalData'] = 'شارژ کیف پول';
            $parameters['callBackUrl'] = url($callBackUrl);
            $parameters['payerId'] = 0;
            $result = $client->bpPayRequest($parameters, $namespace);

            $res = @explode(',', $result->return);

            if ($res[0] == 0) {

                $RefId = $res[1];

                Transaction::where('id', $transaction->id)
                    ->update([
                        'RefId' => $RefId
                    ]);

                return view('chargeWallet', compact('RefId'));
            } else {
                echo 'خطا : ' . $res[0];
            }
        } catch (SoapFault $ex) {
            return $ex->faultstring;
        }
    }


    public function bpVerifyRequest(Request $request)
    {
        $RefId = $request->RefId;
        $ResCode = $request->ResCode;
        $saleOrderId = $request->SaleOrderId;
        $saleReferenceId = $request->SaleReferenceId;
        $CardHolderInfo = $request->CardHolderInfo;
        $CardHolderPan = $request->CardHolderPan;

        if (Transaction::where([['OrderId', $saleOrderId], ['status', 0]])->count() == 0) {
            return 'خطا';
        }

        if ($ResCode > 0)
            return 'خطا';


        Transaction::where('OrderId', $saleOrderId)
            ->update([
                'RefId' => $RefId,
                'ResCode' => $ResCode,
                'SaleReferenceId' => $saleReferenceId
            ]);


        $transaction = Transaction::where('OrderId', $saleOrderId)
            ->first();
        $amount = $transaction->amount;

        switch ($transaction->userType) {
            case 'bearing':
                $bearing = Bearing::where('id', $transaction->user_id)
                    ->select('FCM_token', 'wallet')
                    ->first();

                Bearing::where('id', $transaction->user_id)
                    ->update(['wallet' => ($bearing->wallet + ($amount / 10))]);

                $data = [
                    'title' => '',
                    'body' => '',
                    'notificationType' => 'paySuccess',
                ];
                $this->sendNotification($bearing->FCM_token, $data, API_ACCESS_KEY_TRANSPORTATION_COMPANY);
                break;
            case 'driver':
                $driver = Driver::where('id', $transaction->user_id)
                    ->select('FCM_token', 'wallet')
                    ->first();

                Driver::where('id', $transaction->user_id)
                    ->update([
                        'wallet' => ($driver->wallet + ($amount / 10)),
                        'activationDate' => Carbon::now()->toDateTimeString(),
                        'status' => 1
                    ]);


                $data = [
                    'title' => 'ایرانترابر',
                    'body' => 'هزینه ماهانه پرداخت شد',
                    'notificationType' => 'paySuccess',
                ];
                $this->sendNotification($driver->FCM_token, $data, API_ACCESS_KEY_DRIVER);
                break;
            case 'driver_gibar':
                return redirect('http://gibar.ir/chaneDriverStatus/' . $transaction->user_id);
                break;
        }


        //        ini_set("soap.wsdl_cache_enabled", "0");
        //
        //        try {
        //
        //            $paymentdone = '0';
        //
        //            $client = new SoapClient('https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl');
        //            $namespace = 'http://interfaces.core.sw.bps.com/';
        //
        //            $parameters['terminalId'] = $this->terminal;
        //            $parameters['userName'] = $this->username;
        //            $parameters['userPassword'] = $this->password;
        //            $parameters['orderId'] = $saleOrderId;
        //            $parameters['saleOrderId'] = $saleOrderId;
        //            $parameters['saleReferenceId'] = $saleReferenceId;
        //
        //            $result = $client->bpVerifyRequests($parameters, $namespace);
        //
        //            $res = @explode(',', $result->return);
        //
        //            if ($res[0] == 0) {
        //
        //                $this->bpSettleRequest($saleOrderId, $saleOrderId, $saleReferenceId);
        //
        //            } else {
        //                echo 'خطا : ' . $result;
        //            }
        //
        //        } catch (SoapFault $ex) {
        //            return $ex->faultstring;
        //        }


    }


    public function bpSettleRequest($orderId, $saleOrderId, $saleReferenceId)
    {
        ini_set("soap.wsdl_cache_enabled", "0");

        try {

            $paymentdone = '0';

            $client = new SoapClient('https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl');
            $namespace = 'http://interfaces.core.sw.bps.com/';

            $parameters['terminalId'] = $this->terminal;
            $parameters['userName'] = $this->username;
            $parameters['userPassword'] = $this->password;
            $parameters['orderId'] = $orderId;
            $parameters['saleOrderId'] = $saleOrderId;
            $parameters['saleReferenceId'] = $saleReferenceId;

            $result = $client->bpSettleRequest($parameters, $namespace);

            $res = @explode(',', $result->return);

            if ($res[0] == 0) {

                return 'ok';
            } else {
                echo 'خطا : ' . $result;
            }
        } catch (SoapFault $ex) {
            return $ex->faultstring;
        }
    }

    //
    public function chargeWallet(Request $request)
    {

        if (\auth('bearing')->check()) {
            $amount = $request->amount;
            $bearing_id = auth('bearing')->id();
            return $this->bpPayRequest($amount, $bearing_id, 'bearing', 'payCallBackWeb');
        } else
            return response()->view('errors/404');
    }

    public function payCallBackWeb(Request $request)
    {
        $RefId = $request->RefId;
        $ResCode = $request->ResCode;
        $saleOrderId = $request->SaleOrderId;
        $saleReferenceId = $request->SaleReferenceId;
        $CardHolderInfo = $request->CardHolderInfo;
        $CardHolderPan = $request->CardHolderPan;

        $alert = '';
        $message = [];

        if ($ResCode > 0) {
            $alert = 'alert-danger';
            $message[0] = 'پرداخت انجام نشد';
        } else if (Transaction::where([['OrderId', $saleOrderId], ['status', 0]])->count() == 0) {
            $alert = 'alert-danger';
            $message[0] = 'درخواست نا معتبر است';
        } else {


            Transaction::where('OrderId', $saleOrderId)
                ->update([
                    'RefId' => $RefId,
                    'ResCode' => $ResCode,
                    'SaleReferenceId' => $saleReferenceId,
                    'status' => 1
                ]);

            $transaction = Transaction::where('OrderId', $saleOrderId)
                ->first();
            $amount = $transaction->amount;

            $bearing = Bearing::where('id', $transaction->user_id)
                ->select('FCM_token', 'wallet')
                ->first();
            $amount /= 10;
            Bearing::where('id', $transaction->user_id)
                ->update(['wallet' => ($bearing->wallet + $amount)]);

            $alert = 'alert-success';
            $message[0] = 'پرداخت موفقیت انجام شد';
            $message[1] = 'موجودی کیف پول شما ' . ($bearing->wallet + $amount) . ' تومان';
        }

        return view('users.walletStatus', compact('alert', 'message'));
    }

    public function expenseForDriver()
    {
        return 10000;
    }

    /*****************************************************************************************/
    /*****************************************************************************************/
    /*****************************************************************************************/

    public function payDriver($packageName, Driver $driver)
    {

        $driverPackagesInfo = getDriverPackagesInfo();
        if (!isset($driverPackagesInfo['data'][$packageName]['price']))
            return abort(404);

        $monthsOfThePackage = 0;
        switch ($packageName) {
            case 'monthly':
                $monthsOfThePackage = 1;
                break;
            case 'trimester':
                $monthsOfThePackage = 3;
                break;
            case 'sixMonths':
                $monthsOfThePackage = 6;
                break;
        }


        $amount = $driverPackagesInfo['data'][$packageName]['price'];

        $CallbackURL = 'http://dashboard.iran-tarabar.ir/verifyDriverPay';

        $client = new SoapClient('https://www.zarinpal.com/pg/services/WebGate/wsdl', ['encoding' => 'UTF-8']);
        $result = $client->PaymentRequest(
            [
                'MerchantID' => MERCHANT_ID,
                'Amount' => $amount,
                'Description' => "ایران ترابر",
                'Email' => "",
                'Mobile' => "",
                'CallbackURL' => $CallbackURL,
            ]
        );
        if ($result->Status == 100) {

            try {

                $transaction = new Transaction();
                $transaction->user_id = $driver->id;
                $transaction->userType = ROLE_DRIVER;
                $transaction->authority = $result->Authority;
                $transaction->bank_name = ZARINPAL;
                $transaction->status = 2;
                $transaction->amount = $amount;
                $transaction->monthsOfThePackage = $monthsOfThePackage;
                $transaction->save();

                try {
                    $driver = Driver::find($transaction->user_id);

                    if (
                        Transaction::where('user_id', $driver->id)
                        ->where('userType', 'driver')
                        ->where('created_at', '>', date('Y-m-d', time()) . ' 00:00:00')
                        ->count() == 5
                    ) {
                        $sms = new Driver();
                        $sms->unSuccessPayment($driver->mobileNumber);
                    }
                } catch (Exception $exception) {
                    Log::emergency("-------------------------------- unSuccessPayment -----------------------------");
                    Log::emergency($exception->getMessage());
                    Log::emergency("------------------------------------------------------------------------------");
                }

                if (isset($transaction->id))
                    return redirect('https://www.zarinpal.com/pg/StartPay/' . $result->Authority);
            } catch (\Exception $exception) {
            }
        }
    }

    public function payDriverZibal($packageName, Driver $driver)
    {

        $driverPackagesInfo = getDriverPackagesInfo();
        if (!isset($driverPackagesInfo['data'][$packageName]['price']))
            return abort(404);

        $monthsOfThePackage = 0;
        switch ($packageName) {
            case 'monthly':
                $monthsOfThePackage = 1;
                break;
            case 'trimester':
                $monthsOfThePackage = 3;
                break;
            case 'sixMonths':
                $monthsOfThePackage = 6;
                break;
        }

        switch ($driverPackagesInfo['data'][$packageName]['price']) {
            case MONTHLY:
                $amount = '2500000';
                break;
            case TRIMESTER:
                $amount = '6500000';
                break;
            case SIXMONTHS:
                $amount = '12500000';
                break;
        }
        $amountOrginal = $driverPackagesInfo['data'][$packageName]['price'];

        $CallbackURL = 'http://dashboard.iran-tarabar.ir/verifyDriverPayZibal';

        $parameters = array(
            "merchant" => MERCHANT_ID_ZIBAL, //required
            "callbackUrl" => $CallbackURL, //required
            "amount" => $amount, //required
            "orderId" => time(), //optional
            "mobile" => "09184696188", //optional for mpg
        );
        $payment = new Payment();
        $response = $payment->postToZibal('request', $parameters);
        var_dump($response);
        if ($response->result == 100) {
            try {

                $transaction = new Transaction();
                $transaction->user_id = $driver->id;
                $transaction->userType = ROLE_DRIVER;
                $transaction->authority = $response->trackId;
                $transaction->bank_name = ZIBAL;
                $transaction->status = 2;
                $transaction->amount = $amountOrginal;
                $transaction->monthsOfThePackage = $monthsOfThePackage;
                $transaction->save();

                try {
                    $driver = Driver::find($transaction->user_id);

                    if (
                        Transaction::where('user_id', $driver->id)
                        ->where('userType', 'driver')
                        ->where('created_at', '>', date('Y-m-d', time()) . ' 00:00:00')
                        ->count() == 5
                    ) {
                        $sms = new Driver();
                        $sms->unSuccessPayment($driver->mobileNumber);
                    }
                } catch (Exception $exception) {
                    Log::emergency("-------------------------------- unSuccessPayment -----------------------------");
                    Log::emergency($exception->getMessage());
                    Log::emergency("------------------------------------------------------------------------------");
                }

                if (isset($transaction->id)) {
                    $startGateWayUrl = "https://gateway.zibal.ir/start/" . $response->trackId;
                    return redirect($startGateWayUrl);
                }
            } catch (\Exception $exception) {
            }
        }
    }

    // تابع ایجاد شناسه یکتا برای هر سفارش

    public function generateOrderId()
    {
        $orderId = date('YmdHis');
        if ($this->checkOrder($orderId)) {
            return $this->generateOrderId();
        }
        return (string)$orderId;
    }

    public function checkOrder($number)
    {
        return Transaction::where('ResCode', $number)->exists();
    }

    public function payDriverSina($packageName, Driver $driver)
    {

        $driverPackagesInfo = getDriverPackagesInfo();
        if (!isset($driverPackagesInfo['data'][$packageName]['price'])) {
            return abort(404);
        }

        $monthsOfThePackage = match ($packageName) {
            'monthly' => 1,
            'trimester' => 3,
            'sixMonths' => 6,
            default => 0
        };

        $price = $driverPackagesInfo['data'][$packageName]['price'];

        $amount = match ($price) {
            MONTHLY => '2500000',
            TRIMESTER => '6500000',
            SIXMONTHS => '12500000',
            default => '0'
        };

        $amountOrginal = $price;

        $url = "https://pec.shaparak.ir/NewIPGServices/Sale/SaleService.asmx?WSDL";
        $callbackUrl = 'https://dashboard.iran-tarabar.ir/verifyDriverPaySina';

        $orderId = $driver->id . date('mHis') . substr(Carbon::now()->micro, 0, 2) . rand(100, 999);

        $params = [
            "LoginAccount" => PIN_SINA,
            "Amount" => $amount,
            "OrderId" => $orderId,
            "CallBackUrl" => $callbackUrl,
            "AdditionalData" => '',
            "Originator" => ''
        ];

        try {
            $client = new SoapClient($url);
            $result = $client->SalePaymentRequest(['requestData' => $params]);

            if ($result->SalePaymentRequestResult->Token && $result->SalePaymentRequestResult->Status === 0) {
                $token = $result->SalePaymentRequestResult->Token;

                $transaction = new Transaction();
                $transaction->user_id = $driver->id;
                $transaction->userType = ROLE_DRIVER;
                $transaction->authority = $token;
                $transaction->status = 2;
                $transaction->bank_name = SINA;
                $transaction->amount = $amountOrginal;
                $transaction->monthsOfThePackage = $monthsOfThePackage;
                $transaction->save();

                // بررسی دفعات پرداخت در روز
                try {
                    if (
                        Transaction::where('user_id', $driver->id)
                        ->where('userType', 'driver')
                        ->where('created_at', '>', now()->startOfDay())
                        ->count() >= 5
                    ) {
                        $driver->unSuccessPayment($driver->mobileNumber);
                    }
                } catch (Exception $e) {
                    Log::emergency("unSuccessPayment failed: " . $e->getMessage());
                }

                return redirect("https://pec.shaparak.ir/NewIPG/?Token=$token");
            } else {
                $err_msg = "(<strong>کد خطا: " . $result->SalePaymentRequestResult->Status . "</strong>) " .
                    $result->SalePaymentRequestResult->Message;
                return $err_msg;
            }
        } catch (Exception $ex) {
            Log::error("Soap Error in payDriverSina: " . $ex->getMessage());
            return "خطا در ارتباط با درگاه بانک. لطفا دوباره تلاش کنید.";
        }
    }

    public function payDriverSinaTest($packageName, Driver $driver)
    {

        $driverPackagesInfo = getDriverPackagesInfo();
        if (!isset($driverPackagesInfo['data'][$packageName]['price'])) {
            return abort(404);
        }

        $monthsOfThePackage = match ($packageName) {
            'monthly' => 1,
            'trimester' => 3,
            'sixMonths' => 6,
            default => 0
        };

        $price = $driverPackagesInfo['data'][$packageName]['price'];

        $amount = match ($price) {
            MONTHLY => '2500000',
            TRIMESTER => '6500000',
            SIXMONTHS => '12500000',
            default => '0'
        };

        $amountOrginal = $price;

        $url = "https://pec.shaparak.ir/NewIPGServices/Sale/SaleService.asmx?WSDL";
        $callbackUrl = 'https://dashboard.iran-tarabar.ir/verifyDriverPaySina';

        $orderId = $driver->id . date('mHis') . substr(Carbon::now()->micro, 0, 2) . rand(100, 999);

        $params = [
            "LoginAccount" => PIN_SINA2,
            "Amount" => $amount,
            "OrderId" => $orderId,
            "CallBackUrl" => $callbackUrl,
            "AdditionalData" => '',
            "Originator" => ''
        ];

        try {
            $client = new SoapClient($url);
            $result = $client->SalePaymentRequest(['requestData' => $params]);

            if ($result->SalePaymentRequestResult->Token && $result->SalePaymentRequestResult->Status === 0) {
                $token = $result->SalePaymentRequestResult->Token;

                $transaction = new Transaction();
                $transaction->user_id = $driver->id;
                $transaction->userType = ROLE_DRIVER;
                $transaction->authority = $token;
                $transaction->status = 2;
                $transaction->bank_name = SINA;
                $transaction->amount = $amountOrginal;
                $transaction->monthsOfThePackage = $monthsOfThePackage;
                $transaction->save();

                // بررسی دفعات پرداخت در روز
                try {
                    if (
                        Transaction::where('user_id', $driver->id)
                        ->where('userType', 'driver')
                        ->where('created_at', '>', now()->startOfDay())
                        ->count() >= 5
                    ) {
                        $driver->unSuccessPayment($driver->mobileNumber);
                    }
                } catch (Exception $e) {
                    Log::emergency("unSuccessPayment failed: " . $e->getMessage());
                }

                return redirect("https://pec.shaparak.ir/NewIPG/?Token=$token");
            } else {
                $err_msg = "(<strong>کد خطا: " . $result->SalePaymentRequestResult->Status . "</strong>) " .
                    $result->SalePaymentRequestResult->Message;
                return $err_msg;
            }
        } catch (Exception $ex) {
            Log::error("Soap Error in payDriverSina: " . $ex->getMessage());
            return "خطا در ارتباط با درگاه بانک. لطفا دوباره تلاش کنید.";
        }
    }

    public function verifyDriverPaySinaTest(Request $request)
    {
        $confirmUrl = 'https://pec.shaparak.ir/NewIPGServices/Confirm/ConfirmService.asmx?WSDL';

        $params = [
            "LoginAccount" => PIN_SINA2,
            "Token" => $request->Token
        ];

        $transaction = Transaction::where('authority', $request->Token)->first();

        if (!$transaction) {
            return view('users.driverPayStatus', [
                'message' => 'تراکنش یافت نشد',
                'status' => 0
            ]);
        }

        // ✅ جلوگیری از اجرای مجدد در صورت تأیید شدن تراکنش
        if ($transaction->status == 100) {
            return view('users.driverPayStatus', [
                'message' => $this->getStatusMessage(100),
                'status' => 100,
                'authority' => $transaction->authority
            ]);
        }

        try {
            $client = new SoapClient($confirmUrl);
            $result = $client->ConfirmPayment(['requestData' => $params]);

            if ($result->ConfirmPaymentResult->Status != '0') {
                $transaction->status = 0;
                $transaction->save();

                return view('users.driverPayStatus', [
                    'message' => $this->getStatusMessage(0),
                    'status' => 0
                ]);
            }

            // ✅ تایید موفق (و فقط برای بار اول اجرا می‌شود)
            DB::beginTransaction();

            $transaction->status = 100;
            $transaction->RefId = $result->ConfirmPaymentResult->RRN;
            $transaction->save();

            $driver = Driver::find($transaction->user_id);

            $daysToAdd = 30 * $transaction->monthsOfThePackage;

            if (!$driver->activeDate || Carbon::parse($driver->activeDate)->lt(Carbon::now())) {
                $driver->activeDate = Carbon::now()->addDays($daysToAdd);
            } else {
                $driver->activeDate = Carbon::parse($driver->activeDate)->addDays($daysToAdd);
            }

            if ($driver->freeCalls > 3) {
                $driver->freeCalls = 3;
            }
            $driver->save();

            DB::commit();

            try {
                if (!empty($driver->FCM_token) && $driver->version > 68) {
                    $today = date('Y/m/d');
                    $persianDate = gregorianDateToPersian($today, '/');

                    // نگاشت ماه‌ها به تعداد روز
                    $packageMonths = [
                        '1' => '+30 day',
                        '3' => '+90 day',
                        '6' => '+180 day',
                    ];

                    // محاسبه تاریخ انقضا بر اساس پکیج
                    $expireDate = '';
                    if (!empty($packageMonths[$transaction->monthsOfThePackage])) {
                        $expireDate = gregorianDateToPersian(
                            date('Y/m/d', strtotime($packageMonths[$transaction->monthsOfThePackage])),
                            '/'
                        );
                    }
                    // پیام
                    $title = 'راننده عزیز، 🎉';
                    $body  = "خرید شما در تاریخ {$persianDate} با موفقیت انجام شد.\nتاریخ پایان اعتبار: {$expireDate} 📞";

                    $this->sendNotificationWeb($driver->FCM_token, $title, $body);
                }
            } catch (\Exception $e) {
                Log::warning('نوتیف مربوط به افزایش اعتبار راننده');
                Log::warning($e->getMessage());
            }

            return view('users.driverPayStatus', [
                'message' => $this->getStatusMessage(100),
                'status' => 100,
                'authority' => $transaction->authority
            ]);
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error("verifyDriverPaySina error: " . $ex->getMessage());

            return view('users.driverPayStatus', [
                'message' => 'خطا در تایید پرداخت. لطفا با پشتیبانی تماس بگیرید.',
                'status' => 0
            ]);
        }
    }


    public function verifyDriverPaySina(Request $request)
    {
        $confirmUrl = 'https://pec.shaparak.ir/NewIPGServices/Confirm/ConfirmService.asmx?WSDL';

        $params = [
            "LoginAccount" => PIN_SINA,
            "Token" => $request->Token
        ];

        $transaction = Transaction::where('authority', $request->Token)->first();

        if (!$transaction) {
            return view('users.driverPayStatus', [
                'message' => 'تراکنش یافت نشد',
                'status' => 0
            ]);
        }

        // ✅ جلوگیری از اجرای مجدد در صورت تأیید شدن تراکنش
        if ($transaction->status == 100) {
            return view('users.driverPayStatus', [
                'message' => $this->getStatusMessage(100),
                'status' => 100,
                'authority' => $transaction->authority
            ]);
        }

        try {
            $client = new SoapClient($confirmUrl);
            $result = $client->ConfirmPayment(['requestData' => $params]);

            if ($result->ConfirmPaymentResult->Status != '0') {
                $transaction->status = 0;
                $transaction->save();

                return view('users.driverPayStatus', [
                    'message' => $this->getStatusMessage(0),
                    'status' => 0
                ]);
            }

            // ✅ تایید موفق (و فقط برای بار اول اجرا می‌شود)
            DB::beginTransaction();

            $transaction->status = 100;
            $transaction->RefId = $result->ConfirmPaymentResult->RRN;
            $transaction->save();

            $driver = Driver::find($transaction->user_id);

            $daysToAdd = 30 * $transaction->monthsOfThePackage;

            if (!$driver->activeDate || Carbon::parse($driver->activeDate)->lt(Carbon::now())) {
                $driver->activeDate = Carbon::now()->addDays($daysToAdd);
            } else {
                $driver->activeDate = Carbon::parse($driver->activeDate)->addDays($daysToAdd);
            }

            if ($driver->freeCalls > 3) {
                $driver->freeCalls = 3;
            }
            $driver->save();

            DB::commit();

            try {
                if (!empty($driver->FCM_token) && $driver->version > 68) {
                    $today = date('Y/m/d');
                    $persianDate = gregorianDateToPersian($today, '/');

                    // نگاشت ماه‌ها به تعداد روز
                    $packageMonths = [
                        '1' => '+30 day',
                        '3' => '+90 day',
                        '6' => '+180 day',
                    ];

                    // محاسبه تاریخ انقضا بر اساس پکیج
                    $expireDate = '';
                    if (!empty($packageMonths[$transaction->monthsOfThePackage])) {
                        $expireDate = gregorianDateToPersian(
                            date('Y/m/d', strtotime($packageMonths[$transaction->monthsOfThePackage])),
                            '/'
                        );
                    }
                    // پیام
                    $title = 'راننده عزیز، 🎉';
                    $body  = "خرید شما در تاریخ {$persianDate} با موفقیت انجام شد.\nتاریخ پایان اعتبار: {$expireDate} 📞";

                    $this->sendNotificationWeb($driver->FCM_token, $title, $body);
                }
            } catch (\Exception $e) {
                Log::warning('نوتیف مربوط به افزایش اعتبار راننده');
                Log::warning($e->getMessage());
            }

            return view('users.driverPayStatus', [
                'message' => $this->getStatusMessage(100),
                'status' => 100,
                'authority' => $transaction->authority
            ]);
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error("verifyDriverPaySina error: " . $ex->getMessage());

            return view('users.driverPayStatus', [
                'message' => 'خطا در تایید پرداخت. لطفا با پشتیبانی تماس بگیرید.',
                'status' => 0
            ]);
        }
    }


    private function sendNotificationWeb($FCM_token, $title, $body, $loadId = '/')
    {
        $serviceAccountPath = base_path('public/assets/zarin-tarabar-firebase-adminsdk-9x6c3-7dbc939cac.json');
        $serviceAccountJson = file_get_contents($serviceAccountPath);
        $serviceAccount = json_decode($serviceAccountJson, true);

        $clientEmail = $serviceAccount['client_email'];
        $privateKey = $serviceAccount['private_key'];
        $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
        $now = time();
        $expiration = $now + 3600;
        $payload = json_encode([
            'iss' => $clientEmail,
            'scope' => 'https://www.googleapis.com/auth/cloud-platform https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $expiration,
            'iat' => $now
        ]);

        // Encode to base64
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        // Create the signature
        $signatureInput = $base64UrlHeader . "." . $base64UrlPayload;
        openssl_sign($signatureInput, $signature, $privateKey, 'sha256');
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        // Create the JWT
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
        // Exchange JWT for an access token
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]));
        $response = curl_exec($ch);
        curl_close($ch);
        $responseData = json_decode($response, true);
        $accessToken = $responseData['access_token'];

        $url = 'https://fcm.googleapis.com/v1/projects/zarin-tarabar/messages:send';
        $notification = [
            "message" => [
                "token" => $FCM_token,
                "notification" => [
                    "title" => $title,
                    "body" => $body
                ],
                "data" => [
                    "route" => $loadId ? '/' . $loadId : '',
                ]
            ],
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($notification));
        curl_exec($ch);
        curl_close($ch);
    }

    public function verifyDriverPayZibal(Request $request)
    {
        $Authority = $request->trackId;
        $success = $request->success;

        $transaction = Transaction::where('authority', $Authority)->first();

        if (!$transaction) {
            $status = 0;
            $message = $this->getStatusMessage($status);
            return view('users.driverPayStatus', compact('message', 'status', 'Authority'));
        }

        // ✅ جلوگیری از تکرار اعمال تراکنش
        if ($transaction->status == 100) {
            // تراکنش قبلاً تأیید شده، فقط پیام موفقیت را نشان بده
            $status = 100;
            $message = $this->getStatusMessage($status);
            $authority = $transaction->authority;
            return view('users.driverPayStatus', compact('message', 'status', 'authority'));
        }

        if ($success == 1) {
            try {
                DB::beginTransaction();

                $transaction->status = 100;
                $transaction->RefId = $Authority;
                $transaction->save();

                $driver = Driver::find($transaction->user_id);

                $daysToAdd = 30 * $transaction->monthsOfThePackage;

                if (!$driver->activeDate || Carbon::parse($driver->activeDate)->lt(Carbon::now())) {
                    $driver->activeDate = Carbon::now()->addDays($daysToAdd);
                } else {
                    $driver->activeDate = Carbon::parse($driver->activeDate)->addDays($daysToAdd);
                }

                $driver->save();

                DB::commit();

                $status = 100;
                $message = $this->getStatusMessage($status);
                $authority = $transaction->authority;

                try {
                    if (!empty($driver->FCM_token) && $driver->version > 68) {
                        $today = date('Y/m/d');
                        $persianDate = gregorianDateToPersian($today, '/');

                        // نگاشت ماه‌ها به تعداد روز
                        $packageMonths = [
                            '1' => '+30 day',
                            '3' => '+90 day',
                            '6' => '+180 day',
                        ];

                        // محاسبه تاریخ انقضا بر اساس پکیج
                        $expireDate = '';
                        if (!empty($packageMonths[$transaction->monthsOfThePackage])) {
                            $expireDate = gregorianDateToPersian(
                                date('Y/m/d', strtotime($packageMonths[$transaction->monthsOfThePackage])),
                                '/'
                            );
                        }
                        // پیام
                        $title = 'راننده عزیز، 🎉';
                        $body  = "خرید شما در تاریخ {$persianDate} با موفقیت انجام شد.\nتاریخ پایان اعتبار: {$expireDate} 📞";

                        $this->sendNotificationWeb($driver->FCM_token, $title, $body);
                    }
                } catch (\Exception $e) {
                    Log::warning('نوتیف مربوط به افزایش اعتبار راننده');
                    Log::warning($e->getMessage());
                }

                return view('users.driverPayStatus', compact('message', 'status', 'authority'));
            } catch (\Exception $exception) {
                DB::rollBack();
                $status = 0;
                $message = 'خطایی در پردازش پرداخت رخ داده است.';
                return view('users.driverPayStatus', compact('message', 'status', 'Authority'));
            }
        } else {
            $transaction->status = 0;
            $transaction->save();
        }

        $status = 0;
        $message = $this->getStatusMessage($status);
        $authority = $transaction->authority;

        return view('users.driverPayStatus', compact('message', 'status', 'authority'));
    }

    public function verifyDriverPay()
    {
        $Authority = $_GET['Authority'] ?? null;
        $statusParam = $_GET['Status'] ?? null;

        $transaction = Transaction::where('authority', $Authority)->first();

        if (!$transaction) {
            return view('users.driverPayStatus', [
                'message' => 'تراکنش یافت نشد',
                'status' => 0
            ]);
        }

        // ✅ اگر تراکنش قبلاً تایید شده، دیگر دوباره پردازش نکن
        if ($transaction->status == 100) {
            $message = $this->getStatusMessage(100);
            $authority = $transaction->authority;
            return view('users.driverPayStatus', compact('message', 'authority') + ['status' => 100]);
        }

        if ($statusParam == 'OK') {
            $client = new SoapClient('https://www.zarinpal.com/pg/services/WebGate/wsdl', ['encoding' => 'UTF-8']);

            $result = $client->PaymentVerification([
                'MerchantID' => MERCHANT_ID,
                'Authority' => $Authority,
                'Amount' => $transaction->amount,
            ]);

            try {
                DB::beginTransaction();

                if ($result->Status == 100) {
                    $transaction->status = $result->Status;
                    $transaction->RefId = $result->RefID;
                    $transaction->save();

                    $driver = Driver::find($transaction->user_id);

                    $daysToAdd = 30 * $transaction->monthsOfThePackage;

                    // ✅ بررسی انقضا یا تمدید تاریخ فعال بودن راننده
                    if (!$driver->activeDate || Carbon::parse($driver->activeDate)->lt(Carbon::now())) {
                        $driver->activeDate = Carbon::now()->addDays($daysToAdd);
                    } else {
                        $driver->activeDate = Carbon::parse($driver->activeDate)->addDays($daysToAdd);
                    }

                    // محدود کردن تماس‌های رایگان
                    if ($driver->freeCalls > 3) {
                        $driver->freeCalls = 3;
                    }

                    $driver->save();
                } else {
                    $transaction->status = $result->Status;
                    $transaction->save();
                }

                DB::commit();

                $status = $result->Status;
                $authority = $transaction->authority;
                $message = $this->getStatusMessage($status);


                try {
                    if (!empty($driver->FCM_token) && $driver->version > 68) {
                        $today = date('Y/m/d');
                        $persianDate = gregorianDateToPersian($today, '/');

                        // نگاشت ماه‌ها به تعداد روز
                        $packageMonths = [
                            '1' => '+30 day',
                            '3' => '+90 day',
                            '6' => '+180 day',
                        ];

                        // محاسبه تاریخ انقضا بر اساس پکیج
                        $expireDate = '';
                        if (!empty($packageMonths[$transaction->monthsOfThePackage])) {
                            $expireDate = gregorianDateToPersian(
                                date('Y/m/d', strtotime($packageMonths[$transaction->monthsOfThePackage])),
                                '/'
                            );
                        }
                        // پیام
                        $title = 'راننده عزیز، 🎉';
                        $body  = "خرید شما در تاریخ {$persianDate} با موفقیت انجام شد.\nتاریخ پایان اعتبار: {$expireDate} 📞";

                        $this->sendNotificationWeb($driver->FCM_token, $title, $body);
                    }
                } catch (\Exception $e) {
                    Log::warning('نوتیف مربوط به افزایش اعتبار راننده');
                    Log::warning($e->getMessage());
                }



                return view('users.driverPayStatus', compact('message', 'status', 'authority'));
            } catch (\Exception $exception) {
                DB::rollBack();
                Log::error("verifyDriverPay error: " . $exception->getMessage());
                return view('users.driverPayStatus', [
                    'message' => 'خطا در تایید پرداخت، لطفا با پشتیبانی تماس بگیرید.',
                    'status' => 0
                ]);
            }
        }

        // اگر Status != OK یا خطایی رخ داده
        $status = 0;
        $authority = $transaction->authority;
        $transaction->status = 0;
        $transaction->save();
        $message = $this->getStatusMessage($status);

        return view('users.driverPayStatus', compact('message', 'status', 'authority'));
    }


    /*****************************************************************************************/

    public function payCustomer($packageName, Customer $customer, $action = null)
    {

        $customerPackagesInfo = getCustomerPackagesInfo();
        if ($action == 'call')
            $customerPackagesInfo = getCustomerCallPackagesInfo();

        if (!isset($customerPackagesInfo[$packageName]['price']))
            return abort(404);

        $monthsOfThePackage = 0;
        switch ($packageName) {
            case 'monthly':
                $monthsOfThePackage = 1;
                break;
            case 'trimester':
                $monthsOfThePackage = 3;
                break;
            case 'sixMonths':
                $monthsOfThePackage = 6;
                break;
        }

        $amount = $customerPackagesInfo[$packageName]['price'];

        $CallbackURL = 'http://dashboard.iran-tarabar.com/verifyCustomerPay';

        $client = new SoapClient('https://www.zarinpal.com/pg/services/WebGate/wsdl', ['encoding' => 'UTF-8']);
        $result = $client->PaymentRequest(
            [
                'MerchantID' => MERCHANT_ID,
                'Amount' => $amount,
                'Description' => "ایران ترابر",
                'Email' => "",
                'Mobile' => "",
                'CallbackURL' => $CallbackURL,
            ]
        );
        if ($result->Status == 100) {
            try {

                $transaction = new Transaction();
                $transaction->user_id = $customer->id;
                $transaction->userType = ROLE_CARGo_OWNER;
                $transaction->authority = $result->Authority;
                $transaction->amount = $amount;
                $transaction->monthsOfThePackage = $monthsOfThePackage;
                $transaction->action = $action;
                $transaction->save();

                if (isset($transaction->id))
                    return redirect('https://www.zarinpal.com/pg/StartPay/' . $result->Authority);
            } catch (\Exception $exception) {
            }
        }
    }

    public function verifyCustomerPay()
    {
        $Authority = $_GET['Authority'];

        $transaction = Transaction::where('authority', $Authority)->first();

        if (isset($transaction->id)) {

            if ($_GET['Status'] == 'OK') {

                $client = new SoapClient('https://www.zarinpal.com/pg/services/WebGate/wsdl', ['encoding' => 'UTF-8']);

                $result = $client->PaymentVerification(
                    [
                        'MerchantID' => MERCHANT_ID,
                        'Authority' => $Authority,
                        'Amount' => $transaction->amount,
                    ]
                );

                $numOfDays = 30;

                try {
                    $numOfDays = getNumOfCurrentMonthDays();
                } catch (\Exception $exception) {
                }

                try {

                    DB::beginTransaction();

                    if ($result->Status == 100) {
                        $transaction->status = $result->Status;
                        $transaction->RefId = $result->RefID;
                        $transaction->save();

                        $customer = Customer::find($transaction->user_id);

                        if ($transaction->action == 'call') {
                            $customer->freeCalls = ($customer->freeCalls > 0 ? $customer->freeCalls : 0) + CUSTOMER_FREE_DRIVER_CALLS;
                            $customer->callsDate = ($customer->callsDate > 0 ? $customer->callsDate : 0) + date("Y-m-d H:i:s", time() + $numOfDays * 24 * 60 * 60 * $transaction->monthsOfThePackage);
                        } else {
                            $customer->freeLoads = ($customer->freeLoads > 0 ? $customer->freeLoads : 0) + CUSTOMER_FREE_LOADS;
                            $customer->activeDate = date("Y-m-d H:i:s", time() + $numOfDays * 24 * 60 * 60 * $transaction->monthsOfThePackage);
                        }
                        $customer->save();
                    } else {
                        $transaction->status = $result->Status;
                        $transaction->save();
                    }

                    DB::commit();

                    $status = $result->Status;
                    $message = $this->getStatusMessage($status);

                    return view('users.customerPayStatus', compact('message', 'status'));
                } catch (\Exception $exception) {
                    DB::rollBack();
                }
            }
        }
        $status = 0;
        $message = $this->getStatusMessage($status);
        return view('users.customerPayStatus', compact('message', 'status'));
    }


    public function paymentPackage($packageName, Driver $driver)
    {
        $driverPackagesInfo = getDriverPackagesInfo();
        if (!isset($driverPackagesInfo['data'][$packageName]['price']))
            return abort(404);

        $monthsOfThePackage = 0;
        switch ($packageName) {
            case 'monthly':
                $monthsOfThePackage = 1;
                break;
            case 'trimester':
                $monthsOfThePackage = 3;
                break;
            case 'sixMonths':
                $monthsOfThePackage = 6;
                break;
        }


        $amount = $driverPackagesInfo['data'][$packageName]['price'];

        $CallbackURL = 'http://iran-taraabar.ir/verifyDriverPay';

        $client = new SoapClient('https://www.zarinpal.com/pg/services/WebGate/wsdl', ['encoding' => 'UTF-8']);
        $anotherMerchant = in_array($driver->id, [45050, 95120, 128319, 95120, 1469, 131114, 180206, 24721, 175343, 68704, 46445, 68739, 50140, 59334, 203099]);

        $result = $client->PaymentRequest(
            [
                'MerchantID' => $anotherMerchant ? MERCHANT_ID : '6ea834ac-0327-4513-83c0-ff59bb090255',
                'Amount' => $amount,
                'Description' => "فروشگاه",
                'Email' => "",
                'Mobile' => "",
                'CallbackURL' => $CallbackURL,
            ]
        );
        if ($result->Status == 100) {

            try {

                $transaction = new Transaction();
                $transaction->user_id = $driver->id;
                $transaction->userType = ROLE_DRIVER;
                $transaction->authority = $result->Authority;
                $transaction->bank_name = ZARINPAL;
                $transaction->status = 2;
                $transaction->action = 'delete';
                $transaction->amount = $amount;
                $transaction->monthsOfThePackage = $monthsOfThePackage;
                $transaction->save();

                try {
                    $driver = Driver::find($transaction->user_id);

                    if (
                        Transaction::where('user_id', $driver->id)
                        ->where('userType', 'driver')
                        ->where('created_at', '>', date('Y-m-d', time()) . ' 00:00:00')
                        ->count() == 5
                    ) {
                        $sms = new Driver();
                        $sms->unSuccessPayment($driver->mobileNumber);
                    }
                } catch (Exception $exception) {
                    Log::emergency("-------------------------------- unSuccessPayment -----------------------------");
                    Log::emergency($exception->getMessage());
                    Log::emergency("------------------------------------------------------------------------------");
                }

                if (isset($transaction->id))
                    return redirect('https://www.zarinpal.com/pg/StartPay/' . $result->Authority);
            } catch (\Exception $exception) {
            }
        }
    }

    public function paymentPackageVerify()
    {
        $Authority = $_GET['Authority'] ?? null;
        $statusParam = $_GET['Status'] ?? null;

        $transaction = Transaction::where('authority', $Authority)->first();

        if (!$transaction) {
            return view('users.driverPayStatus', [
                'message' => 'تراکنش یافت نشد',
                'status' => 0
            ]);
        }

        // ✅ جلوگیری از تکرار اعمال تراکنش در صورت تأیید قبلی
        if (in_array($transaction->status, [100, -52])) {
            $message = $this->getStatusMessage(100);
            $authority = $transaction->authority;
            return view('users.driverPayStatus', compact('message', 'authority') + ['status' => 100]);
        }

        if ($statusParam == 'OK') {
            $client = new SoapClient('https://www.zarinpal.com/pg/services/WebGate/wsdl', ['encoding' => 'UTF-8']);

            $anotherMerchant = in_array($transaction->user_id, [
                45050,
                95120,
                128319,
                95120,
                1469,
                131114,
                180206,
                24721,
                175343,
                68704,
                46445,
                68739,
                50140,
                59334,
                203099
            ]);

            $result = $client->PaymentVerification([
                'MerchantID' => $anotherMerchant ? MERCHANT_ID : '6ea834ac-0327-4513-83c0-ff59bb090255',
                'Authority' => $Authority,
                'Amount' => $transaction->amount,
            ]);

            try {
                DB::beginTransaction();

                if ($result->Status == 100) {
                    $transaction->status = '-52';
                    $transaction->RefId = $result->RefID;
                    $transaction->save();

                    $driver = Driver::find($transaction->user_id);

                    $daysToAdd = 30 * $transaction->monthsOfThePackage;

                    // بررسی اگر فعالیت قبلی منقضی شده یا وجود ندارد
                    if (!$driver->activeDate || Carbon::parse($driver->activeDate)->lt(Carbon::now())) {
                        $driver->activeDate = Carbon::now()->addDays($daysToAdd);
                    } else {
                        $driver->activeDate = Carbon::parse($driver->activeDate)->addDays($daysToAdd);
                    }

                    $driver->freeCalls = 3;
                    $driver->save();
                } else {
                    $transaction->status = in_array($result->Status, [100, 101]) ? -52 : $result->Status;
                    $transaction->save();
                }

                DB::commit();

                $status = $result->Status;
                $authority = $transaction->authority;
                $message = $this->getStatusMessage($status);

                try {
                    if (!empty($driver->FCM_token) && $driver->version > 68) {
                        $today = date('Y/m/d');
                        $persianDate = gregorianDateToPersian($today, '/');

                        // نگاشت ماه‌ها به تعداد روز
                        $packageMonths = [
                            '1' => '+30 day',
                            '3' => '+90 day',
                            '6' => '+180 day',
                        ];

                        // محاسبه تاریخ انقضا بر اساس پکیج
                        $expireDate = '';
                        if (!empty($packageMonths[$transaction->monthsOfThePackage])) {
                            $expireDate = gregorianDateToPersian(
                                date('Y/m/d', strtotime($packageMonths[$transaction->monthsOfThePackage])),
                                '/'
                            );
                        }
                        // پیام
                        $title = 'راننده عزیز، 🎉';
                        $body  = "خرید شما در تاریخ {$persianDate} با موفقیت انجام شد.\nتاریخ پایان اعتبار: {$expireDate} 📞";

                        $this->sendNotificationWeb($driver->FCM_token, $title, $body);
                    }
                } catch (\Exception $e) {
                    Log::warning('نوتیف مربوط به افزایش اعتبار راننده');
                    Log::warning($e->getMessage());
                }

                return view('users.driverPayStatus', compact('message', 'status', 'authority'));
            } catch (\Exception $exception) {
                DB::rollBack();
                Log::error("paymentPackageVerify error: " . $exception->getMessage());
                return view('users.driverPayStatus', [
                    'message' => 'خطا در تایید پرداخت، لطفا با پشتیبانی تماس بگیرید.',
                    'status' => 0
                ]);
            }
        }

        // وضعیت ناموفق
        $status = 0;
        $authority = $transaction->authority;
        $transaction->status = 0;
        $transaction->save();
        $message = $this->getStatusMessage($status);

        return view('users.driverPayStatus', compact('message', 'status', 'authority'));
    }

    /*****************************************************************************************/
    // پرداخت هزینه کنترل ناوگان
    public function fleetControlPay($numOfFleetControl, $userType, $user_id)
    {

        if ($userType != ROLE_CUSTOMER && $userType != ROLE_TRANSPORTATION_COMPANY)
            return abort(404);

        $user = '';

        if ($userType == ROLE_CUSTOMER)
            $user = Customer::find($user_id);
        else if ($userType == ROLE_TRANSPORTATION_COMPANY)
            $user = Bearing::find($user_id);

        if (!isset($user->id))
            return abort(404);

        $CallbackURL = 'https://dashboard.iran-tarabar.ir/verifyFleetControlPay';

        $amount = FLEET_CONTROL_PRICE * $numOfFleetControl;

        $client = new SoapClient('https://www.zarinpal.com/pg/services/WebGate/wsdl', ['encoding' => 'UTF-8']);
        $result = $client->PaymentRequest(
            [
                'MerchantID' => MERCHANT_ID,
                'Amount' => $amount,
                'Description' => "ایران ترابر",
                'Email' => "",
                'Mobile' => "",
                'CallbackURL' => $CallbackURL,
            ]
        );
        if ($result->Status == 100) {

            try {

                $transaction = new Transaction();
                $transaction->user_id = $user->id;
                $transaction->userType = $userType;
                $transaction->authority = $result->Authority;
                $transaction->amount = $amount;
                $transaction->monthsOfThePackage = 1;
                $transaction->save();

                $purchasedFleetControlPackage = new PurchasedFleetControlPackage();
                $purchasedFleetControlPackage->user_id = $user_id;
                $purchasedFleetControlPackage->numOfFleetControl = $numOfFleetControl;
                $purchasedFleetControlPackage->userType = $userType;
                $purchasedFleetControlPackage->transaction_id = $transaction->id;
                $purchasedFleetControlPackage->save();

                if (isset($transaction->id))
                    return redirect('https://www.zarinpal.com/pg/StartPay/' . $result->Authority);
            } catch (\Exception $exception) {
            }
        }
    }

    public function verifyFleetControlPay()
    {


        $Authority = $_GET['Authority'];

        $transaction = Transaction::where('authority', $Authority)->first();

        if (isset($transaction->id)) {

            if ($_GET['Status'] == 'OK') {

                $client = new SoapClient('https://www.zarinpal.com/pg/services/WebGate/wsdl', ['encoding' => 'UTF-8']);

                $result = $client->PaymentVerification(
                    [
                        'MerchantID' => MERCHANT_ID,
                        'Authority' => $Authority,
                        'Amount' => $transaction->amount,
                    ]
                );

                try {

                    DB::beginTransaction();

                    if ($result->Status == 100) {
                        $transaction->status = $result->Status;
                        $transaction->RefId = $result->RefID;
                        $transaction->save();

                        $purchasedFleetControlPackage = PurchasedFleetControlPackage::where('transaction_id', $transaction->id)->first();

                        $user = '';
                        if ($transaction->userType == ROLE_CUSTOMER)
                            $user = Customer::find($transaction->user_id);
                        else if ($transaction->userType == ROLE_TRANSPORTATION_COMPANY)
                            $user = Bearing::find($transaction->user_id);

                        $user->numOfFleetControl = $purchasedFleetControlPackage->numOfFleetControl + ($purchasedFleetControlPackage->numOfFleetControl / FLEET_CONTROL_DISCOUNT);
                        $user->save();
                    } else {
                        $transaction->status = $result->Status;
                        $transaction->save();
                    }

                    DB::commit();

                    $status = $result->Status;
                    $message = $this->getStatusMessage($status);

                    return view('users.customerPayStatus', compact('message', 'status'));
                } catch (\Exception $exception) {

                    Log::emergency("**************************** verifyFleetControlPay ******************************");
                    Log::emergency($exception->getMessage());
                    Log::emergency("*********************************************************************************");


                    DB::rollBack();
                }
            }
        }
        $status = 0;
        $message = $this->getStatusMessage($status);
        return view('users.customerPayStatus', compact('message', 'status'));
    }
}
