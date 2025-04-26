<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContractRequest;
use App\Models\Contract;
use App\Models\User;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = User::findOrFail($request->user_id);


        $contracts = Contract::withCount('contractCollaborations')
            ->where('user_id', $request->user_id)
            ->orderByDesc('created_at')
            ->paginate(40);
        // return $contracts;
        return view('admin.contract.index', compact('contracts', 'user'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $user = User::findOrFail($request->user_id);
        return view('admin.contract.create', compact('user'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreContractRequest $request)
    {
        $fromDate = persianDateToGregorian(str_replace('/', '-', $request->fromDate), '-') . ' 00:00:00';
        $toDate = persianDateToGregorian(str_replace('/', '-', $request->toDate), '-') . ' 00:00:00';

        Contract::create([
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'cardNumber' => $request->cardNumber,
            'shabaNumber' => $request->shabaNumber,
            'promissoryNote' => $request->promissoryNote,
            'user_id' => $request->user_id,
        ]);
        return redirect()->route('contract.index', ['user_id' => $request->user_id])->with('success', 'حقوق مورد نظر اضافه شد');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Contract $contract)
    {
        return view('admin.contract.show', compact('contract'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Contract $contract)
    {
        return view('admin.contract.edit', compact('contract'));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  object  $contract
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
{
    $fromDate = persianDateToGregorian(str_replace('/', '-', $request->fromDate), '-') . ' 00:00:00';
    $toDate = persianDateToGregorian(str_replace('/', '-', $request->toDate), '-') . ' 00:00:00';

    $contract = Contract::findOrFail($id);
    $contract->update([
        'fromDate' => $fromDate,
        'toDate' => $toDate,
        'cardNumber' => $request->cardNumber,
        'shabaNumber' => $request->shabaNumber,
        'promissoryNote' => $request->promissoryNote,
    ]);

    return redirect()->route('contract.index', ['user_id' => $request->user_id])->with('success', 'حقوق مورد نظر اضافه شد');
}


    /**
     * Remove the specified resource from storage.
     *
     * @param  object  $contract
     * @return \Illuminate\Http\Response
     */
    public function destroy(Contract $contract)
    {
        $contract->delete();
        return redirect()->route('contract.index', ['user_id' => $contract->user_id])->with('success', 'قرارداد مورد نظر اضافه شد');
    }
}
