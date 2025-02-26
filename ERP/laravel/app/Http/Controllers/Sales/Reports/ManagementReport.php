<?php

namespace App\Http\Controllers\Sales\Reports;

use App\Http\Controllers\Controller;
use App\Permissions;
use Illuminate\Http\Request;

class ManagementReport extends Controller {
    public function __invoke(Request $request)
    {
        abort_unless($request->user()->hasAnyPermission(Permissions::SA_MGMTREP), 403);

        return view('reports.managementReport.base');
    }
}