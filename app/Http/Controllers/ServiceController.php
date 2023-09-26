<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $services = Service::paginate(20);
        return view('admin.service.index', compact('services'));
    }

    /**
     * Get list of servives from app
     * @return mixed
     */
    public function apiIndex()
    {
        try {
            return \response()->json([
                'result' => true,
                'data' => [
                    'services' => Service::select('title', 'link', 'icon')->get()
                ]
            ]);
        } catch (Exception $e) {
            Log::emergency($e->getMessage());
            return \response()->json([
                'result' => false,
                'message' => 'لطفا دوباره تلاش کنید'
            ]);
        }
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $rules = [
            'title' => 'required',
            'link' => 'required',
            'icon' => 'required|mimes:png,jpg,jpeg|max:2048'
        ];
        $messages = [
            'title' => 'عنوان خدمت را وارد کنید.',
            'link' => 'لینک خدمت را وارد کنید.',
            'icon' => 'فرمت آیکن معتبر نیست.'
        ];

        $this->validate($request, $rules, $messages);

        $service = new Service();
        $service->title = $request->title;
        $service->link = $request->link;
        $service->icon = $this->storeIcon($request->file('icon'));
        $service->save();

        if (isset($service->id))
            return back()->with('success', 'خدمت مورد نظر ثبت شد.');

        return back()->with('danger', 'خدمت مورد نظر ثبت نشد، دوباره تلاش کنید.');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Service $service)
    {

        $rules = [
            'title' => 'required',
            'link' => 'required',
            'icon' => 'required|mimes:png,jpg,jpeg|max:2048'
        ];
        $messages = [
            'title' => 'عنوان خدمت را وارد کنید.',
            'link' => 'لینک خدمت را وارد کنید.',
            'icon' => 'فرمت آیکن معتبر نیست.'
        ];

        $this->validate($request, $rules, $messages);

        $service->title = $request->title;
        $service->link = $request->link;
        if ($request->file('icon') != 'null') {
            if (unlink($service->icon))
                $service->icon = $this->storeIcon($request->file('icon'));
        }
        $service->save();

        if (isset($service->id))
            return back()->with('success', 'خدمت مورد نظر بروز رسانی شد.');

        return back()->with('danger', 'خدمت مورد نظر بروز رسانی نشد، دوباره تلاش کنید.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Service $service)
    {
        $service->delete();
        unlink($service->icon);
        return back()->with('success', 'خدمت مورد نظر حذف شد.');
    }

    /**
     * Upload the service icon
     *
     * @param string $icon
     * @return  string
     */
    private function storeIcon($icon)
    {
        $iconName = 'user.png';
        if (strlen($icon)) {
            $fileType = $icon->guessClientExtension();
            if ($icon->isValid() && ($fileType == 'jpg' || $fileType == 'jpeg' || $fileType == 'gif' || $fileType == 'png' || $fileType == 'bmp')) {
                $iconName = sha1(time()) . "." . $fileType;
                $icon->move('pictures/services', $iconName);
            }
        }
        return 'pictures/services/' . $iconName;
    }
}
