<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use DateTime;
use Illuminate\Http\Request;

class DashboardController extends Controller {

    /**
     * Show the dashboard
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $dateFormat = getNativeDateFormat();
        $inputs = $request->validate([
            'date' => "nullable|date_format:{$dateFormat}"
        ]);
        $date = $inputs['date'] ?? date($dateFormat);
        $monthName = (new DateTime($date))->format('F');

        return view('system.dashboard.user', compact('user', 'date', 'monthName'));
    }
}