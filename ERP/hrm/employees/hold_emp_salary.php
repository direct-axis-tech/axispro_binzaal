<?php

$path_to_root = "../..";
$page_security = 'HRM_HOLD_EMP_SALARY';

require_once $path_to_root . "/includes/session.inc";
require_once $path_to_root . "/hrm/db/employees_db.php";
include_once $path_to_root . "/hrm/db/hold_salary_db.php";
require_once $path_to_root . "/hrm/helpers/holdEmpSalaryHelpers.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    holdEmpSalaryHelpers::handleHoldEmpSalaryRequest();
    exit();
}

ob_start(); ?>
<link href="<?= $path_to_root ?>/../assets/plugins/general/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css" rel="stylesheet" type="text/css" />
<link href="<?= $path_to_root ?>/../assets/plugins/custom/parsley/parsley.css" rel="stylesheet" type="text/css" />
<link href="<?= $path_to_root ?>/../assets/plugins/general/toastr/build/toastr.css" rel="stylesheet" type="text/css" />
<link href="<?= $path_to_root ?>/../assets/plugins/general/sweetalert2/dist/sweetalert2.css" rel="stylesheet" type="text/css" />

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

page(trans('Hold Salary'), false, false, '', '', false, '', true); ?>

<div id="_content" class="mx-5 bg-white border rounded text-dark p-3">
    <form method="POST" action="" class="w-100" id="hold_employee_salary_form">
        <div class="row p-3">
            <div class="col-lg-4 offset-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title text-dark text-center">Hold Employee's Salary</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group row required">
                            <label for="employee_id" class="col-form-label col-sm-3">Select Employee</label>
                            <select 
                                required 
                                data-selection-css-class="validate" 
                                class="custom-select" 
                                name="employee_id" 
                                id="employee_id">
                                <option value="">-- Select Employee --</option>
                                <?php foreach (getEmployeesKeyedById() as $empId => $emp) : ?>
                                    <option value="<?= $empId ?>"><?= $emp['formatted_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group row">
                            <label for="current_salary" class="col-form-label col-sm-3">Current Salary:</label>
                            <div class="col-auto">
                                <input type="text" class="form-control-plaintext" id="current_salary" readonly value="0.00">
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="trans_date" class="col-form-label col-sm-3">From the month of:</label>
                            <div class="col-auto">
                                <input type="text" required data-parsley-trigger-after-failure="change" data-parsley-date-after="1971-01-01" autocomplete="off" class="form-control" name="trans_date" id="trans_date" data-provide="datepicker" data-date-format="<?= getDateFormatForBSDatepicker() ?>" data-date-autoclose="true" data-date-today-highlight="true" placeholder="<?= getDateFormatForBSDatepicker() ?>">
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="hold_salary_amount" class="col-form-label col-sm-3">Total Amount:</label>
                            <div class="col-auto">
                                <input type="number" class="form-control" name="hold_salary_amount" id="hold_salary_amount" data-parsley-not-equals="#hold_salary_amount" data-parsley-trigger-after-failure="change" data-parsley-min-message="Please enter Holded salary amount!" required min="1">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="memo" class="col-form-label col-sm-3">Remarks</label>
                            <div class="col-sm-9">
                                <textarea id="memo" name="memo" class="form-control" width="450px" placeholder="Enter Remarks if any." ></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div id="control-area" class="col-12 text-center mt-1">
                                <button type="reset" id="btn-reset" class="btn btn-secondary">Cancel</button>
                                <button type="submit" id="btn-submit" class="btn btn-success">Update</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php ob_start(); ?>
<script src="<?= $path_to_root ?>/../assets/plugins/general/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/parsley/parsley.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/toastr/build/toastr.min.js" type="text/javascript"></script>

<script src="<?= $path_to_root ?>/../assets/plugins/general/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/toastr/build/toastr.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/parsley/parsley.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/sweetalert2/dist/sweetalert2.min.js" type="text/javascript"></script>

<script src="<?= $path_to_root ?>/hrm/js/hold_emp_salary.js?id=v1.0.0" type="text/javascript"></script>

<?php $GLOBALS['__FOOT__'][] = ob_get_clean();
end_page();
