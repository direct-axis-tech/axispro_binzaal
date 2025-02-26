<?php

use App\Models\EntityGroup;
use App\Models\EntityGroupCategory;

class EditEmployeeHelper {

    /**
     * Handle the add Employee Request
     *
     * @return void
     */
    public static function handleUpdateEmployeeRequest() {
        if (!user_check_access('HRM_ADD_EMPLOYEE')) {
            echo json_encode([
                "status" => 403,
                "message" => "You are not allowed to access this function"
            ]);
            exit();
        }

        [
            "emp" => $employee,
            "job" => $empJob
        ] = self::getValidInputsForUpdatingEmployee();

        $empJob['week_offs'] = json_encode($empJob['week_offs']);
        $empJob['supervisor_id'] = json_encode($empJob['supervisor_id'] ?? []);

        begin_transaction();
        $affectedRows = updateEmployee($employee['id'], $employee);
        $affectedRows += updateEmpJob($empJob['id'], $empJob);
        commit_transaction();

        echo json_encode([
            "status" => 204,
            "message" => "Employee updated successfully",
            "affected_rows" => $affectedRows
        ]);
        exit();
    }

    /**
     * Retrieve all the request data that we need for adding employee after validating.
     *
     * Note: This function terminates request if the request contains invalid data
     * 
     * @return array
     */
    private static function getValidInputsForUpdatingEmployee() {
        $errors = [];
        $nationalities = getCountriesKeyedByCode();
        $banks = getBanksKeyedById();
        $shifts = getShiftsKeyedById();
        $employees = getEmployeesKeyedById();
        
        $inputs = $_POST;

        foreach ([
            "emp" => [
                'id',
                'name',
                'preferred_name',
                'ar_name',
                'nationality',
                'gender',
                'date_of_birth',
                'email',
                'mobile_no',
                'mode_of_pay'
            ],
            "job" => [
                'id',
                'week_offs',
                'attendance_type'
            ]
        ] as $section => $keys) {
            if (!empty($_POST[$section])) {
                foreach ($keys as $key) {
                    if (empty($_POST[$section][$key])) {
                        $errors[$section][$key] = "{$key} is required";
                    }
                }
            } else {
                $errors[$section] = "{$section} section is required";
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

        $employee = getEmployee($_POST['emp']['id']);
        
        if (empty($employee)) {
            $errors['emp']['id'] = "The employee doesn't exists";
        }

        if ($employee['job_id'] != $_POST['job']['id']) {
            $errors['job']['id'] = "The employee job doesn't exists";
        }

        $pattern = [
            'alpha_num' => '/^[0-9a-zA-Z][0-9a-zA-Z]*$/',
            'spaced_alpha' => '/^[a-zA-Z][a-zA-Z ]*$/',
        ];

        foreach (['name', 'preferred_name'] as $key) {
            if (!preg_match($pattern['spaced_alpha'], $_POST['emp'][$key])) {
                $errors['emp'][$key] = "{$key} must only consists of alphabets and spaces";
            }
        }

        if (!isset($nationalities[$_POST['emp']['nationality']])) {
            $errors['emp']['nationality'] = "This is not a valid nationality";
        }

        if (!in_array($_POST['emp']['gender'], ['M', 'F'])) {
            $errors['emp']['gender'] = "This is not a valid gender";
        }

        if (
            !($dt = DateTime::createFromFormat(getDateFormatInNativeFormat(), $_POST['emp']['date_of_birth']))
            || $dt->format(getDateFormatInNativeFormat()) != $_POST['emp']['date_of_birth']
        ) {
            $errors['emp']['date_of_birth'] = "This is not a valid date";
        } else {
            $inputs['emp']['date_of_birth'] = $dt->format(DB_DATE_FORMAT);
        }

        if (!filter_var($_POST['emp']['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['emp']['email'] = "This is not a valid email address";
        }

        if (!preg_match(UAE_MOBILE_NO_PATTERN, $_POST['emp']['mobile_no'])) {
            $errors['emp']['mobile_no'] = "This is not a valid UAE mobile number";
        } else {
            $inputs['emp']['mobile_no'] = preg_replace(UAE_MOBILE_NO_PATTERN, "$2", $_POST['emp']['mobile_no']);
        }

//        if (
//            $_POST['emp']['nationality'] != $GLOBALS['SysPrefs']->prefs['home_country']
//            && empty($_POST['emp']['passport_no'])
//        ) {
//            $errors['emp']['passport_no'] = "The passport number should not be empty";
//        }
        
        foreach ([
            'passport_no' => ['passport number', $pattern['alpha_num']],
            'emirates_id' => ['emirates id number', '/^784-\d{4}-\d{7}-\d$/'],
            'file_no'   => ['file number', '_^[1-7]0[12]/\d{4}/\d/?\d+$_'],
            'labour_card_no' => ['labour card number', '/^\d+$/'],
            'uid no' => ['unified number (UID no.)', '/^\d+$/'],
        ] as $key => [$name, $pattern]) {
            if (!empty($_POST['emp'][$key]) && !preg_match($pattern, $_POST['emp'][$key])) {
                $errors['emp'][$key] = "This is not a valid {$name}";
            }
        }
    
        if (!in_array($_POST['emp']['mode_of_pay'], ['C', 'B'])) {
            $errors['emp']['mode_of_pay'] = "This value is not a valid mode of payment";
        }

        if ($_POST['emp']['mode_of_pay'] == 'B') {
            if (empty($_POST['emp']['bank_id']) || !isset($banks[$_POST['emp']['bank_id']])) {
                $errors['emp']['bank_id'] = "This is not a valid bank";
            }

            if (empty($_POST['emp']['iban_no']) || !preg_match('/^(AE\d{21}|\d{23})$/', $_POST['emp']['iban_no'])) {
                $errors['emp']['iban_no'] = "This is not a valid IBAN number";
            }

            if (empty($_POST['emp']['personal_id_no']) || !preg_match('/^\d{14}$/', $_POST['emp']['personal_id_no'])) {
                $errors['emp']['personal_id_no'] = "This is not a valid personal ID number";
            }
        }
        
        if (!is_array($_POST['job']['week_offs'])) {
            $errors['job']['week_offs'] = "This is not a valid list of days"; 
        } else {
            foreach ($_POST['job']['week_offs'] as $day) {
                if (!in_array($day, ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"])) {
                    $errors['job']['week_offs'][$day] = "This is not a valid day"; 
                } else {
                    $inputs['job']['week_offs'][] = $day;
                }
            }
        }

        if (!in_array($_POST['job']['attendance_type'], array_keys(get_employee_attendance_types()))) {
            $errors['job']['attendance_type'] = "This value is not a valid attendance type";
        }

        if (!empty($_POST['job']['default_shift_id']) && !isset($shifts[$_POST['job']['default_shift_id']])) {
            $errors['job']['default_shift_id'] = "This is not a valid shift";
        }

        if (
            !empty($_POST['job']['supervisor_id'])
            && (
                !is_array($_POST['job']['supervisor_id'])
                || in_array(false, array_map(
                    function ($supervisor) use ($employees) { return isset($employees[$supervisor]); },
                    $_POST['job']['supervisor_id']
                ))
            )
        ) {
            $errors['job']['supervisor_id'] = "This is not a valid supervisors list";
        }

        $_POST['job']['has_overtime'] = intval(!empty($_POST['job']['has_overtime']));
        $_POST['job']['has_pension'] = intval(!empty($_POST['job']['has_pension']));

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