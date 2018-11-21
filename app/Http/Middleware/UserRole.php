<?php

namespace App\Http\Middleware;

use Closure;
use Session;

class UserRole
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
        if (!Session::has('customer_setup_config')) {
            abort(403, 'You do not have permission to access this page');
        }
        else
        {
            $customer_setup_config = session('customer_setup_config');
            if($customer_setup_config['userRole'] == 'admin')
                return $next($request);
            else
                abort(403, 'You do not have permission to access this page');
        }
    }
}
