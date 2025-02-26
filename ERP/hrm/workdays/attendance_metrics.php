<?php

use App\Models\Hr\Company;

$path_to_root = "../..";

require_once $path_to_root . "/includes/session.inc";
require_once $path_to_root . "/hrm/db/employees_db.php";
require_once $path_to_root . "/hrm/db/emp_salary_details_db.php";
require_once $path_to_root . "/hrm/db/shifts_db.php";
require_once $path_to_root . "/hrm/db/departments_db.php";
require_once $path_to_root . "/hrm/db/attendance_metrics_db.php";
require_once $path_to_root . "/hrm/helpers/attendanceMetricsHelpers.php";

$canAccess = [
    "OWN" => user_check_access('HRM_VIEWATDMETRICS_OWN'),
    "DEP" => user_check_access('HRM_VIEWATDMETRICS_DEP'),
    "ALL" => user_check_access('HRM_VIEWATDMETRICS_ALL')
];
$canModify = [
    "ALL_BUT_OWN" => user_check_access('HRM_MODIFYATDMETRICS'),
    "OWN" => user_check_access('HRM_MODIFYATDMETRICS_OWN')
];
$page_security = in_array(true, $canAccess, true) ? 'SA_ALLOW' : 'SA_DENIED';

$currentEmployee = getCurrentEmployee();
$currentEmployeeId = $currentEmployee['id'] ?? -1;
$inputs = AttendanceMetricsHelpers::getValidatedInputs($currentEmployee, $canAccess);

if (isset($_POST['update_metric'])) {
    return AttendanceMetricsHelpers::handleUpdateMetricRequest($canModify, $currentEmployeeId);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $metrics = AttendanceMetricsHelpers::getMetrics(
        $canAccess,
        $currentEmployeeId,
        $inputs
    );

    // Add if updatable by this employee or not
    array_walk($metrics, function(&$_metrics, $empId) use ($canModify, $currentEmployeeId) {
        $isUpdatable = $canModify["ALL_BUT_OWN"] && ($canModify["OWN"] || $empId != $currentEmployeeId);
        
        array_walk($_metrics, function(&$metric) use ($isUpdatable){
            AttendanceMetricsHelpers::injectAdditionalFields($metric, $isUpdatable);
        });
    });

    $inputs['from'] = sql2date($inputs['from']);
    $inputs['till'] = sql2date($inputs['till']);
    echo json_encode([
        "status"    => 200,
        "data"      => $metrics,
        "filters"   => http_build_query($inputs)
    ]);
    exit();
}

ob_start(); ?>
<link href="<?= $path_to_root ?>/../assets/plugins/custom/datatables.net-bs4/css/dataTables.bootstrap4.css" rel="stylesheet" type="text/css"/>
<link href="<?= $path_to_root ?>/../assets/plugins/custom/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css"/>
<link href="<?= $path_to_root ?>/../assets/plugins/custom/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css"/>

<link href="<?= $path_to_root ?>/../assets/plugins/general/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css" rel="stylesheet" type="text/css"/>
<link href="<?= $path_to_root ?>/../assets/plugins/general/toastr/build/toastr.css" rel="stylesheet" type="text/css"/>
<link href="<?= $path_to_root ?>/../assets/plugins/custom/parsley/parsley.css" rel="stylesheet" type="text/css"/>
<style>
    #employee {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
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

    #employee + .select2-container--default .select2-selection--single {
        border: 0;
    }

    .select2-container--default .select2-selection--single  button.select2-selection__clear {
        box-shadow: none;
        height: calc(1.5rem + 1.3rem);
        font-size: 1.5rem;
        color: #74788d;
        padding-right: 0.5rem;
        padding-top: 0.65rem;
        padding-bottom: 0.65rem;
    }

    button {
        box-shadow: none;
    }

    .dataTables_length {
        float: left;
    }

    #emp_attd_metrics_tbl th {
        font-weight: bold;
    }

    .table-custom td, .table-custom th {
        padding: 0.3rem 0.75rem;
    }
</style>
<?php $GLOBALS['__HEAD__'][] = ob_get_clean();

page(trans('Attendance Metric Analysis'), false, false, "", "", false, '', true);

$departments = getAuthorizedDepartments($canAccess, $currentEmployee['id'] ?? -1)->fetch_all(MYSQLI_ASSOC);
$companies = Company::all()->sortBy('name');
$employees = getAuthorizedEmployeesKeyedById($canAccess, $currentEmployee['id'] ?? -1);

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
                            <div class="form-group">
                                <label for="working_company_id"><?= trans('Working Company') ?>:</label>
                                <div>
                                    <select
                                        class="form-control mw-100"
                                        name="working_company_id" id="working_company_id">
                                        <option value="">-- select working company --</option>
                                        <?php foreach($companies as $c): ?>
                                        <option value="<?= $c->id ?>" <?= $c->id == $inputs['working_company_id'] ? 'selected' : '' ?>><?= $c->name ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group">
                                <label for="department"><?= trans('Department') ?>:</label>
                                <div>
                                    <select
                                        class="form-control mw-100"
                                        name="department_id" id="department_id">
                                        <option value="">-- select department --</option>
                                        <?php foreach($departments as $d): ?>
                                        <option value="<?= $d['id'] ?>" <?= $d['id'] == $inputs['department_id'] ? 'selected' : '' ?>><?= $d['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label for="employee"><?= trans('Employees') ?>:</label>
                                <div class="input-group">
                                    <div class="form-control p-0 overflow-hidden">
                                        <select
                                            class="form-control mw-100 w-100"
                                            name="employees"
                                            id="employee"
                                            data-placeholder="-- all employees --">
                                            <?php foreach($employees as $e): ?>
                                            <option
                                                value="<?= $e['id'] ?>"
                                                data-department-id="<?= $e['department_id'] ?>"
                                                data-working-company-id="<?= $e['working_company_id'] ?>"
                                                data-is-active="<?= $e['is_active'] ?>"><?= $e['formatted_name'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="input-group-append">
                                        <div class="input-group-text">
                                            <div class="form-check form-check-inline">
                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    id="show_inactive"
                                                    name="show_inactive"
                                                    value="1">
                                                <label class="form-check-label" for="show_inactive">Show inactive employees</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group required">
                                <label for="daterange_picker"><?= trans('Date') ?>:</label>
                                <div 
                                    id="daterange_picker"
                                    class="input-group input-daterange"
                                    data-parsley-max-days-if-selected-all="employee,31"
                                    data-parsley-class-handler="#daterange_picker"
                                    data-parsley-validate-if-empty
                                    data-date-format="<?= getDateFormatForBSDatepicker() ?>"
                                    data-date-autoclose="true">
                                    <input 
                                        required
                                        type="text"
                                        data-parsley-trigger-after-failure="change"
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
                                        data-parsley-trigger-after-failure="change" 
                                        id="till"
                                        class="form-control"
                                        autocomplete="off"
                                        value="<?= sql2date($inputs['till']) ?>"
                                        placeholder="d-MMM-yyyy">
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group">
                                <label for="type"><?= trans('Type') ?>:</label>
                                <div>
                                    <select
                                        style="line-height: 1.5"
                                        class="custom-select mw-100"
                                        name="type"
                                        id="type">
                                        <option value="">-- all types --</option>
                                        <?php foreach($GLOBALS['attendance_metric_types'] as $type => $desc): ?>
                                        <option value="<?= $type ?>"><?= $desc ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <button
                                type="submit"
                                id="view_metrics"
                                class="btn btn-primary mx-3 shadow-none"><?= trans('Proceed') ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="card mx-4 mb-3">
            <div class="card-body">
                <h3 class="card-title mb-3"><?= trans('Metric Analysis') ?></h3>

                <div class="mt-4">
                    <table
                        class="w-100 table table-custom table-bordered table-striped"
                        id="emp_attd_metrics_tbl">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Working Company</th>
                                <th>Department</th>
                                <th>Name</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Minutes</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Reviewed By</th>
                                <th>Reviewed At</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <!- Will generate dynamically -->
                        </tbody>
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

<script src="<?= $path_to_root ?>/../assets/plugins/custom/datatables.net/js/jquery.dataTables.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/custom/datatables.net-bs4/js/dataTables.bootstrap4.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/custom/datatables.net-buttons/js/dataTables.buttons.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/custom/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/custom/jszip/dist/jszip.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/custom/datatables.net-buttons/js/buttons.html5.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/custom/datatables.net-responsive/js/dataTables.responsive.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/custom/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js" type="text/javascript"></script>

<script src="<?= $path_to_root ?>/../assets/plugins/general/sweetalert2/dist/sweetalert2.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/js/global/integration/plugins/sweetalert2.init.js" type="text/javascript"></script>
<script>
    var canModify = JSON.parse('<?= json_encode($canModify) ?>');
</script>
<script src="<?= $path_to_root ?>/hrm/js/attendance_metrics.js?v1.0.5" type="text/javascript"></script>
<?php $GLOBALS['__FOOT__'][] = ob_get_clean(); end_page();?>