<?php

$path_to_root = "../..";

require_once $path_to_root . "/includes/session.inc";
require_once $path_to_root . "/hrm/db/employees_db.php";
require_once $path_to_root . "/hrm/db/leave_types_db.php";
require_once $path_to_root . "/hrm/helpers/addEmployeeLeaveHelpers.php";

$canAccess = [
    'OWN' => user_check_access('HRM_ADD_EMP_LEAVE'),
    'DEP' => user_check_access('HRM_ADD_EMP_LEAVE_DEP'),
    'ALL' => user_check_access('HRM_ADD_EMP_LEAVE_ALL')
];

$page_security = in_array(true, $canAccess, true) ? 'SA_ALLOW' : 'SA_DENIED';
$canOnlyAccessOwn = !$canAccess['ALL'] && !$canAccess['DEP'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['action'])) {
        if ($_POST['action'] == 'get_leave_details') {
            AddEmployeeLeaveHelper::handleGetLeaveDetailsRequest();
            exit();
        }
    }
    AddEmployeeLeaveHelper::handleAddEmployeeLeaveRequest($canAccess);
    exit();
}

$currentEmployeeId = getCurrentEmployee()['id'] ?? -1;

ob_start(); ?>
<link href="<?= $path_to_root ?>/../assets/plugins/general/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css" rel="stylesheet" type="text/css"/>
<link href="<?= $path_to_root ?>/../assets/plugins/custom/parsley/parsley.css" rel="stylesheet" type="text/css"/>
<link href="<?= $path_to_root ?>/../assets/plugins/general/toastr/build/toastr.css" rel="stylesheet" type="text/css"/>
<link href="<?= $path_to_root ?>/../assets/plugins/general/sweetalert2/dist/sweetalert2.css" rel="stylesheet" type="text/css"/>
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

page(trans('Add|Request Employee Leaves'), false, false, '', '', false, '', true); ?>

<div id="_content" class="row mx-5 bg-white border rounded text-dark">
    <div class="border rounded mx-auto mb-4 col-lg-9 p-4">
        <form
            action=""
            method="POST"
            id="add-leave-form"
            data-parsley-validate>
            <div class="row">
                <div class="col-lg-8 offset-lg-2">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title text-dark"><?= $canOnlyAccessOwn ? 'Request Leave' : 'Add Leave' ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row required <?= $canOnlyAccessOwn ? 'd-none' : '' ?>">
                                <label for="employee_id" class="col-form-label col-sm-3">Employee:</label>
                                <div class="col-auto">
                                    <select
                                        required
                                        data-selection-css-class="validate"
                                        name="employee_id"
                                        id="employee_id"
                                        style="width: 100%;"
                                        class="custom-select">
                                        <option value="">-- Select Employee --</option>
                                        <?php foreach (getAuthorizedEmployeesKeyedById($canAccess, $currentEmployeeId, true, true) as $id => $employee): ?>
                                        <option <?= $currentEmployeeId == $id ? 'selected' : '' ?> value="<?= $id ?>">
                                            <?= $employee['formatted_name'] ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row required">
                                <label for="leave_type_id" class="col-form-label col-sm-3">Leave type:</label>
                                <div class="col-auto">
                                    <select
                                        required
                                        data-selection-css-class="validate"
                                        name="leave_type_id"
                                        id="leave_type_id"
                                        class="custom-select">
                                        <option value="">-- Select Leave type --</option>
                                        <?php foreach (getLeaveTypesKeyedById() as $id => $leaveType): ?>
                                        <option value="<?= $id ?>"><?= $leaveType['desc'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row required" id="rdbRadioButtons" name="rdbRadioButtons" style="display: none;">
                                <label for="leave_type_id" class="col-form-label col-sm-3">Is Continuing:</label>
                                <div class="col-auto">
                                    <input type="radio" value='1' name="is_continuing" id="yes" checked /> 
                                    <label for="yes">Yes &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
                                    <input type="radio" value='0' name="is_continuing" id="no" /> 
                                    <label for="no">No &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
                                </div>
                            </div>
                            <div class="form-group row required">
                                <label for="days" class="col-form-label col-sm-3">Days</label>
                                <div class="col-auto">
                                    <input
                                        type="number"
                                        step="0.5"
                                        min="0.5"
                                        required
                                        data-parsley-leave="1"
                                        data-parsley-leave-message="1/2 day leaves are only allowed for one day"
                                        data-parsley-trigger-after-failure="change"
                                        class="form-control"
                                        name="days"
                                        id="days"
                                        placeholder="e.g. 10">
                                </div>
                            </div>

                            <div class="form-group row d-none">
                                <label for="leave_taken" class="col-form-label col-sm-3">Leave taken</label>
                                <div class="col-auto">
                                    <input
                                        type="number"
                                        readonly
                                        class="form-control-plaintext"
                                        name="leave_taken"
                                        id="leave_taken"
                                        placeholder="0"
                                        step=".01">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="leave_remaining" class="col-form-label col-sm-3">Leave remaining</label>
                                <div class="col-auto">
                                    <input
                                        type="number"
                                        readonly
                                        class="form-control-plaintext"
                                        name="leave_remaining"
                                        id="leave_remaining"
                                        placeholder="0"
                                        step=".01">
                                </div>
                            </div>

                            <div class="form-group row required">
                                <label for="from" class="col-form-label col-sm-3">Starts from:</label>
                                <div class="col-auto">
                                    <input
                                        type="text"
                                        required
                                        data-parsley-trigger-after-failure="change"
                                        class="form-control"
                                        name="from"
                                        id="from"
                                        data-provide="datepicker"
                                        autocomplete="off"
                                        data-moment-js-date-format="<?= dateformat('momentJs') ?>"
                                        data-parsley-is-leave-unique="true"
                                        data-date-format="<?= getDateFormatForBSDatepicker() ?>"
                                        data-date-autoclose="true"
                                        data-date-days-of-week-highlighted="5,5"
                                        placeholder="e.g. <?= Today() ?>">
                                    <small id="from_help" class="form-text text-muted">The day: the leave officially starts from</small>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="from" class="col-form-label col-sm-3">Ends at:</label>
                                <div class="col-auto">
                                    <input
                                        type="text"
                                        readonly
                                        class="form-control-plaintext px-4"
                                        id="till">
                                    <small id="till_help" class="form-text text-muted">The day: the leave officially ends at.</small>
                                </div>
                            </div>
                            <div class="form-group row required <?= $canOnlyAccessOwn ? 'd-none' : '' ?>">
                                <label for="requested_on" class="col-form-label col-sm-3">Leave requested on:</label>
                                <div class="col-auto">
                                    <input
                                        type="text"
                                        required
                                        autocomplete="off"
                                        data-parsley-trigger-after-failure="change"
                                        class="form-control"
                                        name="requested_on"
                                        id="requested_on"
                                        data-provide="datepicker"
                                        data-date-format="<?= getDateFormatForBSDatepicker() ?>"
                                        data-date-autoclose="true"
                                        data-date-days-of-week-highlighted="5,5"
                                        data-date-today-btn="true"
                                        data-date-today-highlight="true"
                                        value="<?= $canOnlyAccessOwn ? Today() : '' ?>"
                                        placeholder="e.g. <?= Today() ?>">
                                    <small id="requested_on_help" class="form-text text-muted">The day: employee submitted the leave application</small>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="remarks" class="col-form-label col-sm-3">Remarks</label>
                                <div class="col-sm-9">
                                    <textarea
                                        <?= $canOnlyAccessOwn ? 'required data-parsley-minwords="3"' : ''?>
                                        placeholder="Remarks for this leave"
                                        name="remarks"
                                        class="form-control"
                                        id="remarks"></textarea>
                                </div>
                            </div>
                        </div>
                        <div id="control-area" class="card-footer">
                            <button
                                type="reset"
                                id="reset-btn"
                                class="btn shadow-none btn-label-dark float-left">
                                <span class="la la-refresh mr-2"></span>
                                Reset
                            </button>
                            <button
                                type="submit"
                                id="submit-btn"
                                class="btn shadow-none btn-primary float-right">
                                <span class="la la-plus mr-2"></span>
                                Apply Leave
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
    var leaveTypes = {
        MATERNITY: <?= LT_MATERNITY ?>,
        PARENTAL: <?= LT_PARENTAL ?>,
        SICK: <?= LT_SICK ?>,
        PAID: <?= LT_PAID ?>,
        UNPAID: <?= LT_UNPAID ?>,
        HAJJ: <?= LT_HAJJ ?>,
    }
</script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/parsley/parsley.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/parsley/extra/validator/date.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/parsley/extra/validator/words.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/toastr/build/toastr.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/sweetalert2/dist/sweetalert2.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/hrm/js/add_emp_leaves.js?id=v1.1.0" type="text/javascript"></script>
<?php $GLOBALS['__FOOT__'][] = ob_get_clean(); end_page();