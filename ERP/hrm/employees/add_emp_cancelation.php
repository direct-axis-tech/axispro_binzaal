<?php

$path_to_root = "../..";
$page_security = 'HRM_ADD_EMP_CANCELATION';

require_once $path_to_root . "/includes/session.inc";
require_once $path_to_root . "/hrm/db/employees_db.php";
require_once $path_to_root . "/hrm/helpers/addEmployeeCancelationHelpers.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    AddEmployeeCancelationHelper::handleAddEmployeeCancelationRequest();
}

$employees = getEmployees();

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
</style>
<?php $GLOBALS['__HEAD__'][] = ob_get_clean();

$employmentStatuses = array_intersect_key(
    $GLOBALS['employment_statuses'],
    array_flip([ES_RETIRED, ES_RESIGNED, ES_TERMINATED])
);

page(trans('Add Employee Cancelations'), false, false, '', '', false, '', true); ?>

<div id="_content" class="row mx-5 bg-white border rounded text-dark">
    <div class="border rounded mx-auto mb-4 col-lg-9 p-4">
        <form action="" method="POST" id="add-cancelation-form" data-parsley-validate>
            <div class="row">
                <div class="col-lg-8 offset-lg-2">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title text-dark">Add Employee Cancelation</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row required">
                                <label for="emp_id" class="col-form-label col-sm-3">Employee:</label>
                                <div class="col-auto">
                                    <select required data-selection-css-class="validate" name="emp_id" id="emp_id" style="width: 100%;" class="custom-select">
                                        <option value="">-- Select Employee --</option>
                                        <?php foreach ($employees as $emp) : ?>
                                            <option value="<?= $emp['id'] ?>"><?= $emp['formatted_name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row required">
                                <label for="status" class="col-form-label col-sm-3">Type:</label>
                                <div class="col-auto">
                                    <select required data-selection-css-class="validate" name="status" id="status" style="width: 100%;" class="custom-select">
                                        <option value="">-- Select Cancelation Type --</option>
                                        <?php foreach ($employmentStatuses as $id => $status): ?>
                                            <option value="<?= $id ?>"><?= $status ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row required">
                                <label for="cancel_requested_on" class="col-form-label col-sm-3">Requested On Date:</label>
                                <div class="col-auto">
                                    <input type="text" required data-parsley-trigger-after-failure="change" class="form-control" name="cancel_requested_on" id="cancel_requested_on" data-provide="datepicker" autocomplete="off" data-parsley-is-cancelation-unique="true" data-date-format="<?= getDateFormatForBSDatepicker() ?>" data-date-autoclose="true" data-date-days-of-week-highlighted="5,5" placeholder="e.g. <?= Today() ?>">
                                    <small id="from_help" class="form-text text-muted">The day: When Employee requested for Cancelation</small>
                                </div>
                            </div>
                            <div class="form-group row required">
                                <label for="cancel_leaving_on" class="col-form-label col-sm-3">Leaving Date:</label>
                                <div class="col-auto">
                                    <input type="text" required autocomplete="off" data-parsley-trigger-after-failure="change" class="form-control" name="cancel_leaving_on" id="cancel_leaving_on" data-provide="datepicker" data-date-format="<?= getDateFormatForBSDatepicker() ?>" data-date-autoclose="true" data-date-days-of-week-highlighted="5,5" data-date-today-btn="true" data-date-today-highlight="true" placeholder="e.g. <?= Today() ?>">
                                    <small id="reviewed_on_help" class="form-text text-muted">The day: When Employee will Leave the Company</small>
                                </div>
                            </div>
                            <div class="form-group row required">
                                <label for="cancel_approved_by" class="col-form-label col-sm-3">Approved By:</label>
                                <div class="col-auto">
                                    <select required data-selection-css-class="validate" name="cancel_approved_by" id="cancel_approved_by" style="width: 100%;" class="custom-select">
                                        <option value="">-- Select Approver --</option>
                                        <?php foreach (getHODsKeyedById(["has_user" => true]) as $hod) : ?>
                                            <option value="<?= $hod['user_id'] ?>"><?= $hod['formatted_name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row required">
                                <label for="cancel_remarks" class="col-form-label col-sm-3">Remarks</label>
                                <div class="col-sm-9">
                                    <textarea placeholder="Remarks for this Cancelation if any" name="cancel_remarks" class="form-control" id="cancel_remarks" rows="8"></textarea>
                                </div>
                            </div>
                        </div>
                        <div id="control-area" class="card-footer">
                            <button type="reset" id="reset-btn" class="btn shadow-none btn-label-dark float-left">
                                <span class="la la-refresh mr-2"></span>
                                Reset
                            </button>
                            <button type="submit" id="submit-btn" class="btn shadow-none btn-primary float-right">
                                <span class="la la-plus mr-2"></span>
                                Add Cancelation
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php ob_start(); ?>
<script src="<?= $path_to_root ?>/../assets/plugins/general/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/parsley/parsley.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/toastr/build/toastr.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/sweetalert2/dist/sweetalert2.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/hrm/js/add_emp_cancelation_001.js" type="text/javascript"></script>
<?php $GLOBALS['__FOOT__'][] = ob_get_clean();
end_page();