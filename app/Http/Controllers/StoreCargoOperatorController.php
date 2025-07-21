<?php

namespace App\Http\Controllers;

use App\Models\StoreCargoOperator;
use App\Models\User;
use Illuminate\Http\Request;

class StoreCargoOperatorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $storeCargoOperatorsIds = StoreCargoOperator::select('user_id')->pluck('user_id')->toArray();

        $users = User::whereIn('id', $storeCargoOperatorsIds)
            ->with('storeCargoOperators')
            ->get();

        $today = gregorianDateToPersian(date('Y/m/d', time()), '/');


        foreach ($users as $user) {
            $all = $user->storeCargoOperators;

            $user->total_count = $all->sum('count');
            $user->today_count = $all->where('persian_date', $today)->sum('count');
        }
        return view('admin.load.storeCargoOperators', compact('users'));
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
        //
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
    public function update(Request $request, $id)
    {
        //
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
