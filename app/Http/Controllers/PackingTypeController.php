<?php

namespace App\Http\Controllers;

use App\Models\PackingType;
use function GuzzleHttp\Psr7\copy_to_stream;
use Illuminate\Http\Request;

class PackingTypeController extends Controller
{
    //لیست بسته بندی ها
    public function requestPackingTypes()
    {
        return [
            'packingTypes' => PackingType::select('id', 'title', 'pic')->get()
        ];
    }

    // صفحه نوع بسته بندی ها
    public function packingType($message = '')
    {
        $packingTypes = PackingType::select('id', 'title', 'pic')->get();
        return view('admin/packingTypes', compact('packingTypes', 'message'));
    }

    // فرم افزودن نوع بسته بندی جدید
    public function addNewPackingTypeForm($message = '')
    {
        return view('admin/addNewPackingTypeForm', compact('message'));
    }

    // افزودن نوع بسته بندی جدید
    public function addNewPackingType(Request $request)
    {
        $rules = [
            'title' => 'required'
        ];

        $message = [
            'require' => 'لطفا عنوان نوع بسته بندی را وارد نمایید'
        ];

        $this->validate($request, $rules, $message);

        $pic = $request->file('pic');

        $packingType = new PackingType();
        $packingType->title = $request->title;
        $packingType->pic = $this->savePicOfPackingType($pic);
        $packingType->save();

        return $this->addNewPackingTypeForm('نوع بسته بندی جدید افزوده شد');
    }

    // فرم ویرایش نوع بسته بندی
    public function editPackingTypeForm($id, $message = '')
    {
        $packingType = PackingType::where('id', $id)->first();

        return view('admin/editPackingTypeForm', compact('packingType', 'message'));
    }

    // ویرایش بسته بندی
    public function editPackingType(Request $request)
    {
        $rules = [
            'title' => 'required'
        ];

        $message = [
            'require' => 'لطفا عنوان نوع بسته بندی را وارد نمایید'
        ];

        $this->validate($request, $rules, $message);

        $pic = $request->file('pic');

        if ($pic) {
            $packingType = PackingType::where('id', $request->id)->first();
            if ($packingType)
                @unlink($packingType->pic);
        }

        PackingType::where('id', $request->id)
            ->update([
                'title' => $request->title,
                'pic' => $this->savePicOfPackingType($pic)
            ]);


        return $this->editPackingTypeForm($request->id, 'ویرایش انجام شد');
    }
    // حذف نوع بسته بندی
    public function deletePackingType($id)
    {

        $packingType = PackingType::where('id', $id)->first();
        if ($packingType)
            @unlink($packingType->pic);

        PackingType::where('id', $id)->delete();

        return $this->packingType('نوع بسته بندی مورد نظر حذف شد');
    }

    // ذخیره عکس
    private function savePicOfPackingType($picture)
    {
        $picName = 'user.png';
        if (strlen($picture)) {
            $fileType = $picture->guessClientExtension();
            if ($picture->isValid() && ($fileType == 'jpg' || $fileType == 'jpeg' || $fileType == 'gif' || $fileType == 'png' || $fileType == 'bmp')) {
                $picName = sha1(time()) . "." . $fileType;
                $picture->move('pictures/packingTypes', $picName);
            }
        }
        return 'pictures/packingTypes/' . $picName;
    }
}
