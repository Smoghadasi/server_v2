<?php

namespace App\Http\Controllers;

use App\Models\PrompAi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrompAiController extends Controller
{
    public function store()
    {
        $today = Carbon::today();

        $alreadyExists = PrompAi::where('user_id', Auth::id())
            ->whereDate('created_at', $today)
            ->exists();

        if (!$alreadyExists) {
            $promp = new PrompAi();
            $promp->user_id = Auth::id();
            $promp->save();
            return true;
        }
        return false;
    }
}
