<?php

namespace App\Traits;

use DateTimeImmutable;
use Illuminate\Http\Request;

trait ValidatesDatedDashboardReport {
    /**
     * Validates the request that is only having a date and return the date
     */
    protected function validateRequestWithDate(Request $request)
    {
        $dateFormat = getNativeDateFormat();
        ["date" => $date] = $request->validate(['date' => "required|date_format:{$dateFormat}"]);

        return DateTimeImmutable::createFromFormat($dateFormat, $date);
    }
}