<?php

use App\Models\EntityGroup;
use App\Models\EntityGroupCategory;
use App\Models\Hr\Company;

class AddEmployeeHelper {

    /**
     * Handle the add Employee Request
     *
     * @return void
     */
    public static function handleAddEmployeeRequest() {
        if (!user_check_access('HRM_ADD_EMPLOYEE')) {
            echo json_encode([
                "status" => 403,
                "message" => "You are not allowed to access this function"
            ]);
            exit();
        }

        [
            "emp" => $employee,
            "job" => $empJob,
            "salary" => $empSalaryDetails
        ] = self::getValidInputsForAddingEmployee();

        $empJob['week_offs'] = json_encode($empJob['week_offs']);
        $empJob['supervisor_id'] = json_encode($empJob['supervisor_id'] ?? []);

        $empJob['commence_from'] = $empJob['_commence_from'];
        $employee['date_of_birth'] = $employee['_date_of_birth'];
        $employee['date_of_join'] = $empJob['commence_from'];
        
        begin_transaction();
        $empJob['employee_id'] = insertOneEmployee($employee);
        insertOneEmpJob($empJob);
        saveEmployeeSalary($empJob['employee_id'], $empJob['commence_from'], $empSalaryDetails);
        commit_transaction();

        echo json_encode([
            "status" => 201,
            "message" => "Created"
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
    private static function getValidInputsForAddingEmployee() {
        $errors = [];
        $fixedPayElements = getPayElementsKeyedById(['is_fixed' => 1]);
        $nationalities = getCountriesKeyedByCode();
        $departments = getDepartmentsKeyedById();
        $designations = getDesignationsKeyedById();
        $banks = getBanksKeyedById();
        $shifts = getShiftsKeyedById();
        $employees = getEmployeesKeyedById();

        foreach ([
            "emp" => [
                'emp_ref',
                'machine_id',
                'name',
                'preferred_name',
                'nationality',
                'gender',
                'date_of_birth',
                'email',
                'mobile_no',
                'mode_of_pay'
            ],
            "job" => [
                'department_id',
                'designation_id',
                'commence_from',
                'week_offs',
                'work_hours',
                'visa_company_id',
                'working_company_id',
                'attendance_type',
            ],
            "salary" => [1]
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

        $pattern = [
            'alpha_num' => '/^[0-9a-zA-Z][0-9a-zA-Z]*$/',
            'spaced_alpha' => '/^[a-zA-Z][a-zA-Z ]*$/',
        ];

        foreach (['emp_ref', 'machine_id'] as $key) {
            if (!preg_match($pattern['alpha_num'], $_POST['emp'][$key])) {
                $errors['emp'][$key] = "{$key} must only consists of alphabets and numbers";
            } else {
                $_POST['emp'][$key] = strtoupper($_POST['emp'][$key]);
            }
        }

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
            $_POST['emp']['_date_of_birth'] = $dt->format(DB_DATE_FORMAT);
        }

        if (!filter_var($_POST['emp']['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['emp']['email'] = "This is not a valid email address";
        }

        if (!preg_match(UAE_MOBILE_NO_PATTERN, $_POST['emp']['mobile_no'])) {
            $errors['emp']['mobile_no'] = "This is not a valid UAE mobile number";
        } else {
            $_POST['emp']['mobile_no'] = preg_replace(UAE_MOBILE_NO_PATTERN, "$2", $_POST['emp']['mobile_no']);
        }

        if (
            $_POST['emp']['nationality'] != $GLOBALS['SysPrefs']->prefs['home_country']
            && empty($_POST['emp']['passport_no'])
        ) {
            $errors['emp']['passport_no'] = "The passport number should not be empty";
        }
        
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

        if (!Company::whereId($_POST['job']['working_company_id'])->exists()) {
            $errors['job']['working_company_id'] = "This is not a valid company";
        }
        
        if (!Company::whereId($_POST['job']['visa_company_id'])->exists()) {
            $errors['job']['visa_company_id'] = "This is not a valid company";
        }

        if (!isset($departments[$_POST['job']['department_id']])) {
            $errors['job']['department_id'] = "This is not a valid department";
        }

        if (!isset($designations[$_POST['job']['designation_id']])) {
            $errors['job']['designation_id'] = "This is not a valid designation";
        }

        if (
            !($dt = DateTime::createFromFormat(getDateFormatInNativeFormat(), $_POST['job']['commence_from']))
            || $dt->format(getDateFormatInNativeFormat()) != $_POST['job']['commence_from']
        ) {
            $errors['job']['commence_from'] = "This is not a valid date";
        } else {
            $_POST['job']['_commence_from'] = $dt->format(DB_DATE_FORMAT);
        }

        if (!is_array($_POST['job']['week_offs'])) {
            $errors['job']['week_offs'] = "This is not a valid list of days"; 
        } else {
            foreach ($_POST['job']['week_offs'] as $day) {
                if (!in_array($day, ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"])) {
                    $errors['job']['week_offs'][$day] = "This is not a valid day"; 
                }
            }
        }

        if (!is_numeric($_POST['job']['work_hours'])) {
            $errors['job']['work_hours'] = "This is a valid working hour";
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

        foreach (array_keys($fixedPayElements) as $id) {
            if (!isset($_POST['salary'][$id]) || !is_numeric($_POST['salary'][$id])) {
                $errors['salary'][$id] = "This is not a valid salary";
            }
        }

        if (!empty($_POST['job']['has_pension']) && empty($_POST['job']['pension_scheme'])) {
            $errors['job']['pension_scheme'] = "Pension_scheme is required";
        }

        if (!empty($errors)) {
            echo json_encode([
                "status" => 422,
                "message" => "Request contains invalid data",
                "errors" => $errors
            ]);
            exit();
        }

        $_POST['ar_name'] = $GLOBALS['db']->real_escape_string($_POST['ar_name'] ?? '');

        return $_POST;
    }
}