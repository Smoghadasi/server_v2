<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Fleet;
use App\Models\FleetOperator;
use Exception;
use Illuminate\Http\Request;

class FleetController extends Controller
{
    /**
     * صفحه ناوگان.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($message = '')
    {
        $fleetsParents = Fleet::where('parent_id', 0)->get();
        $fleets = Fleet::where('parent_id', '>', 0)->orderby('parent_id', 'asc')->get();
        return view('admin.fleet.index', compact('fleets', 'fleetsParents', 'message'));
    }

    // دریافت نام ناوگان از روی کد ناوگان
    public static function getFleetName($id)
    {
        $fleet = Fleet::where('id', $id)->first();
        if ($fleet)
            return $fleet->title;
        return 'ناوگان بدون عنوان';
    }

    /**
     *  // فرم افزودن ناوگان جدید
     *
     * @return \Illuminate\Http\Response
     */
    public function create($message = '')
    {
        $fleets = Fleet::where('parent_id', 0)->get();
        return view('admin.fleet.create', compact('fleets', 'message'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'title' => 'required',
            'length' => 'required',
            'width' => 'required',
            'height' => 'required',
            'capacity' => 'required',
        ];

        $customAttr = [
            'title' => 'عنوان',
            'length' => 'طول',
            'width' => 'عرض',
            'height' => 'ارتفاع',
            'capacity' => 'ظرفیت',
        ];

        $this->validate($request, $rules, [], $customAttr);

        $pic = $request->file('pic');

        $fleet = new Fleet();
        $fleet->title = $request->title;
        $fleet->parent_id = $request->parent_id;
        $fleet->length = $request->length;
        $fleet->width = $request->width;
        $fleet->height = $request->height;
        $fleet->capacity = $request->capacity;
        $fleet->pic = $this->savePicOfFleet($pic);
        $fleet->save();
        return $this->create('ناوگان جدید با عنوان ' . $request->title . ' افزوده شد. ');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $fleet = Fleet::where('id', $id)->first();
        $fleetParents = Fleet::where('parent_id', 0)->get();
        return view('admin.fleet.edit', compact('fleet', 'fleetParents'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $rules = [
                'title' => 'required',
                'length' => 'required',
                'width' => 'required',
                'height' => 'required',
                'capacity' => 'required',
            ];

            $customAttr = [
                'title' => 'عنوان',
                'length' => 'طول',
                'width' => 'عرض',
                'height' => 'ارتفاع',
                'capacity' => 'ظرفیت',
            ];
            $this->validate($request, $rules, [], $customAttr);

            $id = $request->id;

            $pic = $request->file('pic');
            $fleet = Fleet::find($id);
            $fleet->title = $request->title;
            if (strlen($pic))
                $fleet->pic = $this->savePicOfFleet($pic);
            $fleet->parent_id = $request->parent_id;
            $fleet->length = $request->length;
            $fleet->width = $request->width;
            $fleet->height = $request->height;
            $fleet->capacity = $request->capacity;
            $fleet->save();

            $fleet = Fleet::where('id', $id)->first();
            $fleetParents = Fleet::where('parent_id', 0)->get();
            $message = 'ویرایش انجام شد';
            return view('admin.fleet.edit', compact('fleet', 'fleetParents', 'message'));
        } catch (Exception $e) {
            return back()->with('danger', 'لطفا تمامی موارد را پر کنید');

        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return 'adawd';
        $fleet = Fleet::where('id', $id)->first();

        Fleet::where('id', $id)
            ->orwhere('parent_id', $id)
            ->delete();

        try {
            if (file_exists($fleet->pic) && $fleet->pic != 'pictures/fleets/user.png')
                unlink($fleet->pic);
        } catch (Exception $exception) {
        }


        return back()->with('success', 'ناوگان مورد نظر حذف شد');
        return $this->index('ناوگان مورد نظر حذف شد');
    }

    // لیست ناوگان های اصلی
    public function requestMainFleetsList()
    {
        $fleetsParents = Fleet::where('parent_id', 0)->get();
        if (count($fleetsParents)) {
            return [
                'result' => SUCCESS,
                'fleets' => $fleetsParents
            ];
        }
        return [
            'result' => UN_SUCCESS,
            'message' => 'خطا! لطفا دوباره تلاش کنید'
        ];
    }

    // لیست ناوگان های فرعی
    public function requestSubFleetsList($parent_id)
    {
        $fleetsParents = Fleet::where('parent_id', $parent_id)->get();
        if (count($fleetsParents)) {
            return [
                'result' => SUCCESS,
                'fleets' => $fleetsParents
            ];
        }
        return [
            'result' => UN_SUCCESS,
            'message' => 'خطا! لطفا دوباره تلاش کنید'
        ];
    }

    // لیست ناوگان های فرعی
    public function requestAllSubFleetsList()
    {
        $fleetsParents = Fleet::where('parent_id', '>', 0)->get();
        if (count($fleetsParents)) {
            return [
                'result' => SUCCESS,
                'fleets' => $fleetsParents
            ];
        }
        return [
            'result' => UN_SUCCESS,
            'message' => 'خطا! لطفا دوباره تلاش کنید'
        ];
    }

    // ذخیره عکس
    private function savePicOfFleet($picture)
    {
        $picName = 'user.png';
        if (strlen($picture)) {
            $fileType = $picture->guessClientExtension();
            if ($picture->isValid() && ($fileType == 'jpg' || $fileType == 'jpeg' || $fileType == 'gif' || $fileType == 'png' || $fileType == 'bmp')) {
                $picName = sha1(time()) . "." . $fileType;
                $picture->move('pictures/fleets', $picName);
            }
        }

        return 'pictures/fleets/' . $picName;
    }

    // درخواست لیست ناوگان ها
    public function requestAllFleetsList()
    {
        return [
            'fleets' => Fleet::where('status', 1)->where('parent_id' , '!=', 0)
                ->select('id', 'title', 'parent_id', 'pic', 'length', 'width', 'height', 'capacity')
                ->get()
        ];
    }

    // درخواست لیست ناوگان ها
    public function requestAllFleetsLists()
    {
        return [
            'fleets' => Fleet::where('status', 1)
                ->select('id', 'title', 'parent_id')
                ->get()
        ];
    }

    // درخواست تغییر ناوگان
    public function changeFleet(Request $request)
    {
        $checkDriver = Driver::where('id', $request->driver_id)->count();
        $checkFleet = Fleet::where([
            ['id', $request->fleet_id],
            ['parent_id', '>', 0]
        ])->count();
        if ($checkDriver > 0 && $checkFleet > 0) {

            Driver::where('id', $request->driver_id)
                ->update([
                    'fleet_id' => $request->fleet_id
                ]);

            return [
                'result' => SUCCESS
            ];
        }

        return [
            'result' => UN_SUCCESS,
            'message' => 'عدم ارسال اطلاعات معتبر'
        ];
    }

    /*******************************************************************************************/
    // لیست ناوگان انتخابی اپراتور ها
    public function operatorFleets()
    {
        $fleets = Fleet::where('parent_id', '>', 0)->select('id', 'title')->get();
        $myFleets = FleetOperator::where('operator_id', auth()->id())->pluck('fleet_id');
        return view('admin.operatorFleets', compact('fleets', 'myFleets'));
    }

    public function updateOperatorFleets(Request $request)
    {

        FleetOperator::where('operator_id', auth()->id())->delete();

        /*$data = [];
        foreach ($request->fleets as $fleet) {
            $data [] = [
                'operator_id' => auth()->id(),
                'fleet_id' => $fleet
            ];
        }
        FleetOperator::create($data);*/
        if (isset($request->fleets)){
            foreach ($request->fleets as $fleet) {
                $myFleets = new FleetOperator();
                $myFleets->operator_id = auth()->id();
                $myFleets->fleet_id = $fleet;
                $myFleets->save();
            }
        }
        return back()->with('success', 'ناوگان مورد نظر ذخیره شد');
    }
}
