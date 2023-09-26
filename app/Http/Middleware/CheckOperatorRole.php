<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\UserActivityReport;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use PHPUnit\Exception;


class CheckOperatorRole
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {

            try {

                $expiresAt = now()->addMinutes(5);

                Cache::put('user-is-online-' . \auth()->user()->id, true, $expiresAt);

                User::where('id', \auth()->user()->id)->update(['last_seen' => now()]);


            } catch (Exception $e) {
                Log::emergency("-------------------------- UserActivityReport ----------------------------------------");
                Log::emergency($e->getMessage());
                Log::emergency("------------------------------------------------------------------");
            }

            if ((\auth()->user()->role != ROLE_OPERATOR && \auth()->user()->role != ROLE_ADMIN) || \auth()->user()->status == DE_ACTIVE)
                return response()->view('errors/404');
        } else
            return redirect('dashboard');

        return $next($request);
    }
}
