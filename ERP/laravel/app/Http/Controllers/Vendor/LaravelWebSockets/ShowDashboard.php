<?php

namespace App\Http\Controllers\Vendor\LaravelWebSockets;

use Illuminate\Http\Request;
use BeyondCode\LaravelWebSockets\Apps\AppProvider;

class ShowDashboard
{
    public function __invoke(Request $request, AppProvider $apps)
    {
        return view('vendor.websockets.dashboard', [
            'apps' => $apps->all(),
            'port' => config('websockets.dashboard.port', 6001),
        ]);
    }
}
