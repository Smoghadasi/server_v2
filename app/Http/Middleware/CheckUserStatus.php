<?php

namespace App\Http\Middleware;

use App\Models\Bearing;
use App\Models\Customer;
use Closure;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(!auth('customer')->check() && !auth('bearing')->check())
            return redirect(url('user'));

        if (auth('bearing')->check()) {

            $bearing = Bearing::where('id', auth('bearing')->id())
                ->select('status')
                ->first();
            if ($bearing->status == 0)
                return redirect(url('user/status'));

        } else if (auth('customer')->check()) {

            $customer = Customer::where('id', auth('customer')->id())
                ->select('status')
                ->first();

            if ($customer->status == 0)
                return redirect(url('user/status'));
        }


        return $next($request);
    }
}
