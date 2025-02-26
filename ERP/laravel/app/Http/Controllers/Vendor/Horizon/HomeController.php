<?php

namespace App\Http\Controllers\Vendor\Horizon;

use Laravel\Horizon\Http\Controllers\Controller;
use Laravel\Horizon\Horizon;

class HomeController extends Controller
{
    /**
     * Single page application catch-all route.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $scriptVariables = array_merge(
            Horizon::scriptVariables(),
            [
                'path' => ltrim(request()->getBasePath().'/'.config('horizon.path'), '/')
            ]
            );

        return view('horizon::layout', [
            'cssFile' => Horizon::$useDarkTheme ? 'app-dark.css' : 'app.css',
            'horizonScriptVariables' => $scriptVariables,
        ]);
    }
}
