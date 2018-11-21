<?php

namespace App\Http\Middleware;

use Closure;
use Session;
use Lang;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

use App\Helpers\CustomerCache;
use App\User;

class Locale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            if (!Session::has('customer_setup_config')) {
                abort(403, 'You do not have permission to access this page');
            }
            else{
                if (!Session::has('locale')) {
                    Session::put('locale', config('app.locale'));
                }
                Lang::setLocale(Session::get('locale'));

                $CustomerCache = new CustomerCache();
                $CustomerCache->initCustomerVariables();
                return $next($request);
            }
        }
        else
            abort(403, 'You do not have permission to access this page');
    }
}
