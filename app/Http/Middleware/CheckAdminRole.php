<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Auth;

class CheckAdminRole
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
//            $id = Auth::id();
//            $role = User::find($id)->roles[0]->role;

            if (\auth()->user()->role != 'admin' || \auth()->user()->status == DE_ACTIVE)
                return response()->view('errors/404');
        } else
            return  redirect('dashboard');

//            return response()->view('errors/404');

        return $next($request);
    }
}
