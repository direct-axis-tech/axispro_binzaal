<?php

use App\Models\Hr\Shift;
use App\Models\Hr\Company;
$path_to_root = "../..";

require_once $path_to_root . "/includes/session.inc";
require_once $path_to_root . "/hrm/helpers/timesheetHelpers.php";
require_once $path_to_root . "/hrm/db/employees_db.php";
require_once $path_to_root . "/hrm/db/departments_db.php";

$canAccess = [
    'OWN' => user_check_access('HRM_VIEWTIMESHEET_OWN'),
    'DEP' => user_check_access('HRM_VIEWTIMESHEET_DEP'),
    'ALL' => user_check_access('HRM_VIEWTIMESHEET_ALL')
];
$canUpdate = [
    "OWN" => user_check_access('HRM_EDITTIMESHEET_OWN'),
    "DEP" => user_check_access('HRM_EDITTIMESHEET_DEP'),
    "ALL" => user_check_access('HRM_EDITTIMESHEET_ALL')
];
$page_security = in_array(true, $canAccess, true) ? 'SA_ALLOW' : 'SA_DENIED'; 

$currentEmployee = getCurrentEmployee();
$currentEmployeeId = $currentEmployee['id'] ?? -1;
$inputs = TimesheetHelpers::getValidatedInputs($currentEmployee, $canAccess);

// If the request wants json just echo the results, No need to process any further.
$wantsJson = request()->wantsJson();
$isExportReqest = isset($_GET['export_timesheet']);
$isSyncronizeRequest = isset($_GET['sync']);
if ($wantsJson || $isExportReqest) {
    $timeSheet = [];
    $groupedWorkRecords = TimesheetHelpers::getTimesheet($canAccess, $canUpdate, $currentEmployeeId, $inputs);

    // If this is a request for exporting to excel, then no need to proceed further.
    if ($isExportReqest) {
        TimesheetHelpers::exportTimeSheet($groupedWorkRecords, $inputs);
        exit();
    }

    if ($isSyncronizeRequest) {
        TimesheetHelpers::syncronizeAttendance($inputs);
        exit();
    }

    $inputs['from'] = sql2date($inputs['from']);
    $inputs['till'] = sql2date($inputs['till']);
    echo json_encode([
        "data"      => $groupedWorkRecords,
        "filters"   => http_build_query($inputs)
    ]);
    exit();
}

// The extra head block
ob_start(); ?>
<link href="<?= $path_to_root ?>/../assets/plugins/general/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css" rel="stylesheet" type="text/css"/>
<link href="<?= $path_to_root ?>/../assets/plugins/general/toastr/build/toastr.css" rel="stylesheet" type="text/css"/>
<link href="<?= $path_to_root ?>/../assets/plugins/custom/parsley/parsley.css" rel="stylesheet" type="text/css"/>
<link href="<?= $path_to_root ?>/../assets/plugins/general/sweetalert2/dist/sweetalert2.css" rel="stylesheet" type="text/css"/>
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
  
  #daterange_picker.is-invalid .form-control,
  #daterange_picker.is-invalid .input-group-addon,
  .form-group.is-invalid .validate {
    border-color: #dc3545;
  }

  .hidden-punches .punch {display: none;}
  .weekend { color: #fff; background-color: <?= Shift::OFF_COLOR_CODE ?>; }
  .badge-duration { color: #fff; background-color: #2786fb; }
  .badge-punchin { color: #fff; background-color: #1dc9b7; }
  .badge-punchout { color: #fff; background-color: #1db9b7; }
  .badge-missed-punch { color: #212529; background-color: #e1e1ef; }
  .badge-punchin, .badge-punchout,
  .badge-duration, .badge-missed-punch, .emp-shift, .on-leave, .public-holiday { 
    display: block;
    margin-left: auto;
    margin-right: auto;
  }

  /* .badge-punchin, .badge-punchout,
  .badge-duration, .badge-missed-punch, .emp-shift {
    width: 145px;
  } */

  .emp-shift, .on-leave, .public-holiday {
    cursor: default;
    border: solid 1px;
    margin-top: 0.3rem;
    border-radius: 0.25rem;
    padding: 0.25rem 0.5rem;
  }

  .emp-shift {
    color: #93B5C6;
  }

  .on-leave {
    color: #212529;
    border-color: #ac30d9;
  }

  .public-holiday {
    color: #009688;
    border-color: #fd27278c;
  }

  .emp-shift, .on-leave, .public-holiday {
      background-color: white;
  }

  .reviewed,
  #timesheet_tbl tbody tr:nth-child(odd) td.reviewed,
  #timesheet_tbl tbody tr:nth-child(even) td.reviewed {
      background-color: #fd27278c;
  }

  .badge-not-present { color: #fff; background-color: #fd27eb; }
  .badge-off { color: #212529; background-color: #ffb822; }
  #timesheet_tbl .emp-workday {
    line-height: 1.75;
    padding-left: 2rem;
    padding-right: 3rem;
  }
  #timesheet_tbl th { font-weight: bold; }
  #timesheet_tbl td { vertical-align: middle; }
  .updatable .clickable { cursor: pointer; }

  #timesheet_tbl thead th {
    position: sticky;
    background-color: white;
    top: -1px;
    box-shadow: inset 0 -1px 1px rgba(0, 0, 0, 0.2);
  }

  #timesheet_tbl thead th.weekend {
    background-color: <?= Shift::OFF_COLOR_CODE ?>;
  }

  #timesheet_tbl tbody tr:nth-child(odd) td {
    background: rgba(245, 248, 250);
  }

  #timesheet_tbl tbody tr:nth-child(even) td {
    background: #fff;
  }

  #timesheet_tbl thead th:nth-child(1) {
    position: sticky;
    left: 0;
    z-index: 2;
  }

  #timesheet_tbl tbody td:nth-child(1) {
    position: sticky;
    left: 0;
    z-index: 1;
  }
</style>
<?php $GLOBALS['__HEAD__'][] = ob_get_clean();

page(trans('Timesheet - HRM'), false, false, "", "", false, '', true);

$departments = getAuthorizedDepartments($canAccess, $currentEmployeeId)->fetch_all(MYSQLI_ASSOC);
$employees = getAuthorizedEmployeesWithSelectedAttribute($canAccess, $currentEmployeeId, $inputs['employee_id']);

$defaultWeekends = $GLOBALS['SysPrefs']->prefs['weekends'] ?: -1;
$defaultWeekends = array_map(function ($index) {
    return (["Mon", "Tue", "Wed", "Fri", "Sat", "Sun"][$index - 1] ?? "");
}, explode(",", $defaultWeekends));
$defaultWeekends = implode(",", $defaultWeekends);
$companies = Company::orderBy('name')->get();
?>
<div id="_content" class="text-dark">
    <?php if ($canUpdate['ALL'] || $canUpdate['DEP'] || $canUpdate['OWN']) : ?>
    <!-- Begin: Modal -->
    <div id="update_attendance_modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Attendance</h5>
                    <button type="button" class="close shadow-none" data-dismiss="modal" data-bs-dismiss="modal"  aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form
                    action="<?= route("attendance.update") ?>"
                    method="POST"
                    id="update_attendace_form">
                    <input type="hidden" name="employee_id" id="employee_id">
                    <input type="hidden" name="date" id="date">
                    <div class="modal-body">
                        <div class="container w-100">
                            <div class="form-group row">
                                <label for="duty_status" class="col-form-label col-2">Status</label>
                                <div class="col-10">
                                    <select id="duty_status" name="status" class="custom-select mw-100" required>
                                        <option value="">-- select --</option>
                                        <option value="present">Present</option>
                                        <option value="not_present">Not Present</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="punchin" class="col-form-label col-2">Punchin</label>
                                <div class="col-10">
                                    <input
                                        type="time"
                                        name="punchin"
                                        step="1"
                                        data-parsley-pattern="(2[0-3]|[01]?[0-9]):([0-5]?[0-9]):([0-5]?[0-9])"
                                        data-parsley-pattern-message="Time must be in HH:mm:ss format Eg. 20:25:67"
                                        data-parsley-required-on-select="status,present"
                                        data-parsley-validate-if-empty
                                        id="punchin"
                                        class="form-control">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="punchout" class="col-form-label col-2">Punchout</label>
                                <div class="col-10">
                                    <input
                                        type="time"
                                        name="punchout"
                                        step="1"
                                        data-parsley-pattern="(2[0-3]|[01]?[0-9]):([0-5]?[0-9]):([0-5]?[0-9])"
                                        data-parsley-pattern-message="Time must be in HH:mm:ss format Eg. 20:25:67"
                                        data-parsley-required-on-select="status,present"
                                        data-parsley-validate-if-empty
                                        id="punchout"
                                        class="form-control">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="punchin2" class="col-form-label col-2">Punchin2</label>
                                <div class="col-10">
                                    <input
                                        type="time"
                                        name="punchin2"
                                        step="1"
                                        data-parsley-pattern="(2[0-3]|[01]?[0-9]):([0-5]?[0-9]):([0-5]?[0-9])"
                                        data-parsley-pattern-message="Time must be in HH:mm:ss format Eg. 20:25:67"
                                        id="punchin2"
                                        class="form-control">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="punchout2" class="col-form-label col-2">Punchout2</label>
                                <div class="col-10">
                                    <input
                                        type="time"
                                        name="punchout2"
                                        step="1"
                                        data-parsley-pattern="(2[0-3]|[01]?[0-9]):([0-5]?[0-9]):([0-5]?[0-9])"
                                        data-parsley-pattern-message="Time must be in HH:mm:ss format Eg. 20:25:67"
                                        data-parsley-required-if="punchin2"
                                        data-parsley-validate-if-empty
                                        id="punchout2"
                                        class="form-control">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="attendance_remarks" class="col-form-label col-2">Remarks</label>
                                <div class="col-10">
                                    <textarea
                                        placeholder="Remarks for this update if any"
                                        name="remarks"
                                        class="form-control"
                                        data-parsley-required-on-select="status,present"
                                        data-parsley-validate-if-empty
                                        id="attendance_remarks"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-dismiss="modal" data-bs-dismiss="modal"  class="btn btn-secondary shadow-none">Cancel</button>
                        <button type="submit" class="btn btn-success shadow-none">Update changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End: Modal -->
    <?php endif; ?>

    <div class="card mx-5">
        <form action="" class="w-100" id="filter_form">
            <input type="hidden" id="default_weekends" value="<?= $defaultWeekends ?>">
            <div class="card mx-4 mb-3">
                <div class="card-body">
                    <div class="row pt-3 align-items-center">
                        <div class="col-lg-2">
                            <div class="form-group">
                                <label for="department"><?= trans('Department') ?>:</label>
                                <div>
                                    <select
                                        data-selection-css-class="validate"
                                        class="form-control mw-100"
                                        name="department_id" id="department">
                                        <option value="">-- select department --</option>
                                        <?php foreach($departments as $d): ?>
                                        <option value="<?= $d['id'] ?>" <?= $d['id'] == $inputs['department_id'] ? 'selected' : '' ?>><?= $d['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-1">
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
                                            data-department="<?= $e['department_id'] ?>" data-working_company="<?= $e['working_company_id'] ?>"
                                            data-is-active="<?= $e['is_active'] ?>"><?= $e['formatted_name'] ?></option>
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
                            <div class="form-check form-check-inline mx-3">
                                <input <?= $inputs['show_punchinouts'] ? 'checked' : '' ?>
                                    class="form-check-input"
                                    type="checkbox"
                                    id="show_punchinouts"
                                    name="show_punchinouts"
                                    value="1">
                                <label class="form-check-label" for="show_punchinouts">Show punchin & punchout</label>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="form-check form-check-inline mx-3">
                                <input <?= $inputs['show_inactive'] ? 'checked' : '' ?>
                                    class="form-check-input"
                                    type="checkbox"
                                    id="show_inactive"
                                    name="show_inactive"
                                    value="1">
                                <label class="form-check-label" for="show_inactive">Show inactive employees</label>
                            </div>
                        </div>
                        <div class="col-auto">
                            <button type="submit" id="view_timesheet" class="btn btn-primary mx-3 shadow-none"><?= trans('View Timesheet') ?></button>
                            <button
                                type="button"
                                id="export_timesheet"
                                class="btn btn-outline-primary mx-3 shadow-none"
                                value="export_timesheet"
                                title="Download Excel">
                                <span class="la la-download"></span>
                            </button>
                            <?php if(user_check_access(App\Permissions::HRM_SYNCATTD)): ?>
                            <button
                                type="button"
                                id="sync"
                                class="btn btn-outline-primary mx-3 shadow-none"
                                name="sync"
                                value="sync"
                                title="Syncronise">
                                <span class="la la-refresh"></span>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="card mx-4 mb-3">
            <div class="card-body">
                <h3 class="card-title mb-3"><?= trans('Time Sheet') ?></h3>
                <div class="table-responsive mh-600px">
                    <table
                        class="text-nowrap text-center w-auto table-sm table table-bordered <?= $inputs['show_punchinouts'] ? '' : 'hidden-punches' ?>"
                        id="timesheet_tbl">
                        <!- Will generate dynamically -->
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script src="<?= $path_to_root ?>/../assets/plugins/general/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/toastr/build/toastr.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/parsley/parsley.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/sweetalert2/dist/sweetalert2.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/hrm/js/timesheet.js?id=v1.1.3" type="text/javascript"></script>
<?php $GLOBALS['__FOOT__'][] = ob_get_clean(); end_page();?>