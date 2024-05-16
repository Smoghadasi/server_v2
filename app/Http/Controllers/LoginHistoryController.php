<?php

namespace App\Http\Controllers;

use App\Models\LoginHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginHistoryController extends Controller
{
    public function index()
    {
        $loginHistories = LoginHistory::with('user')
            ->where('user_id', Auth::user()->id)
            ->orderByDesc('created_at')
            ->paginate(15);
        return view('admin.private.history.index', compact('loginHistories'));
    }
}
