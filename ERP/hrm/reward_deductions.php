<?php

use App\Http\Controllers\Hr\EmployeeRewardsDeductionsController;
use App\Models\Hr\EmployeeRewardsDeductions;

$path_to_root = '..';
require_once __DIR__ . "/../includes/session.inc";
require_once __DIR__ . "/../API/AxisPro.php";

config()->set('database.default', 'fa');

$request = request();
$controller = app(EmployeeRewardsDeductionsController::class);

begin_transaction();

if ($request->_method === 'DELETE' && $request->empRewardDeduction) {
    $empRewardDeduction = EmployeeRewardsDeductions::find($request->empRewardDeduction);
    if (!$empRewardDeduction) {
        return response()->json(['status' => 404, 'msg' => 'Record not found'], 404)->send();
    }

    $response = $controller->destroy($empRewardDeduction);
}

elseif ($request->reward_deduction_id) {
    $empRewardDeduction = EmployeeRewardsDeductions::find($request->reward_deduction_id);
    $response = $controller->update($request, $empRewardDeduction);
}

else {
    $response = $controller->store($request);
}

commit_transaction();

$response->send();

?>