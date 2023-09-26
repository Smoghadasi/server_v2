<?php

namespace App\Http\Controllers;

use App\Models\Bearing;
use App\Models\Load;
use App\Models\Transaction;
use Illuminate\Http\Request;

class AccountingController extends Controller
{
    //
    public function accounting()
    {
        $transactions = (Transaction::where('status', 1)->sum('amount')/10);
        $bearingsInitialCharges = (Bearing::count() * 100000);
        $bearingsCurrentCharges = Bearing::where('status', 1)->sum('wallet');
        $loadPrice = (Load::where('status', '>=', 4)->sum('price') / 100) * PERCENT;

        return view('admin.accounting', compact('transactions', 'bearingsInitialCharges', 'bearingsCurrentCharges', 'loadPrice'));
    }
}
