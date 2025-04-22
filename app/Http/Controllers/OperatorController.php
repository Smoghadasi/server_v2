<?php

namespace App\Http\Controllers;

use App\Models\Fleet;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\User;
use Illuminate\Http\Request;

class OperatorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::all();
        $fleets = Fleet::where('parent_id', '>', 0)->get();
        return view('admin.operator.index', compact('users', 'fleets'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = Role::all();
        return view('admin.operator.create', compact('roles'));
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
            'name' => 'required|min:2|max:16',
            'lastName' => 'required|min:2|max:16',
            'nationalCode' => 'required|min:10|max:10|unique:users',
            'email' => 'required|unique:users',
            'mobileNumber' => 'required|min:11|max:11|unique:users',
            'role' => 'required',
            'password' => 'required|min:6|max:16|same:password_confirmation',
            'password_confirmation' => 'required|min:6|max:16',
        ];
        $message = [
            'required' => 'این فیلد الزمامی می باشد',
            'name.min' => 'حداقل طول نام باید 2 کاراکتر باشد',
            'name.max' => 'حداکثر طول نام باید 16 کاراکتر باشد',

            'lastName.min' => 'حداقل طول نام خانوادگی باید 2 کاراکتر باشد',
            'lastName.max' => 'حداکثر طول نام خانوادگی باید 16 کاراکتر باشد',

            'nationalCode.min' => 'کد ملی باید 10 رقم باشد',
            'nationalCode.max' => 'کد ملی باید 10 رقم باشد',
            'nationalCode.unique' => 'کد ملی تکراری می باشد',

            'mobileNumber.min' => 'شماره موبایل باید 11 رقم باشد',
            'mobileNumber.max' => 'شماره موبایل باید 11 رقم باشد',
            'mobileNumber.unique' => 'شماره موبایل تکراری می باشد',

            'role.min' => 'نوع کاربر می بایست انتخاب شود',
            'role.max' => 'نوع کاربر می بایست انتخاب شود',

            'password.min' => 'رمز ورود باید حداقل 6 کاراکتر باشد',
            'password.max' => 'رمز ورود باید حداکثر 16 کاراکتر باشد',

            'password_confirmation.min' => 'تکرار رمز ورود باید حداقل 6 کاراکتر باشد',
            'password_confirmation.max' => 'تکرار رمز ورود باید حداکثر 16 کاراکتر باشد',
            'password.same' => 'رمز ورود و تکرار رمز ورود باهم برابر نیستند',

            'email.unique' => 'ایمیل تکراری می باشد',
            'email.required' => 'ایمیل الزامی می باشد'
        ];
        $this->validate($request, $rules, $message);

        try {

            $role = Role::find($request->role);

            $pic = $request->file('pic');

            // ذخیره کاربر
            $user = new User();
            $user->name = $request->name;
            $user->lastName = $request->lastName;
            $user->pic = $this->savePicOfUsers($pic);
            $user->nationalCode = $request->nationalCode;
            $user->mobileNumber = ParameterController::convertNumbers($request->mobileNumber);
            $user->role = $role->role;
            $user->email = $request->email;
            $user->password = bcrypt($request->password);
            $user->save();

            // ذخیره نقش کاربر
            $role_user = new RoleUser();
            $role_user->user_id = $user->id;
            $role_user->role_id = $request->role;
            $role_user->save();

            $message = 'اپراتور جدید ذخیره شد';
            $roles = Role::all();

            return view('admin.operator.create', compact('message', 'roles'));
        } catch (\Exception $exception) {
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);
        return view('admin.operator.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::find($id);
        return view('admin.operator.edit', compact('user'));
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
            $birthdate = persianDateToGregorian(str_replace('/', '-', $request->birthdate), '-') . ' 00:00:00';

            $user = User::find($id);
            if ($request->file('degree')) {
                $file = $request->file('degree');
                $extenstion = $file->getClientOriginalExtension();
                $filename = time() . '.' . $extenstion;
                if (file_exists($user->degree))
                    unlink($user->degree);
                $file->move('pictures/users', $filename);
                $user->degree = 'pictures/users/' . $filename;
            }

            $user->name = $request->name;
            $user->lastName = $request->lastName;
            $user->fatherName = $request->fatherName;
            $user->birthdate = $birthdate;
            $user->education = $request->education;
            $user->email = $request->email;
            $user->nationalCode = $request->nationalCode;
            $user->mobileNumber = ParameterController::convertNumbers($request->mobileNumber);
            $user->role = $request->role;
            $user->save();
            return back()->with('success', 'اپراتور مورد نظر ویرایش شد');
        } catch (\Exception $e) {
            return $e;
        }
    }

    private function savePicOfUsers($picture)
    {
        $picName = 'user.png';
        if (strlen($picture)) {
            $fileType = $picture->guessClientExtension();
            if ($picture->isValid() && ($fileType == 'jpg' || $fileType == 'jpeg' || $fileType == 'gif' || $fileType == 'png' || $fileType == 'bmp')) {
                $picName = sha1(time()) . "." . $fileType;
                $picture->move('pictures/users', $picName);
            }
        }
        return $picName;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user->delete();

        return back()->with('success', 'اپراتور مورد نظر حذف شد');
    }

    // تغییر وضعیت اپراتور
    public function changeOperatorStatus(User $user)
    {
        if ($user->status == ACTIVE)
            $user->status = DE_ACTIVE;
        else
            $user->status = ACTIVE;

        $user->save();

        return back()->with('success', 'تغییر وضعیت اپراتور انجام شد');
    }
}
