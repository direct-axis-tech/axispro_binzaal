<?php

use App\Models\Hr\Shift;
use App\Models\Hr\Company;
use Carbon\Carbon;

$path_to_root = "../..";

require_once $path_to_root . "/includes/session.inc";
require_once $path_to_root . "/hrm/db/employees_db.php";
require_once $path_to_root . "/hrm/db/departments_db.php";
require_once $path_to_root . "/hrm/db/department_shifts_db.php";
require_once $path_to_root . "/hrm/db/shifts_db.php";
require_once $path_to_root . "/hrm/helpers/employeeShiftHelpers.php";

$canAdd = [
    "OWN" => user_check_access('HRM_ADDSHIFT_OWN'),
    "DEP" => user_check_access('HRM_ADDSHIFT_DEP'),
    "ALL" => user_check_access('HRM_ADDSHIFT_ALL')
];
$page_security = $canAdd['DEP'] || $canAdd['ALL'] ? 'SA_ALLOW' : 'SA_DENIED'; 
$companies = Company::orderBy('name')->get();
$currentEmployee = getCurrentEmployee();
$inputs = EmployeeShiftHelpers::getValidatedInputs($currentEmployee);

// check if this is an upsert request
if (isset($_GET['upsert_shifts'])) {
    return EmployeeShiftHelpers::handleUpsertShiftRequest($canAdd, $currentEmployee['id'] ?? -1, $inputs);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (get_post('method') == 'getShiftsForCopy') {
        return EmployeeShiftHelpers::handleGetShiftsForCopyRequest($canAdd, $currentEmployee['id'] ?? -1, $inputs);
    }

    if (get_post('method') == 'getDepartmentShifts') {
        echo json_encode(['data' => getDepartmentShiftsGroupedById()]);
        exit();
    }

    $employeeShifts = EmployeeShiftHelpers::getShifts(
        $canAdd,
        $currentEmployee['id'] ?? -1,
        $inputs
    );
   
    $inputs['from'] = sql2date($inputs['from']);
    $inputs['till'] = sql2date($inputs['till']);
   
    echo json_encode([
        "status"            => 200,
        "data"              => $employeeShifts,
        "filters"           => $inputs,
        "department_shifts" => getDepartmentShifts($inputs)->fetch_all(MYSQLI_ASSOC)
    ]);
    exit();
}

// The extra head block
ob_start(); ?>
<link href="<?= $path_to_root ?>/../assets/plugins/general/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css" rel="stylesheet" type="text/css" />
<link href="<?= $path_to_root ?>/../assets/plugins/general/toastr/build/toastr.css" rel="stylesheet" type="text/css" />
<link href="<?= $path_to_root ?>/../assets/plugins/custom/parsley/parsley.css" rel="stylesheet" type="text/css" />
<link href="<?= $path_to_root ?>/../assets/plugins/general/sweetalert2/dist/sweetalert2.css" rel="stylesheet" type="text/css" />
<style>
  .select2-container--default .select2-selection--multiple {
    line-height: calc(1.5em + 2px);
    min-height: calc(1.5em + 1.3rem + 2px);
  }

  #daterange_picker.is-valid .form-control,
  #daterange_picker.is-valid .input-group-addon,
  .form-group.is-valid .validate {
    border-color: #28a745;
  }
  
  #daterange_picker.is-invalid .input-group-addon,
  #daterange_picker.is-invalid .form-control,
  .form-group.is-invalid .validate {
      border-color: #dc3545;
  }

  .weekend { color: #fff; background-color: <?= Shift::OFF_COLOR_CODE ?>; }
  .table-custom td, .table-custom th {
    padding: 0.3rem 0.75rem;
  }

  .valid.form-control,
  .valid.custom-select,
  .valid.validate,
  .valid .form-control,
  .valid .custom-select,
  .valid .validate {
      border-color: #28a745;
  }

  .invalid.form-control,
  .invalid.custom-select,
  .invalid.validate,
  .invalid .form-control,
  .invalid .custom-select,
  .invalid .validate {
      border-color: #dc3545;
  }

  .datepicker tbody tr td:hover.disabled,
  .datepicker tbody tr td:focus.disabled,
  .datepicker tbody tr td.disabled {
    background-color: var(--bs-gray-200);
    border-radius: 0;
  }

  .datepicker tbody tr td.selected-date {
    background-color: #E8FFF3;
  }
  
  .datepicker tbody tr td:focus.selected-date,
  .datepicker tbody tr td:hover.selected-date {
    background-color: #d1fff7;
  }

  .datepicker tbody tr td.selected-date.disabled {
    background-color: #dff5ea;
  }

  #emp_shifts_tbl .emp-shift .custom-select { width: 100px; }
  #emp_shifts_tbl .emp-shift select.custom-select { cursor: pointer; }
  #emp_shifts_tbl th {
    font-weight: bold;
    border-bottom-width: 5px;
  }
  #emp_shifts_tbl td { vertical-align: middle; }
  #save_shifts_btn [class^="la-"], #save_shifts_btn [class*=" la-"] { font-size: 4rem; }

  .readonly-shift {
    position: relative;
    text-align: left;
    background: #f8f9fa;
  }
  .readonly-shift:after {
    content: attr(data-text);
  }

  #copyShiftsTable tbody,
  #copyShiftsTable thead {
    position: relative;
  }

  #copyShiftsTable thead th {
    position: sticky;
    background-color: white;
    top: 0;
  }

  #copyShiftsTable tbody tr:nth-child(odd) td {
    background: rgba(245, 248, 250);
  }

  #copyShiftsTable tbody tr:nth-child(even) td {
    background: #fff;
  }

  #copyShiftsTable thead th:nth-child(1) {
    position: sticky;
    left: -1px;
    z-index: 2;
  }

  #copyShiftsTable tbody td:nth-child(1) {
    position: sticky;
    left: -1px;
    z-index: 1;
  }
</style>
<?php $GLOBALS['__HEAD__'][] = ob_get_clean();

page(trans('Assign Shift - HRM'), false, false, "", "", false, '', true);

$departments = getAuthorizedDepartments($canAdd, $currentEmployee['id'] ?? -1)->fetch_all(MYSQLI_ASSOC);
$departmentShifts = getDepartmentShiftsKeyedById(["department_id" => $inputs['department_id'] ?: -1]);
$employees = getAuthorizedEmployeesWithSelectedAttribute(
    $canAdd,
    $currentEmployee['id'] ?? -1,
    $inputs['employee_id'],
    false,
    true
);
$shifts = getShiftsKeyedById();

?>
<div id="_content" class="text-dark">
    <div class="card mx-5">
        <form
            action=""
            method="POST"
            class="w-100"
            id="filter_form">
            <div class="card mx-4 mb-3">
                <div class="card-body">
                    <div class="row pt-3 align-items-center">
                        <div class="col-lg-2">
                            <div class="form-group required">
                                <label for="department"><?= trans('Department') ?>:</label>
                                <div>
                                    <select
                                        required
                                        data-selection-css-class="validate"
                                        class="form-control mw-100"
                                        name="department_id"
                                        id="department">
                                        <option value="">-- select department --</option>
                                        <?php foreach($departments as $d): ?>
                                        <option value="<?= $d['id'] ?>" <?= $d['id'] == $inputs['department_id'] ? 'selected' : '' ?>><?= $d['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="working_company_id"><?= trans('Working Company') ?>:</label>
                            <div>
                                <select
                                    data-selection-css-class="validate"
                                    class="form-control mw-100"
                                    name="working_company_id" id="working_company_id">
                                    <option value="">-- select company --</option>
                                    <?php foreach($companies as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= $c['id'] == $inputs['working_company_id'] ? 'selected' : '' ?>><?= $c['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group">
                                <label for="employees"><?= trans('Employees') ?>:</label>
                                <div>
                                    <select
                                        style="line-height: 1.5"
                                        class="form-control mw-100"
                                        name="employee_id[]"
                                        id="employees"
                                        multiple
                                        data-placeholder="-- all employees --">
                                        <?php foreach($employees as $e): ?>
                                        <option <?= $e['_selected'] ?>
                                            value="<?= $e['id'] ?>"
                                            data-department="<?= $e['department_id'] ?>"
                                            data-is-active="<?= $e['is_active'] ?>" data-working_company="<?= $e['working_company_id'] ?>" ><?= $e['formatted_name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group required">
                                <label for="daterange_picker"><?= trans('Date') ?>:</label>
                                <div 
                                    id="daterange_picker"
                                    class="input-group input-daterange"
                                    data-parsley-max-days="31"
                                    data-parsley-class-handler="#daterange_picker"
                                    data-parsley-validate-if-empty
                                    data-date-format="<?= getDateFormatForBSDatepicker() ?>"
                                    data-date-autoclose="true">
                                    <input 
                                        required
                                        type="text" 
                                        name="from" 
                                        id="from"
                                        class="form-control"
                                        autocomplete="off"
                                        placeholder="d-MMM-yyyy"
                                        value="<?= sql2date($inputs['from']) ?>">
                                    <div class="input-group-text input-group-addon px-4 rounded-0 border-left-0 border-right-0">to</div>
                                    <input
                                        required
                                        type="text" 
                                        name="till" 
                                        id="till"
                                        class="form-control"
                                        autocomplete="off"
                                        value="<?= sql2date($inputs['till']) ?>"
                                        placeholder="d-MMM-yyyy">
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <button
                                type="submit"
                                id="view_shifts"
                                class="btn btn-primary mx-3 shadow-none"><?= trans('Get Shifts') ?>
                            </button>
                            <button type="button"
                                id="print_shifts"
                                class="btn btn-warning mx-3 shadow-none"><?= trans('Print Shifts') ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <input type="hidden" id="can_edit_shift" value="<?= intval(user_check_access('HRM_EDITSHIFT')) ?>">
        <input type="hidden" id="default_weekends" value="<?= $GLOBALS['SysPrefs']->prefs['weekends'] ?>">
        <div class="card mx-4 mb-3">
            <div class="card-body">
                <h3 class="card-title mb-3">
                    <span
                        class="mx-3 la la-arrows-alt text-info mb-2"
                        style="cursor:pointer;"
                        data-bs-toggle="collapse"
                        data-bs-target="#shift_master"
                        aria-expanded="true"
                        aria-controls="collapseExample">    
                    </span><?= trans('All Shifts') ?>
                </h3>
                <div class="bg-light">
                    <hr>
                    <div class="collapse" id="shift_master">
                        <div class="row justify-content-around">
                        <?php foreach(array_chunk($shifts, 4) as $_shifts): ?>
                            <div class="col-auto">
                                <table class="table w-auto table-custom">
                                    <thead>
                                        <tr>
                                            <th>Code</th>
                                            <th>Description</th>
                                            <th>From</th>
                                            <th>Till</th>
                                            <th>Color</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach($_shifts as $shift): ?>
                                        <tr
                                            data-shift-row
                                            data-shift-id="<?= $shift['id'] ?>">
                                            <td data-shift-code><?= $shift['code'] ?></td>
                                            <td data-shift-desc><?= $shift['description'] ?></td>
                                            <td
                                                data-shift-from
                                                data-time="<?= $shift['from'] ?>">
                                                <?= (new DateTime($shift['from']))->format('h:i A'); ?>
                                            </td>
                                            <td
                                                data-shift-till
                                                data-time="<?= $shift['till'] ?>">
                                                <?= (new DateTime($shift['till']))->format('h:i A'); ?>
                                            </td>
                                            <td <?= 
                                                empty($shift['color']) 
                                                    ? ''
                                                    : "style=\"background-color:{$shift['color']};\"" 
                                                ?>
                                                data-shift-color><?= $shift['color'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    </div>
                    <hr>
                </div>

                <!-- second section div -->
                <div class="card mx-4 mb-3 mr-auto">
                    <div class="card-body">
                        <div class="row pt-3 align-items-center">
                            <div class="col-lg-2 mt-3">
                                <div class="form-group required form-row">
                                    <label class="col-form-label"><?= trans('Shift') ?>:</label>
                                    <div class="ms-3" id="shiftSelector">
                                        <select class="custom-select" id="group_shift_select">
                                            <option value=""> -- all shifts -- </option>
                                            <option value="off">Off</option>
                                        </select> 
                                     </div>
                                </div>
                            </div>
                            <!-- date  start-->
                            <div class="col-lg-2 mt-3">
                                <div class="form-group form-row">
                                    <label class="col-form-label" for="daterange_picker_2"><?= trans('Date') ?>:</label>
                                    <div 
                                        id="daterange_picker_2"
                                        class="input-group input-daterange col ms-3"
                                        data-date-format="<?= getDateFormatForBSDatepicker() ?>"
                                        data-date-autoclose="true">
                                        <input 
                                            required
                                            type="text" 
                                            id="group_shift_from"
                                            class="form-control"
                                            autocomplete="on"
                                            placeholder="d-MMM-yyyy"
                                            value="<?= sql2date($inputs['from']) ?>">
                                        <div class="input-group-text input-group-addon px-4 rounded-0 border-left-0 border-right-0">to</div>
                                        <input
                                            required
                                            type="text" 
                                            id="group_shift_till"
                                            class="form-control"
                                            autocomplete="on"
                                            placeholder="d-MMM-yyyy"
                                            value="<?= sql2date($inputs['till']) ?>">
                                    </div>
                                </div>
                            </div>
                            <!-- date ends -->
                            <div class="col-lg-2 mt-3">
                                <div class="form-group form-row">
                                    <div class="col">
                                        <button type="button" id="assign_shift" class="btn btn-primary mx-3 shadow-none"><?= trans('Assign  shift to selected employees') ?></button>
                                    </div>  
                                </div>
                            </div>
                            <div class="col-lg-2 offset-lg-4 mt-3 text-end">
                                <div class="form-group form-row">
                                    <div class="col">
                                        <button type="button" id="copy_shift" class="btn btn-instagram mx-3 shadow-none" disabled>
                                            <span class="fa fa-copy"></span>
                                            <span><?= trans('Copy shift from previous dates') ?></span>
                                        </button>
                                    </div>  
                                </div>
                            </div>
                        </div> 
                    </div> 
                </div>
                 <!-- second section  end-->
                <form
                    action="?upsert_shifts="
                    method="post"
                    id="emp_shifts_form">
                    <div class="table-responsive mt-4">
                        <table
                            class="text-center w-auto table table-bordered"
                            id="emp_shifts_tbl">
                            <!- Will generate dynamically -->
                        </table>
                    </div>
                    <div class="row mt-3">
                        <div class="col-auto">
                            <button
                                type="submit"
                                id="save_shifts_btn"
                                disabled
                                title="Save Shifts"
                                class="btn btn-primary mx-3 shadow-none p-4">
                                <span class="la la-save align-center mx-3">
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<form id="copyShiftForm">
    <div
        class="modal fade"
        id="copyShiftModal"
        tabindex="-1"
        data-bs-backdrop="static"
        data-bs-keyboard="false"
        aria-labelledby="copyShiftModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="copyShiftModalLabel">Copy Shift</h2>
                    <button type="button" class="btn shadow-none" data-bs-dismiss="modal" aria-label="Close">
                        <span class="la la-close"></span>
                    </button>
                </div>

                <div class="modal-body p-10 bg-light">
                    <div class="row">
                        <div class="col-lg-2">
                            <div class="form-group form-row">
                                <div class="col">
                                    <label for="copy_from">Copy From:</label>
                                    <div class="input-group">
                                        <input
                                            type="text"
                                            name="copy_from"
                                            data-parsley-trigger-after-failure="change"
                                            data-parsley-date="<?= dateformat('momentJs') ?>"
                                            data-control="bsDatepicker"
                                            data-date-format="<?= dateformat('bsDatepicker') ?>"
                                            data-date-container="#copyShiftModal .modal-body"
                                            data-date-autoclose="true"
                                            value=""
                                            required
                                            id="copy_from"
                                            class="form-control"
                                            autocomplete="off"
                                            placeholder="<?= Carbon::parse('1997-01-01')->format(dateformat()) ?>"
                                            value="">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group form-row">
                                <div class="col">
                                    <label for="upto_weeks">Copy Upto (No. Weeks)</label>
                                    <select
                                        required
                                        name="upto_weeks"
                                        id="upto_weeks"
                                        class="form-control">
                                        <option value=''>--select--</option>
                                        <option value="1">1 Week</option>
                                        <option value="2">2 Weeks</option>
                                        <option value="3">3 Weeks</option>
                                        <option value="4">4 Weeks</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 mt-7">
                            <div class="form-group form-row">
                                <div class="col">
                                    <button type="submit" class="btn btn-success mx-3 shadow-none"><?= trans('View Shifts') ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row bg-white border">
                        <div class="w-100 table-responsive" id="dataTableWrapper">
                            <table
                                id="copyShiftsTable"
                                data-control="dataTable"
                                class="table table-striped table-bordered g-3 text-nowrap thead-strong mh-500px">
                            </table>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary mx-3 shadow-none" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="proceedToCopy" class="btn btn-youtube mx-3 shadow-none"><?= trans('Proceed & Copy') ?></button>
                </div>
            </div>
        </div>
    </div>
</form>

<?php ob_start(); ?>
<script>
    const constants = {
        OFF_COLOR_CODE: '<?= Shift::OFF_COLOR_CODE ?>',
        MOMENT_JS_DATE_FORMAT: '<?= getDateFormatForMomentJs(); ?>'
    }
</script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/toastr/build/toastr.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/parsley/parsley.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/parsley/extra/validator/date.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/sweetalert2/dist/sweetalert2.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/hrm/js/emp_shift.js?id=v1.2.1" type="text/javascript"></script>
<?php $GLOBALS['__FOOT__'][] = ob_get_clean(); end_page(); ?>