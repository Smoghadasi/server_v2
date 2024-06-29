<?php

namespace App\Http\Controllers;

use App\Models\Bearing;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\PurchasedFleetControlPackage;
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
                return "عمليات با موفقيت انجام گرديده است.";
            case "101":
                return "عمليات پرداخت موفق بوده و قبلا PaymentVerification تراكنش انجام شده است.";
            default:
                return "خطای نامشخص هنگام اتصال به درگاه زرین پال";
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
                $transaction->amount = $amount;
                $transaction->monthsOfThePackage = $monthsOfThePackage;
                $transaction->save();

                try {
                    $driver = Driver::find($transaction->user_id);

                    if (Transaction::where('user_id', $driver->id)
                        ->where('userType', 'driver')
                        ->where('created_at', '>', date('Y-m-d', time()) . ' 00:00:00')
                        ->count() == 3) {
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

    public function verifyDriverPay()
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

                        $numOfDays = 30;

                        try {
                            $numOfDays = getNumOfCurrentMonthDays();
                        } catch (\Exception $exception) {
                        }


                        $activeDate = date("Y-m-d H:i:s", time() + $numOfDays * 24 * 60 * 60 * $transaction->monthsOfThePackage);
                        $driver = Driver::find($transaction->user_id);

                        try {
                            $date = new \DateTime($driver->activeDate);
                            $time = $date->getTimestamp();
                            if ($time < time())
                                $activeDate = date('Y-m-d', time() + $transaction->monthsOfThePackage * $numOfDays * 24 * 60 * 60);
                            else
                                $activeDate = date('Y-m-d', $time + $transaction->monthsOfThePackage * $numOfDays * 24 * 60 * 60);
                        } catch (\Exception $e) {
                        }
                        $driver->activeDate = $activeDate;
                        // خاور و نیسان
                        $driver->freeCalls = ($driver->freeCalls > 0 ? $driver->freeCalls : 0) + DRIVER_FREE_CALLS;

                        $driver->freeAcceptLoads = ($driver->freeAcceptLoads > 0 ? $driver->freeAcceptLoads : 0) + DRIVER_FREE_ACCEPT_LOAD;
                        $driver->save();
                    } else {
                        $transaction->status = $result->Status;
                        $transaction->save();
                    }

                    DB::commit();

                    $status = $result->Status;
                    $message = $this->getStatusMessage($status);

                    return view('users.driverPayStatus', compact('message', 'status'));
                } catch (\Exception $exception) {
                    DB::rollBack();
                }
            }
        }
        $status = 0;
        $message = $this->getStatusMessage($status);
        return view('users.driverPayStatus', compact('message', 'status'));
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
