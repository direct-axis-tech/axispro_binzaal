<?php

use App\Http\Controllers\Hr\PayslipController;

class PayslipHelpers {

     /** 
     * Returns the validated user inputs or the default value.
     * 
     * @param $currentEmployee The employee defined for the current user
     * @return array
     */
    public static function getValidatedInputs($currentEmployee) {
        // defaults
        $filters = [
            "payroll_id"    => null,
            "department_id" => $currentEmployee['department_id'] ?? null,
            "employee_id"   => null,
            "working_company_id" => $currentEmployee['working_company_id'] ?? null
        ];

        if (
            isset($_POST['department_id'])
            && preg_match('/^[1-9][0-9]{0,15}$/', $_POST['department_id']) === 1
        ) {
            $filters['department_id'] = $_POST['department_id'];
        }
        
        if (
            isset($_POST['employee_id'])
            && preg_match('/^[1-9][0-9]{0,15}$/', $_POST['employee_id']) === 1
        ) {
            $filters['employee_id'] = $_POST['employee_id'];
        }

        if (
            isset($_POST['payroll_id'])
            && preg_match('/^[1-9][0-9]{0,15}$/', $_POST['payroll_id']) === 1
            && !empty(getPayroll($_POST['payroll_id']))
        ) {
            $filters['payroll_id'] = $_POST['payroll_id'];
        }


        if (
            isset($_POST['working_company_id'])
            && preg_match('/^[1-9][0-9]{0,15}$/', $_POST['working_company_id']) === 1
        ) {
            $filters['working_company_id'] = $_POST['working_company_id'];
        }

        return $filters;
    }

    public static function handlePrintPayslipRequest($renderedHtml, $payrollId, $employeeId) {
        try {
            
            exit();
        } catch (Exception $e) {
            return display_error("Error occurred while preparing PDF");
        }
    }
}