<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSalaryRequest;
use App\Models\Salary;
use App\Models\User;
use Illuminate\Http\Request;

class SalaryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = User::findOrFail($request->user_id);
        $salaries = Salary::with('user')
            ->where('user_id', $request->user_id)
            ->orderByDesc('created_at')
            ->paginate(40);
        return view('admin.salary.index', compact('salaries', 'user'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $user = User::findOrFail($request->user_id);
        return view('admin.salary.create', compact('user'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSalaryRequest $request)
    {
        $date = persianDateToGregorian(str_replace('/', '-', $request->date), '-') . ' 00:00:00';

        Salary::create([
            'price' => $request->price,
            'salary' => $request->salary,
            'salary_increase' => $request->salary_increase,
            'date' => $date,
            'description' => $request->description,
            'user_id' => $request->user_id,
        ]);
        return redirect()->route('salary.index', ['user_id' => $request->user_id])->with('success', 'حقوق مورد نظر اضافه شد');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($user_id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Salary $salary)
    {
        return view('admin.salary.edit', compact('salary'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Salary $salary)
    {
        $date = persianDateToGregorian(str_replace('/', '-', $request->date), '-') . ' 00:00:00';

        $salary->update([
            'price' => $request->price,
            'salary' => $request->salary,
            'salary_increase' => $request->salary_increase,
            'date' => $date,
            'description' => $request->description,
        ]);
        return redirect()->route('salary.index', ['user_id' => $salary->user_id])->with('success', 'حقوق مورد نظر اضافه شد');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Salary $salary)
    {
        $salary->delete();
        return redirect()->route('salary.index', ['user_id' => $salary->user_id])->with('success', 'حقوق مورد نظر اضافه شد');

    }
}
