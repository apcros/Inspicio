<?php

namespace App\Http\Middleware;

use Closure;

class IsLoggedIn
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
        $session = $request->session();

        if($session->has('user_nickname') && $session->has('user_id') && $session->has('user_email')) {
                return $next($request);
        }

        return redirect('choose-auth');
        
    }
}
