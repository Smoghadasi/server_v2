<?php

namespace App\Http\Controllers;

use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class SliderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sliders = Slider::paginate(10);
        return view('admin.slider.index', compact('sliders'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.slider.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $slider = new Slider();
        if ($request->hasfile('file_url')) {
            $photo = $request->file('file_url');
            $extenstion = $photo->getClientOriginalExtension();
            $photoname = time() . '.' . $extenstion;
            $photo->move('slider', $photoname);
            $slider->file_url = 'slider/' . $photoname;
        }
        $slider->name = $request->name;
        $slider->status = $request->status;
        $slider->save();
        return redirect()->route('slider.index')->with('success', 'اسلایدر جدید ثبت شد');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Slider $slider)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Slider $slider)
    {
        return view('admin.slider.edit', compact('slider'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Slider $slider)
    {
        if ($request->hasfile('file_url')) {
            if (File::exists($slider->slider)) {
                File::delete($slider->slider);
            }
            $photo = $request->file('file_url');
            $extenstion = $photo->getClientOriginalExtension();
            $photoname = time() . '.' . $extenstion;
            $photo->move('slider', $photoname);
            $slider->file_url = 'slider/' . $photoname;
        }
        $slider->name = $request->name;
        $slider->status = $request->status;
        $slider->save();
        return redirect()->route('slider.index')->with('success', 'اسلایدر جدید ویرایش شد');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Slider $slider)
    {
        $slider->delete();
        return redirect()->route('slider.index')->with('danger', 'اسلایدر جدید حذف شد');
    }
}
