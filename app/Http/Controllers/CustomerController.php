<?php

namespace App\Http\Controllers;

use App\Models\ActivationCode;
use App\Models\BlockPhoneNumber;
use App\Models\City;
use App\Models\Customer;
use App\Models\Fleet;
use App\Models\Load;
use App\Models\LoadType;
use App\Models\PackingType;
use HttpException;
use Illuminate\Http\Request;

use Kavenegar\Exceptions\ApiException;

use Kavenegar\KavenegarApi;
use Log;

class CustomerController extends Controller
{
    // مشتریان
    public function customers()
    {
        $customers = Customer::orderby('id', 'desc')->paginate(50);
        return view('admin.customers', compact('customers'));
    }

    // جستجوی مشتری
    public function searchCustomers(Request $request)
    {
        $word = $request->word;
        $customers = '';
        $message = 'جستجو بر اساس : ';
        switch ($request->searchMethod) {
            case 'name':
                $message .= ' نام - کلمه جستجو شده : ' . $word;
                $customers = Customer::where('name', 'LIKE', "%$word%")->orderby('id', 'desc')->paginate(100);
                break;
            case 'lastName':
                $message .= ' نام خانوادگی  - کلمه جستجو شده : ' . $word;
                $customers = Customer::where('lastName', 'LIKE', "%$word%")->orderby('id', 'desc')->paginate(100);
                break;
            case 'nationalCode':
                $message .= ' کدملی  - کلمه جستجو شده : ' . $word;
                $customers = Customer::where('nationalCode', 'LIKE', "%$word%")->orderby('id', 'desc')->paginate(100);
                break;
            case 'mobileNumber':
                $message .= ' شماره موبایل  - کلمه جستجو شده : ' . $word;
                $customers = Customer::where('mobileNumber', 'LIKE', "%$word%")->orderby('id', 'desc')->paginate(100);
                break;
        }

        $message .= ' - تعداد یافته ها : ' . count($customers) . ' مورد ';

        return view('admin.customers', compact('customers', 'message'));
    }

    // درخواست اطلاعات مشتری
    public function requestCustomerInfo(Customer $customer)
    {
        if ($customer) {
            return [
                'result' => SUCCESS,
                'customer' => $customer
            ];
        }

        return [
            'result' => UN_SUCCESS,
            'message' => 'چنین کاربری وجود ندارد'
        ];
    }

    // دریافت نام مشتری
    public static function getCustomerName($id)
    {
        $customer = Customer::where('id', $id)->first();
        if ($customer)
            return ($customer->name . ' ' . $customer->lastName);
        return 'بدون نام';
    }

    // درخواست نوع ناوگان، شهرها، نوع بسته بندی و نوع بارها
    public function requestFleetAndCitiesAndPackingTypeAndLoadTypeList()
    {
        return [
            'cities' => City::select('id', 'name')->get(),
            'fleets' => Fleet::select('id', 'title', 'pic', 'parent_id')->get(),
            'loadTypes' => LoadType::select('id', 'title', 'parent_id')->get(),
            'packingType' => PackingType::select('id', 'title')->get()
        ];
    }

    // ذخیره توکن FCM
    public function saveMyFireBaseToken(Request $request)
    {
        $customer_id = $request->customer_id;
        $token = $request->token;

        Customer::where('id', $customer_id)->update(['FCM_token' => $token]);
        return ['result' => SUCCESS];
    }

    // تغییر وضعیت مشتری
    public function changeCustomerStatus($customer_id)
    {
        $customer = Customer::where('id', $customer_id)
            ->select('status')
            ->first();

        $message = '';
        $alert = 'alert-success';

        if ($customer->status == 0) {
            Customer::where('id', $customer_id)
                ->update(['status' => 1]);
            $message = 'وضعیت به فعال تغییر یافت';
        } else {
            Customer::where('id', $customer_id)
                ->update(['status' => 0]);
            $message = 'وضعیت به غیر فعال تغییر یافت';
        }

        $buttonUrl = 'admin/customers';

        return view('admin.alert', compact('message', 'alert', 'buttonUrl'));
    }

    // حذف مشتری
    public function removeCustomer(Customer $customer)
    {
        $customer->delete();
        return back()->with("success", "مشتری مورد نظر حذف شد");
    }

    public function acceptCustomer(string $id)
    {
        $customer = Customer::findOrFail($id);
        $customer->isPublish = 1;
        $customer->save();
        $sms = new Customer();
        $sms->acceptCustomerSms($customer->mobileNumber);

        $loads = Load::where('user_id', $id)->get();
        foreach ($loads as $load) {
            if ($load->status = -1) {
                $load->status = 4;
                $load->save();
            }
        }
        return back()->with("success", "صاحب بار تایید شد");
    }

    public function rejectCustomer(string $id)
    {
        $customer = Customer::findOrFail($id);
        $blockNumber = new BlockPhoneNumber();
        $blockNumber->phoneNumber = $customer->mobileNumber;
        $blockNumber->name = $customer->name . " " . $customer->lastName;
        $blockNumber->description = "کلاهبرداری";
        $blockNumber->save();
        Load::where('user_id', $id)->delete();
        return back()->with("success", "صاحب بار رد شد");
    }

    // آپدیت اطلاعات صاحب بار
    public function updateCustomer(Request $request, Customer $customer)
    {
        $customer->name = $request->name;
        $customer->lastName = $request->lastName;
        $customer->mobileNumber = $request->mobileNumber;
        $customer->save();

        return back()->with('success', 'اطلاعات صاحب بار مورد نظر بروز شد');
    }

    // بررسی وضعیت شارژ اکانت صاحب بار
    public function checkCustomerAccountChargeStatus(Customer $customer, $action)
    {
        if (
            ($action == 'call' && ($customer->freeCalls > 0 || $customer->callsDate > date("Y-m-d H:i:s", time()))) ||
            ($action == 'newLoad' && ($customer->freeLoads > 0 || $customer->activeDate > date("Y-m-d H:i:s", time())))
        ) {

            if ($action == 'call' && $customer->freeCalls > 0 && $customer->callsDate < date("Y-m-d H:i:s", time())) {
                $customer->freeCalls--;
                $customer->save();
            }

            return [
                'result' => true,
                'message' => null,
                'data' => [
                    'customer' => $customer,
                    'action' => $action
                ]
            ];
        }

        $customerPackagesInfo = getCustomerPackagesInfo();
        if ($action == 'call')
            $customerPackagesInfo = getCustomerCallPackagesInfo();

        return [
            'result' => false,
            'message' => "برای برقراری تماس حساب کاربری خود را شارژ نمایید",
            'data' => [
                'customerPackagesInfo' => $customerPackagesInfo
            ]
        ];
    }
}
