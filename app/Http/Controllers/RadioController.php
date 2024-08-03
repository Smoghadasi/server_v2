<?php

namespace App\Http\Controllers;

use App\Models\Radio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class RadioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $radios = Radio::paginate(20);
        return view('admin.radio.index', compact('radios'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.radio.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $radio = new Radio();
        if ($request->hasfile('cover')) {
            $photo = $request->file('cover');
            $extenstion = $photo->getClientOriginalExtension();
            $photoname = time() . '.' . $extenstion;
            $photo->move('radio/cover', $photoname);
            $radio->cover = 'radio/cover/' . $photoname;
        }

        if ($request->file('source')) {
            $photo = $request->file('source');
            $extenstion = $photo->getClientOriginalExtension();
            $photoname = time() . '.' . $extenstion;
            $photo->move('radio/source', $photoname);
            $radio->source = 'radio/source/' . $photoname;
        }
        $radio->name = $request->input('name');
        $radio->artist = $request->input('artist');
        $radio->status = $request->input('status');
        $radio->save();
        return redirect()->route('radio.index')->with('success', 'رادیو جدید ثبت شد');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Radio $radio)
    {
        return view('admin.radio.show', compact('radio'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Radio $radio)
    {
        return view('admin.radio.edit', compact('radio'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Radio $radio)
    {
        if ($request->hasfile('cover')) {
            if (File::exists($radio->cover)) {
                File::delete($radio->cover);
            }
            $photo = $request->file('cover');
            $extenstion = $photo->getClientOriginalExtension();
            $photoname = time() . '.' . $extenstion;
            $photo->move('radio/cover', $photoname);
            $radio->cover = 'radio/cover/' . $photoname;
        }

        if ($request->file('source')) {
            if (File::exists($radio->source))
                File::delete($radio->source);
            $photo = $request->file('source');
            $extenstion = $photo->getClientOriginalExtension();
            $photoname = time() . '.' . $extenstion;
            $photo->move('radio/source', $photoname);
            $radio->source = 'radio/source/' . $photoname;
        }
        $radio->name = $request->input('name');
        $radio->artist = $request->input('artist');
        $radio->status = $request->input('status');
        $radio->save();
        return redirect()->route('radio.index')->with('success', 'رادیو جدید ثبت شد');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
