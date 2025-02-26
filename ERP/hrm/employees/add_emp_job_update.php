<?php

use App\Models\Hr\Company;
use App\Models\Hr\EmployeePensionConfig;

$path_to_root = "../..";
$page_security = 'HRM_JOB_UPDATE'; 

require_once $path_to_root . "/includes/session.inc";
require_once $path_to_root . "/hrm/db/employees_db.php";
require_once $path_to_root . "/hrm/db/shifts_db.php";
require_once $path_to_root . "/hrm/db/departments_db.php";
require_once $path_to_root . "/hrm/db/designations_db.php";
require_once $path_to_root . "/hrm/db/emp_jobs_db.php";
require_once $path_to_root . "/hrm/helpers/addEmpJobUpdateHelpers.php";

$weekdays = [
    ["abbr" => "Mon", "name" => "Monday"],
    ["abbr" => "Tue", "name" => "Tuesday"],
    ["abbr" => "Wed", "name" => "Wednesday"],
    ["abbr" => "Thu", "name" => "Thursday"],
    ["abbr" => "Fri", "name" => "Friday"],
    ["abbr" => "Sat", "name" => "Saturday"],
    ["abbr" => "Sun", "name" => "Sunday"]
];

$companies = Company::all()->sortBy('name');
$visaCompanies = $companies->where('mol_id', '!=', '');
$pensionConfigs = EmployeePensionConfig::active()->get();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    AddEmpJobUpdateHelper::handleAddEmpJobUpdateRequest();
}

ob_start(); ?>
<link href="<?= $path_to_root ?>/../assets/plugins/general/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css" rel="stylesheet" type="text/css"/>
<link href="<?= $path_to_root ?>/../assets/plugins/custom/parsley/parsley.css" rel="stylesheet" type="text/css"/>
<link href="<?= $path_to_root ?>/../assets/plugins/general/toastr/build/toastr.css" rel="stylesheet" type="text/css"/>
<style>
    .is-valid .form-control,
    .is-valid .custom-select,
    .is-valid .validate {
        border-color: #28a745;
    }

    .is-invalid .form-control,
    .is-invalid .custom-select,
    .is-invalid .validate {
        border-color: #dc3545;
    }

    .btn-action {
        font-size: 1.5rem;
    }
</style>
<?php $GLOBALS['__HEAD__'][] = ob_get_clean();

page(trans('Adjust Salary'), false, false, '', '', false, '', true); ?>

<div id="_content" class="mx-5 bg-white border rounded text-dark p-3">
    <h1 class="p-4">Update Employee's Job Description</h1>

    <form
        action=""
        method="POST"
        id="job-update-form">
        <div class="row p-3">
            <div class="col-lg-4 offset-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title text-dark">New Job Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row required">
                            <label for="employee_id" class="col-form-label col-sm-3">Employee</label>
                            <div class="col-sm-9 col-md-6 col-lg-4">
                                <select
                                    required
                                    data-selection-css-class="validate"
                                    name="employee_id"
                                    class="custom-select"
                                    id="employee_id">
                                    <option value="">-- Select Employee --</option>
                                    <?php foreach (getEmployeesKeyedById() as $empId => $emp): ?>
                                    <option value="<?= $empId ?>"><?= $emp['formatted_name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="working_company_id" class="col-form-label col-sm-3">Working Company:</label>
                            <div class="col-sm-9 col-md-6 col-lg-4">
                                <select
                                    required
                                    class="custom-select"
                                    name="working_company_id"
                                    id="working_company_id">
                                    <option value="">-- Select Working Company --</option>
                                    <?php foreach ($companies as $wCompany): ?>
                                    <option value="<?= $wCompany->id ?>"><?= $wCompany->name ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="visa_company_id" class="col-form-label col-sm-3">Visa Company:</label>
                            <div class="col-sm-9 col-md-6 col-lg-4">
                                <select
                                    required
                                    class="custom-select"
                                    name="visa_company_id"
                                    id="visa_company_id">
                                    <option value="">-- Select Visa Company --</option>
                                    <?php foreach ($visaCompanies as $vCompany): ?>
                                    <option value="<?= $vCompany->id ?>"><?= $vCompany->name ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="department_id" class="col-form-label col-sm-3">Department:</label>
                            <div class="col-sm-9 col-md-6 col-lg-4">
                                <select
                                    required
                                    class="custom-select"
                                    name="department_id"
                                    id="department_id">
                                    <option value="">-- Select Department --</option>
                                    <?php foreach (getDepartmentsKeyedById() as $id => $department): ?>
                                    <option value="<?= $id ?>"><?= $department['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="designation_id" class="col-form-label col-sm-3">Designation:</label>
                            <div class="col-sm-9 col-md-6 col-lg-4">
                                <select
                                    class="custom-select"
                                    required
                                    name="designation_id"
                                    id="designation_id">
                                    <option value="">-- Select Designation --</option>
                                    <?php foreach (getDesignationsKeyedById() as $id => $designation): ?>
                                    <option value="<?= $id ?>"><?= $designation['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="commence_from" class="col-form-label col-sm-3">Starting from date:</label>
                            <div class="col-auto">
                                <input
                                    type="text"
                                    required
                                    data-parsley-trigger-after-failure="change"
                                    class="form-control"
                                    name="commence_from"
                                    id="commence_from"
                                    data-provide="datepicker"
                                    data-date-format="<?= getDateFormatForBSDatepicker() ?>"
                                    data-date-autoclose="true"
                                    data-date-today-highlight="true"
                                    placeholder="<?= getDateFormatForBSDatepicker() ?>">
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="week_offs" class="col-form-label col-sm-3">Weekly Offs:</label>
                            <div class="col-sm-9 col-md-6 col-lg-4">
                                <select
                                    required
                                    class="custom-select"
                                    name="week_offs[]"
                                    id="week_offs"
                                    multiple>
                                    <?php foreach ($weekdays as $day): ?>
                                    <option value="<?= $day['abbr'] ?>">
                                        <?= $day['name'] ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="work_hours" class="col-form-label col-sm-3">Work Hours:</label>
                            <div class="col-auto">
                                <input
                                    type="number"
                                    required
                                    class="form-control"
                                    name="work_hours"
                                    id="work_hours">
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="attendance_type" class="col-form-label col-sm-3">Attendance Type:</label>
                            <div class="col-sm-9 col-md-6 col-lg-4">
                                <select 
                                    required
                                    class="custom-select"
                                    name="attendance_type"
                                    id="attendance_type" >
                                    <option value="">-- Select Attendance Type --</option>
                                    <?php foreach (get_employee_attendance_types() as $typeId => $types): ?>
                                    <option value="<?= $typeId ?>"><?= $types ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="default_shift_id" class="col-form-label col-sm-3">Shift Timing:</label>
                            <div class="col-sm-9 col-md-6 col-lg-4">
                                <select class="custom-select" name="default_shift_id" id="default_shift_id">
                                    <option value="">-- Select Shift Timing --</option>
                                    <?php foreach (getShiftsKeyedById() as $id => $shift): ?>
                                    <option value="<?= $id ?>"><?= $shift['description'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="supervisor_id" class="col-form-label col-sm-3">Supervisor:</label>
                            <div class="col-sm-9 col-md-6 col-lg-4">
                                <select class="custom-select" name="supervisor_id[]" id="supervisor_id" multiple >
                                    <option value="">-- Select Supervisor --</option>
                                    <?php foreach (getEmployeesKeyedById() as $id => $emp): ?>
                                    <option value="<?= $id ?>"><?= $emp['emp_ref'] . " - " . $emp['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="pension_scheme" class="col-form-label col-sm-3">Pension Scheme:</label>
                            <div class="col-sm-9 col-md-6 col-lg-4">
                                <select class="custom-select" name="pension_scheme" id="pension_scheme"   >
                                    <option value="" >-- Select Pension Scheme --</option>
                                    <?php foreach ($pensionConfigs as $pension => $schemes): ?>
                                        <option value="<?= $schemes['id'] ?>"><?= $schemes['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-9 offset-sm-3 mb-2">
                                <div class="form-check">
                                    <input
                                        type="checkbox"
                                        name="has_commission"
                                        id="has_commission"
                                        class="form-check-input mt-0"
                                        checked="checked"
                                        value="1">
                                    <label for="has_commission" class="form-check-label pl-2">Has Commission</label>
                                </div>
                            </div>
                            <div class="col-sm-9 offset-sm-3 mb-2">
                                <div class="form-check">
                                    <input
                                        type="checkbox"
                                        name="has_pension"
                                        id="has_pension"
                                        class="form-check-input mt-0"
                                        value="1">
                                    <label for="has_pension" class="pl-2 form-check-label">Has Pension</label>
                                </div>
                            </div>
                            <div class="col-sm-9 offset-sm-3 mb-2">
                                <div class="form-check">
                                    <input
                                        type="checkbox"
                                        name="has_overtime"
                                        id="has_overtime"
                                        class="form-check-input mt-0"
                                        checked="checked"
                                        value="1">
                                    <label for="has_overtime" class="pl-2 form-check-label">Has Overtime</label>
                                </div>
                            </div>
                            <div class="col-sm-9 offset-sm-3 mb-2">
                                <div class="form-check">
                                    <input
                                        type="checkbox"
                                        name="require_attendance"
                                        id="require_attendance"
                                        class="form-check-input mt-0"
                                        checked="checked"
                                        value="1">
                                    <label for="require_attendance" class="pl-2 form-check-label">Require Attendance Validation</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div id="control-area" class="col-12 text-center mt-4">
                <button type="reset" id="btn-reset" class="btn shadow-none btn-action btn-label-dark">Cancel</button>
                <button type="submit" id="btn-submit" class="btn shadow-none btn-action btn-primary">Add Job Update</button>
            </div>
        </div>
    </form>
</div>

<?php ob_start(); ?>
<script src="<?= $path_to_root ?>/../assets/plugins/general/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/parsley/parsley.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/toastr/build/toastr.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/hrm/js/add_emp_job_update.js?id=v1.0.2" type="text/javascript"></script>
<?php $GLOBALS['__FOOT__'][] = ob_get_clean(); end_page();