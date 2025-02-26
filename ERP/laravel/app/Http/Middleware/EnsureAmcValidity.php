<?php

namespace App\Http\Middleware;

use Closure;

class EnsureAmcValidity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     * @throws \App\Exceptions\AmcExpiredException
     */
    public function handle($request, Closure $next)
    {
        app(\App\Amc::class)->enforceValidity();

        return $next($request);
    }
}