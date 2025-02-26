<?php

use App\Models\Hr\Company;
use App\Models\Hr\EmployeePensionConfig;

class AddEmpJobUpdateHelper {

    /**
     * Handle the add employee job update request
     *
     * @return void
     */
    public static function handleAddEmpJobUpdateRequest() {
        if (!user_check_access('HRM_JOB_UPDATE')) {
            echo json_encode([
                "status" => 403,
                "message" => "You are not allowed to access this function"
            ]);
            exit();
        }

        $inputs = self::getValidInputsForAddingEmployeeJobUpdate();

        $lastDay = (new DateTimeImmutable($inputs['commence_from']))
            ->modify('-1 day')
            ->format(DB_DATE_FORMAT);
        $inputs['week_offs'] = json_encode($inputs['week_offs']);
        $inputs['supervisor_id'] = json_encode($inputs['supervisor_id'] ?? []);

        unsetCurrentJob($inputs['employee_id'], $lastDay);
        $insertedId = insertOneEmpJob($inputs);

        echo json_encode([
            "status" => 201,
            "message" => "Added employee job update",
            "data" => [
                "inserted_id" => $insertedId
            ]
        ]);
        exit();
    }

    /**
     * Retrieve all the request data that we need for adding employee's Job update after validating.
     *
     * Note: This function terminates request if the request contains invalid data
     * 
     * @return array
     */
    private static function getValidInputsForAddingEmployeeJobUpdate() {
        $errors = [];
        $inputs = $_POST;

        foreach ([
            'employee_id',
            'department_id',
            'designation_id',
            'commence_from',
            'week_offs',
            'work_hours',
            'working_company_id',
            'visa_company_id',
            'attendance_type'
        ] as $key) {
            if (empty($_POST[$key])) {
                $errors[$key] = "{$key} is required";
            }
        }

        if (!empty($errors)) {
            echo json_encode([
                "status" => 422,
                "message" => "Some of the required fields are missing",
                "errors" => $errors
            ]);
            exit();
        }

        if (
            !preg_match('/^\d{1,15}$/', $_POST['employee_id'])
            || empty(getEmployee($_POST['employee_id']))
        ) {
            $errors['employee_id'] = "Could not find the employee";
        }

        if (!Company::whereId($_POST['working_company_id'])->exists()) {
            $errors['working_company_id'] = "Could not find the company";
        }
        
        if (!Company::whereId($_POST['visa_company_id'])->exists()) {
            $errors['visa_company_id'] = "Could not find the company";
        }

        if (
            !preg_match('/^\d{1,15}$/', $_POST['department_id'])
            || empty(getDepartment($_POST['department_id']))
        ) {
            $errors['department_id'] = "Could not find the department";
        }

        if (
            !preg_match('/^\d{1,15}$/', $_POST['designation_id'])
            || empty(getDesignation($_POST['designation_id']))
        ) {
            $errors['designation_id'] = "Could not find the designation";
        }

        if (
            !($dt = DateTime::createFromFormat(getDateFormatInNativeFormat(), $_POST['commence_from']))
            || $dt->format(getDateFormatInNativeFormat()) != $_POST['commence_from']
        ) {
            $errors['commence_from'] = "This is not a valid date";
        } else {
            $inputs['commence_from'] = $dt->format(DB_DATE_FORMAT);
        }

        if (!is_array($_POST['week_offs'])) {
            $errors['week_offs'] = "This is not a valid list of days"; 
        } else {
            foreach ($_POST['week_offs'] as $day) {
                if (!in_array($day, ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"])) {
                    $errors['week_offs'][$day] = "This is not a valid day"; 
                }
            }
        }

        if (!is_numeric($_POST['work_hours'])) {
            $errors['work_hours'] = "This is a valid working hour";
        }

        if (!in_array($_POST['attendance_type'], array_keys(get_employee_attendance_types()))) {
            $errors['attendance_type'] = "This value is not a valid attendance type";
        }

        if (
            !empty($_POST['default_shift_id'])
            && (
                !preg_match('/^\d{1,15}$/', $_POST['default_shift_id'])
                || empty(getShift($_POST['default_shift_id']))
            )
        ) {
            $errors['default_shift_id'] = "This is not a valid shift";
        }
        
        if (
            !empty($_POST['supervisor_id'])
            && (
                !is_array($_POST['supervisor_id'])
                || in_array(
                    false,
                    array_map(
                        function ($supervisor) { return preg_match('/^\d{1,15}$/', $supervisor); },
                        $_POST['supervisor_id']
                    ),
                )
                || !empty(
                    array_diff(
                        array_keys(getEmployeesKeyedById(["employee_id" => $_POST['supervisor_id']])),
                        $_POST['supervisor_id']
                    )
                )
            )
        ) {
            $errors['supervisor_id'] = "This is not a valid supervisors list";
        }

        if(!empty($_POST['has_pension']) && (empty($_POST['pension_scheme']) || !EmployeePensionConfig::whereId($_POST['pension_scheme'])->exists())) {
            $errors['pension_scheme'] = "Pension scheme is required";
        }

        if (!empty($errors)) {
            echo json_encode([
                "status" => 422,
                "message" => "Request contains invalid data",
                "errors" => $errors
            ]);
            exit();
        }

        return $inputs;
    }
}