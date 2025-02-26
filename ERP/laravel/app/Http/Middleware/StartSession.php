<?php

namespace App\Http\Middleware;

use LaravelHelpers;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession as StartSessionMiddleware;

class StartSession extends StartSessionMiddleware {

    /**
     * Store the current URL for the request if necessary.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Contracts\Session\Session  $session
     * @return void
     */
    protected function storeCurrentUrl(Request $request, $session)
    {
        LaravelHelpers::storeCurrentUrl($request, $session, true);
    }
}