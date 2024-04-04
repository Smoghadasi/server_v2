<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Vacation;
use Illuminate\Http\Request;

class VacationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $vacations = Vacation::with('user')
            ->orderByDesc('created_at')
            ->where('type', VACATION_DAY)
            ->paginate('10');
        $users = User::all();
        return view('admin.vacation.index', compact('vacations', 'users'));
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
        $vacation = new Vacation();
        $vacation->user_id = $request->user_id;
        $vacation->fromDate = $request->fromDate;
        $vacation->toDate = $request->toDate;
        $vacation->type = VACATION_DAY;
        $vacation->description = $request->description;
        $vacation->save();
        return back()->with('success', 'مرخصی ثبت شد.');
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
    public function update(Request $request, Vacation $vacation)
    {
        $vacation->user_id = $request->user_id;
        $vacation->fromDate = $request->fromDate;
        $vacation->toDate = $request->toDate;
        $vacation->type = VACATION_DAY;
        $vacation->description = $request->description;
        $vacation->save();
        return back()->with('success', 'مرخصی ویرایش شد.');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Vacation $vacation)
    {
        $vacation->delete();
        return back()->with('danger', 'مرخصی حذف شد.');

    }

    public function vacationDay($user_id)
    {
        $vacations = Vacation::with('user')
            ->orderByDesc('created_at')
            ->where('type', VACATION_DAY)
            ->where('user_id', $user_id)
            ->paginate('10');
        return view('admin.vacation.vacationDay', compact('vacations'));
    }
}
