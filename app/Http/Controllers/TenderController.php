<?php

namespace App\Http\Controllers;

use App\Models\Bearing;
use App\Models\Customer;
use App\Models\Load;
use App\Models\Tender;
use App\Models\TenderStart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TenderController extends Controller
{

    public function suggestionPriceInWeb(Request $request)
    {
        $result = $this->suggestionPrice($request);
        return redirect('user/loadInfo/' . $request->load_id)->with('result', $result);
    }

    // پیشنهاد قیمت از طرف باربری
    public function suggestionPrice(Request $request)
    {

        try {

            $request->suggestedPrice = str_replace(',', '', $request->suggestedPrice);

            if (Load::where([['id', $request->load_id], ['status', '<', '2']])->count() == 0) {
                return [
                    'result' => UN_SUCCESS,
                    'message' => 'ثبت قیمت به پایان رسید'
                ];
            }

            if ($request->suggestedPrice < 99000) {
                return [
                    'result' => UN_SUCCESS,
                    'message' => 'مبلغ پیشنهادی باید حداقل 99 هزار تومان باشد'
                ];
            }

            $tender_start = '';

            if ($this->getLoadStatus($request->load_id) == 0) {

                if (Tender::where('load_id', $request->load_id)->count() == 0) {
                    $tender_start = date("Y-m-d H:i:s");
                    Load::where('id', $request->load_id)
                        ->update([
                            'tender_start' => $tender_start,
                            'status' => 1
                        ]);
                }
            } else {
                $load = Load::where('id', $request->load_id)->first();
                $tender_start = $load->tender_start;
            }

            $remainingTime = (TNDER_TIME - DateController::getSecondFromCreateRowToPresent($tender_start));

            // اگر قبلا این باربری برای این بار قیمتی پیشنهاد داده است که بروز شود
            // در غیر این صورت درج شود
            if (Tender::where([
                    ['load_id', $request->load_id],
                    ['bearing_id', $request->bearing_id],
                ])->count() == 0) {

                $tender = new Tender();
                $tender->load_id = $request->load_id;
                $tender->bearing_id = $request->bearing_id;
                $tender->suggestedPrice = $request->suggestedPrice;
                $tender->status = 0;
                if (isset($request->description))
                    $tender->description = $request->description;
                $tender->save();

                if (TenderStart::where('load_id', $request->load_id)->count() == 0) {
                    $tenderStart = new TenderStart();
                    $tenderStart->load_id = $request->load_id;
                    $tenderStart->tender_start = date("Y-m-d H:i:s");
                    $tenderStart->type = 'tender';
                    $tenderStart->save();
                }

            } else {
                Tender::where('load_id', $request->load_id)
                    ->where('bearing_id', $request->bearing_id)
                    ->update([
                        'suggestedPrice' => $request->suggestedPrice,
                        'description' => $request->description
                    ]);
            }

            $this->sendNewSuggestionNotification($request->load_id, $request->bearing_id);
            $this->sendNewSuggestionNotificationCustomer($request->load_id);

            return [
                'result' => SUCCESS,
                'remainingTime' => $remainingTime
            ];


        } catch (\Exception $exception) {
            Log::emergency($exception->getMessage());
        }

    }

    public function requestSuggestionsOfATender($load_id)
    {
        // ابتدا وضعیت مناقصه مشخص شود
        $load = Load::where('id', $load_id)->first();

        if ($load) {
            if ($load->status > 1) {

                return [
                    'result' => FINISHED,
                    'message' => 'ثبت قیمت به پایان رسید'
                ];
            }

            $suggestions = Tender::join('bearings', 'bearings.id', 'tenders.bearing_id')
                ->where('tenders.load_id', $load_id)
                ->orderby('tenders.suggestedPrice', 'asc')
                ->select('tenders.suggestedPrice', 'tenders.bearing_id', 'bearings.title as bearingTitle')
                ->get();

            $remainingTime = 0;
            $remainingTimeStatus = 'noStart';
            if ($load->tender_start != null) {
                $remainingTime = (TNDER_TIME - DateController::getSecondFromCreateRowToPresent($load->tender_start));
                $remainingTimeStatus = 'start';
            }

            if (count($suggestions) > 0) {
                return [
                    'result' => SUCCESS,
                    'suggestions' => $suggestions,
                    'remainingTimeStatus' => $remainingTimeStatus,
                    'remainingTime' => $remainingTime
                ];
            }
        }
        return [
            'result' => UN_SUCCESS,
            'message' => 'هیچ پیشنهادی ثبت نشده است'
        ];
    }

    //
    public function stopTender(Request $request)
    {
        $load_id = $request->load_id;
        $customer_id = $request->customer_id;

        $load = Load::where([
            ['id', $load_id],
            ['customer_id', $customer_id]
        ])->count();

        if ($load) {

            Load::where('id', $load_id)
                ->update(['status' => STOP_TENDER]);

            return [
                'result' => SUCCESS
            ];
        }

        return [
            'result' => UN_SUCCESS,
            'message' => 'چنین باری وجود ندارد'
        ];
    }

    // درخواست لیست باربری های که قیمت کمتر را ثبت کرده اند
    public function requestTopBearingListInTender($load_id)
    {
        $topBearingList = Tender::join('bearings', 'bearings.id', '=', 'tenders.bearing_id')
            ->where('tenders.load_id', $load_id)
            ->select('tenders.bearing_id', 'bearings.title', 'bearings.operatorName', 'bearings.grade', 'bearings.score', 'tenders.suggestedPrice')
            ->orderBy('tenders.suggestedPrice', 'asc')
            ->get();

        return ['topBearingList' => $topBearingList];
    }

    // درخواست لیست باربری های که قیمت کمتر را ثبت کرده اند
    public static function requestABearingPriceInTender($load_id, $bearing_id)
    {
        $tender = Tender::where([
            ['load_id', $load_id],
            ['bearing_id', $bearing_id]
        ])
            ->select('suggestedPrice')
            ->first();
        return $tender->suggestedPrice;
    }

    public static function requestBearingPriceInTender($load_id, $bearing_id)
    {
        $tender = Tender::where([
            ['load_id', $load_id],
            ['bearing_id', $bearing_id]
        ])
            ->select('suggestedPrice')
            ->first();
        if ($tender)
            return $tender->suggestedPrice;

        return 0;
    }

    private function getLoadStatus($load_id)
    {
        $load = Load::where('id', $load_id)->first();
        return $load->status;
    }

    // ارسال نوتیفیکیشن برای راننده
    private function sendNewSuggestionNotification($load_id, $bearing_id)
    {
        $data = [
            'title' => 'قیمت جدید',
            'body' => 'یک قیمت جدید',
            'load_id' => $load_id,
            'notificationType' => 'newSuggestionInTender'
        ];


        $bearings = Bearing::join('tenders', 'bearings.id', '=', 'tenders.bearing_id')
            ->where('tenders.load_id', $load_id)
            ->whereRaw('LENGTH(FCM_token)>10')
            ->select('bearings.FCM_token', 'bearings.id')
            ->get();

        foreach ($bearings as $bearing) {
            if ($bearing->id == $bearing_id)
                continue;

            $url = 'https://fcm.googleapis.com/fcm/send';

            $notification = [
                'body' => $data['body'],
                'sound' => true,
            ];
            $fields = array(
                'to' => $bearing->FCM_token,
                'notification' => $notification,
                'data' => $data
            );

            $headers = array(
                'Authorization: key=' . API_ACCESS_KEY_TRANSPORTATION_COMPANY,
                'Content-Type: application/json'
            );

            #Send Reponse To FireBase Server
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            $result = curl_exec($ch);
            if ($result === FALSE) {
                die('Oops! FCM Send Error: ' . curl_error($ch));
            }

            curl_close($ch);
        }
    }

    // ارسال نوتیفیکیشن برای راننده
    private function sendNewSuggestionNotificationCustomer($load_id)
    {

        $load = Load::where('id', $load_id)->first();
        $customer = Customer::where('id', $load->user_id)->first();

        if ($customer && isset($customer->FCM_token) && strlen($customer->FCM_token)) {

            $data = [
                'title' => 'قیمت جدید',
                'body' => 'یک قیمت جدید',
                'load_id' => $load_id,
                'notificationType' => 'newSuggestionInTender'
            ];


            $url = 'https://fcm.googleapis.com/fcm/send';

            $notification = [
                'body' => $data['body'],
                'sound' => true,
            ];
            $fields = array(
                'to' => $customer->FCM_token,
                'notification' => $notification,
                'data' => $data
            );

            $headers = array(
                'Authorization: key=' . API_ACCESS_KEY_USER,
                'Content-Type: application/json'
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            $result = curl_exec($ch);
            if ($result === FALSE) {
                die('Oops! FCM Send Error: ' . curl_error($ch));
            }
            curl_close($ch);
        }
    }

    // اجرای مجدد مناقصه
    public function repeatTender($load_id)
    {
        Tender::where('load_id', $load_id)->delete();
        Load::where('id', $load_id)
            ->update([
                'tender_start' => null,
                'bearing_id' => null,
                'status' => 0
            ]);

        /*
        $load = Load::select('origin_state_id', 'user_id')
            ->where('loads.id', $load_id)
            ->first();

        $this->sendNewLoadNotificationForBearing($load->origin_state_id, $load_id);
*/
        $message = 'اجرای مجدد مناقصه انجام شد';
        $alert = 'alert-success';
        return view('admin.alert', compact('message', 'alert'));
    }

    public function sendNewLoadNotificationForBearing($origin_state_id, $load_id)
    {
        $bearings = Bearing::where([
            ['state_id', $origin_state_id],
            ['notification', 'enable'],
            ['status', 1],
        ])
            ->whereRaw('LENGTH(FCM_token)>10')
            ->select('FCM_token')
            ->get();

        $data = [
            'title' => 'بار جدید',
            'body' => 'یک بار جدید دریافت کرده اید',
            'load_id' => $load_id,
            'notificationType' => 'newLoad'
        ];


        foreach ($bearings as $bearing) {
            $this->sendNotification($bearing->FCM_token, $data, API_ACCESS_KEY_TRANSPORTATION_COMPANY);
        }
    }

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

    public function suggestionToLoadPriceByAdmin(Request $request)
    {
        $request->insertOrUpdate = INSERT;
        // اگر قبلا این باربری برای این بار قیمتی پیشنهاد داده است که بروز شود
        if (Tender::where([
                ['load_id', $request->load_id],
                ['bearing_id', $request->bearing_id],
            ])->count() > 0) {
            $request->insertOrUpdate = UPDATE;
        }

        $request->suggestedPrice = str_replace(',', '', $request->suggestedPrice);

        $tender_start = '';

        if ($this->getLoadStatus($request->load_id) == 0) {

            if (Tender::where('load_id', $request->load_id)->count() == 0) {
                $tender_start = date("Y-m-d H:i:s");
                Load::where('id', $request->load_id)
                    ->update([
                        'tender_start' => $tender_start,
                        'status' => 1
                    ]);
            }
        } else {
            $load = Load::where('id', $request->load_id)->first();
            $tender_start = $load->tender_start;
        }

//        $remainingTime = (TNDER_TIME - DateController::getSecondFromCreateRowToPresent($tender_start));

        switch ($request->insertOrUpdate) {
            case INSERT:
                $tender = new Tender();
                $tender->load_id = $request->load_id;
                $tender->bearing_id = $request->bearing_id;
                $tender->suggestedPrice = $request->suggestedPrice;
                $tender->status = 0;
                $tender->save();

                if (TenderStart::where('load_id', $request->load_id)->count() == 0) {
                    $tenderStart = new TenderStart();
                    $tenderStart->load_id = $request->load_id;
                    $tenderStart->tender_start = date("Y-m-d H:i:s");
                    $tenderStart->type = 'tender';
                    $tenderStart->save();
                }

                break;
            case UPDATE:
                Tender::where('load_id', $request->load_id)
                    ->where('bearing_id', $request->bearing_id)
                    ->update(['suggestedPrice' => $request->suggestedPrice]);
                break;
        }

//        $this->sendNewSuggestionNotification($request->load_id, $request->bearing_id);
        $this->sendNewSuggestionNotificationCustomer($request->load_id);

        $message = 'قیمت پیشنهادی ثبت شد';
        $alert = 'alert-success';
        $buttonUrl = 'admin/loadInfo/' . $request->load_id;

        return view('admin.alert', compact('message', 'alert', 'buttonUrl'));

    }

    // درخواست قیمت پیشنهادی شرکت کمپانی
    public function requestTransportationCompanySuggestionPrice($transportationCompany_id, $load_id)
    {

        try {

            $data = Tender::where([
                'bearing_id' => $transportationCompany_id,
                'load_id' => $load_id
            ])
                ->select('suggestedPrice', 'description')
                ->first();

            if ($data)
                return [
                    'result' => SUCCESS,
                    'data' => $data,
                    'message' => null
                ];

        } catch (\Exception $exception) {
            Log::emergency($exception->getMessage());
        }

        return [
            'result' => UN_SUCCESS,
            'data' => null,
            'message' => 'هیچ قیمتی ذخیره نشده'
        ];
    }

    // توقف مناقصه به صورت دستی
    public function stopTenderManually(Load $load)
    {

        try {

            DB::beginTransaction();

            $load->status = STOP_TENDER;
            $load->save();

            $data = [
                'title' => ' استعلام قیمت',
                'body' => 'استعلام قیمت به صورت دستی توسط صاحب بار به پایان رسید',
                'load_id' => $load->id,
                'notificationType' => 'TENDER_STOP'
            ];

            $bearings = Bearing::whereIn('id', Tender::where('load_id', $load->id)->pluck("bearing_id"))
                ->whereRaw('LENGTH(FCM_token)>10')
                ->select('FCM_token')
                ->get();

            foreach ($bearings as $bearing)
                $this->sendNotification($bearing->FCM_token, $data, API_ACCESS_KEY_TRANSPORTATION_COMPANY);

            DB::commit();

            return [
                'result' => SUCCESS
            ];
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::emergency($exception->getMessage());
        }
        return [
            'result' => UN_SUCCESS,
            'message' => 'چنین باری وجود ندارد'
        ];
    }
}
