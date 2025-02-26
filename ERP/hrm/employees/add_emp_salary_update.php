<?php

$path_to_root = "../..";
$page_security = 'HRM_EMP_SALARY'; 

require_once $path_to_root . "/includes/session.inc";
require_once $path_to_root . "/hrm/db/employees_db.php";
require_once $path_to_root . "/hrm/db/emp_salaries_db.php";
require_once $path_to_root . "/hrm/db/emp_salary_details_db.php";
require_once $path_to_root . "/hrm/db/pay_elements_db.php";
require_once $path_to_root . "/hrm/helpers/addEmpSalaryUpdateHelpers.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    AddEmpSalaryUpdateHelper::handleAddEmpSalaryUpdateRequest();
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
    <h1 class="p-4">Increment / Decrement Employee Salary</h1>

    <form
        action=""
        method="POST"
        id="salary-form">
        <div class="row p-3">
            <div class="col-lg-4 offset-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title text-dark">Salary Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row required">
                            <label for="employee_id" class="col-form-label col-sm-3">Employee</label>
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
                        <div class="form-group row">
                            <label for="current_salary" class="col-form-label col-sm-3">Current Salary:</label>
                            <div class="col-auto">
                                <input
                                    type="text"
                                    class="form-control-plaintext"
                                    id="current_salary"
                                    readonly
                                    value="0.00">
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="from" class="col-form-label col-sm-3">From:</label>
                            <div class="col-auto">
                                <input
                                    type="text"
                                    required
                                    data-parsley-trigger-after-failure="change"
                                    data-parsley-date-after="1971-01-01"
                                    autocomplete="off"
                                    class="form-control"
                                    name="from"
                                    id="from"
                                    data-provide="datepicker"
                                    data-date-format="<?= getDateFormatForBSDatepicker() ?>"
                                    data-date-autoclose="true"
                                    data-date-today-highlight="true"
                                    placeholder="<?= getDateFormatForBSDatepicker() ?>">
                            </div>
                        </div>
                        <?php foreach(getPayElementsKeyedById(['is_fixed' => 1]) as $id => $payElem): ?>
                        <div class="form-group row required">
                            <label for="PEL-<?= $id ?>" class="col-form-label col-sm-3"><?= $payElem['name'] ?>:</label>
                            <div class="col-auto">
                                <input
                                    required
                                    data-pay-element="<?= $id ?>"
                                    data-type="<?= $payElem['type'] ?>"
                                    type="number"
                                    class="form-control"
                                    name="salary[<?= $id; ?>]"
                                    id="PEL-<?= $id ?>"
                                    min="0"
                                    value="0">
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <div class="form-group row required">
                            <label for="gross_salary" class="col-form-label col-sm-3">Total Salary:</label>
                            <div class="col-auto">
                                <input
                                    type="number"
                                    readonly
                                    class="form-control"
                                    name="gross_salary"
                                    id="gross_salary"
                                    data-parsley-not-equals="#current_salary"
                                    data-parsley-trigger-after-failure="change"
                                    data-parsley-min-message="Nobody works without salary!"
                                    required
                                    min="1"
                                    value="0">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div id="control-area" class="col-12 text-center mt-4">
                <button type="reset" id="btn-reset" class="btn shadow-none btn-action btn-label-dark">Cancel</button>
                <button type="submit" id="btn-submit" class="btn shadow-none btn-action btn-primary">Update</button>
            </div>
        </div>
    </form>
</div>

<?php ob_start(); ?>
<script src="<?= $path_to_root ?>/../assets/plugins/general/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/parsley/parsley.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/toastr/build/toastr.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/hrm/js/add_emp_salary_update.js?v1.0.0" type="text/javascript"></script>
<?php $GLOBALS['__FOOT__'][] = ob_get_clean(); end_page();