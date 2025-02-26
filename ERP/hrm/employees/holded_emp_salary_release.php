<?php

$path_to_root = "../..";
$page_security = 'HRM_HOLD_EMP_SALARY'; // cater this one

include_once $path_to_root . "/includes/session.inc";
include_once $path_to_root . "/hrm/db/employees_db.php";
include_once $path_to_root . "/hrm/db/hold_salary_db.php";
include_once $path_to_root . "/hrm/helpers/holdedEmpSalaryReleaseHelpers.php";

$filters['id'] = $_GET['id_no'];
$sql = get_sql_for_view_holded_salary($filters);
$holdedSalary = db_query($sql, "Could not retrieve Holded salary details")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    holdedEmpSalaryReleaseHelpers::handleHoldedEmpSalaryReleaseRequest($holdedSalary);
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

page(trans('Release Holded Salary'), false, false, '', '', false, '', true); ?>

<div id="_content" class="mx-5 bg-white border rounded text-dark p-3">
<form action="" method="POST" id="holded_salary_Release_form" >
        <div class="row p-3">
            <div class="col-lg-4 offset-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title text-dark">Release Holded Salary</h5>
                    </div>
                    <div class="card-body">
                        <input type="hidden" id="employee_id" name="employee_id" value="<?=$holdedSalary['employee_id']?>">
                        <input type="hidden" id="id" name="id" value="<?=$holdedSalary['id']?>">
                        <div class="form-group row required">
                            <input type="text" value="<?=$holdedSalary['formatted_name']?>" disabled placeholder="Selected employee" class="form-control" name="emp_name" required>
                        </div>
                        <div class="form-group row required">
                            <label for="amount" class="col-form-label col-sm-3">Total Amount:</label>
                            <div class="col-auto">
                                <input type="number" value=<?=$holdedSalary['amount']?> disabled class="form-control" name="amount" id="amount" required>
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="trans_date" class="col-form-label col-sm-3">From the month of:</label>
                            <div class="col-auto">
                                <input type="text" required data-parsley-trigger-after-failure="change" data-parsley-date-after="1971-01-01" autocomplete="off" class="form-control" name="trans_date" id="trans_date" data-provide="datepicker" data-date-format="<?= getDateFormatForBSDatepicker() ?>" data-date-autoclose="true" data-date-today-highlight="true" placeholder="<?= getDateFormatForBSDatepicker() ?>">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div id="control-area" class="col-12 text-center mt-1">
                                <button type="reset" id="btn-reset" class="btn btn-secondary">Cancel</button>
                                <button type="submit" id="release_holded_salary" class="btn btn-success"><?= trans('Release') ?></button>
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

<script src="<?= $path_to_root ?>/hrm/js/holded_emp_salary_release.js?id=v1.0.0" type="text/javascript"></script>

<?php $GLOBALS['__FOOT__'][] = ob_get_clean();
end_page();