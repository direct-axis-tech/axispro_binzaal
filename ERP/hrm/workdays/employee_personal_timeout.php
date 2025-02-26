<?php

$path_to_root = "../..";

require_once $path_to_root . "/includes/session.inc";
require_once $path_to_root . "/hrm/db/employees_db.php";
require_once $path_to_root . "/hrm/db/emp_timeouts_db.php";

$canAccess = [
    'OWN' => user_check_access('HRM_TIMEOUT_REQUEST'),
    'ALL' => user_check_access('HRM_TIMEOUT_REQUEST_ALL')
];

$page_security = in_array(true, $canAccess, true) ? 'SA_ALLOW' : 'SA_DENIED';
$canOnlyAccessOwn = !$canAccess['ALL'];

$currentEmployeeId = getCurrentEmployee()['id'] ?? -1;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['action']) && $_POST['action'] == 'get_employee_timeouts') {

        employeeTimeoutRequestDetails($canAccess);
        exit;
    } elseif (!empty($_POST['action']) && $_POST['action'] == 'is_timeout_unique') {

        isEmployeeTimeoutUnique();
        exit;
    }
    handleEmployeeTimeoutRequest($canAccess);
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
</style>
<?php $GLOBALS['__HEAD__'][] = ob_get_clean();

page(trans('Employee Personal Timeouts'), false, false, '', '', false, '', true); ?>

<div id="_content" class="row mx-5 bg-white border rounded text-dark">
    <div class="border rounded mx-auto mb-4 col-lg-9 p-4">
        <form action="" method="POST" id="add-timeout-form" data-parsley-validate>
            <div class="row">
                <div class="col-lg-8 offset-lg-2">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title text-dark">Apply Personal Timeout</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row required <?= $canOnlyAccessOwn ? 'd-none' : '' ?>">
                                <label for="employee_id" class="col-form-label col-sm-3">Employee:</label>
                                <div class="col-auto">
                                    <select required data-selection-css-class="validate" name="employee_id" id="employee_id" style="width: 100%;" class="custom-select">
                                        <option value="">-- Select Employee --</option>
                                        <?php foreach (getAuthorizedEmployeesKeyedById($canAccess, $currentEmployeeId) as $id => $employee) : ?>
                                            <option <?= $currentEmployeeId == $id ? 'selected' : '' ?> value="<?= $id ?>">
                                                <?= $employee['formatted_name'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row required">
                                <label for="time_out_date" class="col-form-label col-sm-3">Timeout Date:</label>
                                <div class="col-auto">
                                    <input type="text" required class="form-control" name="time_out_date" id="time_out_date" data-provide="datepicker" autocomplete="off" data-moment-js-date-format="<?= dateformat('momentJs') ?>" data-date-format="<?= getDateFormatForBSDatepicker() ?>" data-date-autoclose="true" data-date-days-of-week-highlighted="5,5" value="<?= Today() ?>">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="time_remaining" class="col-form-label col-sm-3">Timeout Remaining In Minutes:</label>
                                <div class="col-auto">
                                    <input type="number" readonly class="form-control-plaintext" name="time_remaining" id="time_remaining" placeholder="0" step=".01" value="0">
                                </div>
                            </div>

                            <div class="form-group row required">
                                <label for="time_out_from" class="col-form-label col-sm-3">Timeout From:</label>
                                <div class="col-auto">
                                    <input type="time" required class="form-control" name="time_out_from" id="time_out_from" placeholder="Select time" autocomplete="off" data-parsley-is-timeout-unique="true" >
                                </div>
                            </div>

                            <div class="form-group row required">
                                <label for="time_out_to" class="col-form-label col-sm-3">Timeout To:</label>
                                <div class="col-auto">
                                    <input type="time" required class="form-control" name="time_out_to" id="time_out_to" placeholder="Select time" autocomplete="off" data-parsley-is-timeout-unique="true" >
                                </div>
                            </div>

                            <div class="form-group row required">
                                <label for="timeout_duration" class="col-form-label col-sm-3">Timeout Duration In Minutes:</label>
                                <div class="col-auto">
                                    <input type="number" required class="form-control" name="timeout_duration" id="timeout_duration" placeholder="" autocomplete="off" readonly>
                                </div>
                            </div>

                            <div class="form-group row required">
                                <label for="remarks" class="col-form-label col-sm-3">Remarks:</label>
                                <div class="col-sm-9">
                                    <textarea placeholder="Remarks for this leave" required name="remarks" class="form-control" id="remarks"></textarea>
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
                                Apply Timeout
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php ob_start(); ?>
<script>
    var leaveTypes = {}
</script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/parsley/parsley.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/parsley/extra/validator/date.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/parsley/extra/validator/words.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/toastr/build/toastr.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/sweetalert2/dist/sweetalert2.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/hrm/js/add_emp_timeout.js?v=<?= time() ?>" type="text/javascript"></script>

<?php $GLOBALS['__FOOT__'][] = ob_get_clean();
end_page(); ?>