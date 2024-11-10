<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use Illuminate\Http\Request;

class BankController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $banks = Bank::all();
        return view('admin.bank.index', compact('banks'));
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $bank = new Bank();
        $bank->name = $request->name;
        $bank->en_name = $request->en_name;
        $bank->icon = $this->storeIcon($request->file('icon'));
        $bank->status = $request->status;
        $bank->save();
        if (isset($bank->id))
            return back()->with('success', 'بانک  مورد نظر ثبت شد.');

        return back()->with('danger', 'بانک مورد نظر ثبت نشد، دوباره تلاش کنید.');
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
            if ($icon->isValid() && ($fileType == 'jpg' || $fileType == 'jpeg' || $fileType == 'gif' || $fileType == 'png' || $fileType == 'svg' || $fileType == 'bmp')) {
                $iconName = sha1(time()) . "." . $fileType;
                $icon->move('pictures/banks', $iconName);
            }
        }
        return 'pictures/banks/' . $iconName;
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
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Bank $bank)
    {
        // return dd($request->all());
        $rules = [
            'name' => 'required',
            'icon' => 'mimes:png,jpg,jpeg,svg|max:2048'
        ];
        $messages = [
            'name' => 'عنوان را وارد کنید.',
            'icon' => 'فرمت آیکن معتبر نیست.'
        ];
        $this->validate($request, $rules, $messages);

        $bank->name = $request->name;
        $bank->status = $request->status;
        $bank->en_name = $request->en_name;

        if($request->has('icon')){
            if (unlink($bank->icon))
                $bank->icon = $this->storeIcon($request->file('icon'));
        }
        $bank->save();

        if (isset($bank->id))
            return back()->with('success', 'بانک مورد نظر بروز رسانی شد.');

        return back()->with('danger', 'بانک مورد نظر بروز رسانی نشد، دوباره تلاش کنید.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Bank $bank)
    {
        $bank->delete();
        unlink($bank->icon);
        return back()->with('success', 'خدمت مورد نظر حذف شد.');
    }
}
