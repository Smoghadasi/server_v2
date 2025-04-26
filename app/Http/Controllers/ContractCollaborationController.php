<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\ContractCollaboration;
use Google\Service\CloudSearch\Collaboration;
use Illuminate\Http\Request;

class ContractCollaborationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $contract = Contract::findorFail($request->contract_id);
        $collaborations = ContractCollaboration::with('contract.user')
            ->where('contract_id', $request->contract_id)
            ->paginate(40);
        return view('admin.collaboration.index', compact('collaborations', 'contract'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $contract = Contract::findorFail($request->contract_id);

        return view('admin.collaboration.create', compact('contract'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $fromDate = persianDateToGregorian(str_replace('/', '-', $request->fromDate), '-') . ' 00:00:00';
        $toDate = persianDateToGregorian(str_replace('/', '-', $request->toDate), '-') . ' 00:00:00';

        $collaboration = ContractCollaboration::create([
            'contractNumber' => $request->contractNumber,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'contractType' => $request->contractType,
            'isInsurance' => $request->isInsurance,
            'contract_id' => $request->contract_id,
        ]);
        if ($request->file('contract_file')) {
            $file = $request->file('contract_file');
            $extenstion = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extenstion;
            $file->move('pictures/collaborations', $filename);
            $collaboration->contract_file = 'pictures/collaborations/' . $filename;
            $collaboration->save();
        }
        return redirect()
            ->route('collaboration.index', ['contract_id' => $collaboration->contract_id])
            ->with('success', 'قرارداد مورد نظر اضافه شد');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(ContractCollaboration $collaboration)
    {
        return view('admin.collaboration.show', compact('collaboration'));
        // return $collaboration;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(ContractCollaboration $collaboration)
    {
        return view('admin.collaboration.edit', compact('collaboration'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ContractCollaboration $collaboration)
    {
        $fromDate = persianDateToGregorian(str_replace('/', '-', $request->fromDate), '-') . ' 00:00:00';
        $toDate = persianDateToGregorian(str_replace('/', '-', $request->toDate), '-') . ' 00:00:00';

        $collaboration->update([
            'contractNumber' => $request->contractNumber,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'contractType' => $request->contractType,
            'isInsurance' => $request->isInsurance,
        ]);

        if ($request->file('contract_file')) {
            $file = $request->file('contract_file');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move('pictures/collaborations', $filename);
            $collaboration->contract_file = 'pictures/collaborations/' . $filename;
        }
        $collaboration->save();

        return redirect()
            ->route('collaboration.index', ['contract_id' => $collaboration->contract_id])
            ->with('success', 'قرارداد مورد نظر اضافه شد');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(ContractCollaboration $collaboration)
    {
        $collaboration->delete();
        return redirect()
            ->route('collaboration.index', ['contract_id' => $collaboration->contract_id])
            ->with('success', 'قرارداد مورد نظر اضافه شد');
    }
}
