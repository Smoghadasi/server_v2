<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\FreeSubscription;
use Illuminate\Http\Request;

class FreeSubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $freeSubscriptions = FreeSubscription::with('driver', 'operator')
            ->orderByDesc('created_at')
            ->where('value', '!=', 0)
            ->whereIn('type', ['AuthCalls', 'AuthValidity'])
            ->paginate(20);
        $authCallToDay = FreeSubscription::where('created_at', '>', date('Y-m-d', time()) . ' 00:00:00')
            ->where('type', 'AuthCalls')
            ->get();
        $freeCallCount = $authCallToDay->sum('value');
        return view('admin.freeSubscription.index', compact('freeSubscriptions', 'freeCallCount'));
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
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FreeSubscription $freeSubscription)
    {
        return $freeSubscription;
        return view('admin.freeSubscription', compact('freeSubscriptions'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function search(Request $request)
    {
        $freeSubscriptions = FreeSubscription::with('driver', 'operator')
            ->whereHas('driver', function ($q) use ($request) {
                $q->where('mobileNumber', $request->mobileNumber);
            })
            ->orderByDesc('created_at')
            ->where('value', '!=', 0)
            ->whereIn('type', ['AuthCalls', 'AuthValidity'])
            ->get();
        $authCallToDay = FreeSubscription::where('created_at', '>', date('Y-m-d', time()) . ' 00:00:00')
            ->where('type', 'AuthCalls')
            ->get();
        $freeCallCount = $authCallToDay->sum('value');
        return view('admin.freeSubscription.search', compact('freeSubscriptions', 'freeCallCount'));
    }
}
