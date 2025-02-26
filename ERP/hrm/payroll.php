<?php

use App\Models\Hr\Company;
use Illuminate\Support\Arr;

$path_to_root = "..";
$page_security = 'HRM_PAYROLL';

require_once $path_to_root . "/includes/session.inc";
require_once $path_to_root . "/hrm/db/attendance_metrics_db.php";
require_once $path_to_root . "/hrm/db/emp_salary_details_db.php";
require_once $path_to_root . "/hrm/db/employees_db.php";
require_once $path_to_root . "/hrm/db/pay_elements_db.php";
require_once $path_to_root . "/hrm/db/payslip_details_db.php";
require_once $path_to_root . "/hrm/db/payslip_elements_db.php";
require_once $path_to_root . "/hrm/db/payslips_db.php";
require_once $path_to_root . "/hrm/db/shifts_db.php";
require_once $path_to_root . "/hrm/db/departments_db.php";
require_once $path_to_root . "/hrm/db/payrolls_db.php";
require_once $path_to_root . "/hrm/helpers/attendanceMetricsHelpers.php";
require_once $path_to_root . "/hrm/helpers/payrollHelpers.php";

$inputs = PayrollHelpers::getValidatedInputs();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['process_payroll'])) {
        PayrollHelpers::HandleProcessPayrollRequest($inputs);
    }

    if (isset($_POST['process_payslips'])) {
        return PayrollHelpers::HandleProcessPayslipsRequest($inputs);
    }

    if (isset($_POST['redo_payslip'])) {
        return PayrollHelpers::HandleRedoPayslipRequest($inputs);
    }

    if (isset($_POST['post_gl'])) {
        return PayrollHelpers::HandlePostToGlRequest($inputs);
    }

    $filters = Arr::except($inputs, ['year', 'month']);
    $result = PayrollHelpers::getPayroll(
        $inputs['year'],
        $inputs['month'],
        isset($_POST['export_wps']) ? [] : $filters
    );

    if (isset($result['error'])) {
        echo json_encode([
            "status" => 422,
            "message" => $result['error'],
            "data" => null
        ]);
        exit();
    }

    // check if this is a request to export to excel for WPS system.
    if (isset($_POST['export_wps'])) {
        if (!$result['payroll']['is_processed']) {
            echo json_encode([
                "status" => 422,
                "message" => "This payroll is not fully processed yet. Please finalize the payroll before exporting",
                "data" => null
            ]);
            exit();
        }
        PayrollHelpers::exportPayrollForWPS($result, $_POST['visa_company_mol_id'] ?? '');
        exit();
    }

    // check if this is a request to export to excel
    if (isset($_POST['export_payroll'])) {
        PayrollHelpers::exportPayroll($result, $filters);
        exit();
    };

    $result['activeFilters'] = $inputs;
    echo json_encode([
        "status"    => 200,
        "data"      => $result,
        "filters"   => http_build_query($inputs)
    ]);
    exit();
}

ob_start(); ?>
<link href="<?= $path_to_root ?>/../assets/plugins/general/toastr/build/toastr.css" rel="stylesheet" type="text/css"/>
<link href="<?= $path_to_root ?>/../assets/plugins/custom/parsley/parsley.css" rel="stylesheet" type="text/css"/>
<link href="<?= $path_to_root ?>/../assets/plugins/general/sweetalert2/dist/sweetalert2.css" rel="stylesheet" type="text/css"/>
<style>
  .table-custom td, .table-custom th {
    padding: 0.3rem 0.75rem;
  }

  .payroll-input {
    display: block;
    width: 8rem;
    margin-right: auto;
    margin-left: auto;
    font-size: 1rem;
    font-weight: 400;
    line-height: 1.5;
    color: #495057;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid #e2e5ec;
    height: calc(1.5em + 1rem + 2px);
    padding: 0.5rem 1rem;
    border-radius: 0.2rem;
  }

  .payroll-input:disabled, .payroll-input[readonly] {
    background-color: #e9ecef;
    opacity: 1;
  }

  .modified:after {
     content: '*';
    color: #888;
    font-size: 2rem;
    position: absolute;
    top: -.5rem;
    left: 0rem;
    display: block;
  }

  #payroll_tbl th {
    font-weight: bold;
    border-bottom-width: 5px;
  }
  #payroll_tbl td {
    position: relative;
    vertical-align: middle;
  }

  #payroll_tbl tr {
    position: relative;
  }

  /* If payslip is processed add green tint to it*/
  #payroll_tbl tr[data-payslip]:nth-child(odd)[data-is-processed="true"] td {
    background:
        linear-gradient(rgba(181, 14, 14, 0.17), rgba(181, 14, 14, 0.17)),
        linear-gradient(#f7f8fa, #f7f8fa);
  }

  /* If payslip is processed add green tint to it*/
  #payroll_tbl tr[data-payslip]:nth-child(even)[data-is-processed="true"] td {
    background:
        linear-gradient(rgba(181, 14, 14, 0.17), rgba(181, 14, 14, 0.17)),
        linear-gradient(#fff, #fff);
  }

  /* If payroll is finalized add pink tint to it*/
  #payroll_tbl[data-is-processed="true"]  tr[data-payslip]:nth-child(odd) td {
      background: 
        linear-gradient(rgba(253, 39, 235, 0.1), rgba(253, 39, 235, 0.1)),
        linear-gradient(rgba(14, 181, 151, 0.1), rgba(14, 181, 151, 0.1)),
        linear-gradient(#f7f8fa, #f7f8fa);
  }

  /* If payroll is finalized add pink tint to it*/
  #payroll_tbl[data-is-processed="true"]  tr[data-payslip]:nth-child(even) td {
      background:
        linear-gradient(rgba(253, 39, 235, 0.1), rgba(253, 39, 235, 0.1)),
        linear-gradient(rgba(14, 181, 151, 0.1), rgba(14, 181, 151, 0.1)),
        linear-gradient(#fff, #fff);
  }

  #payroll_tbl .bg-pel-fixed { background-color: #f0f8ff; }
  #payroll_tbl .bg-pel-ded   { background-color: #fff0f0; }
  #payroll_tbl .bg-pel-alw   { background-color: #f2fffb; }

  .table-responsive {
      max-height: 600px;
      overflow-y: auto;
  }

  #payroll_tbl thead th {
    z-index: 1;
    position: sticky;
    top: 0;
    background: white;
  }

  #payroll_tbl tbody tr:nth-child(odd) td {
    background: #f7f8fa;
  }

  #payroll_tbl tbody tr:nth-child(even) td {
    background: #fff;
  }

  #payroll_tbl thead th:nth-child(3) {
    position: sticky;
    left: 0;
    z-index: 2;
  }

  #payroll_tbl tbody td:nth-child(3) {
    position: sticky;
    left: 0;
    z-index: 1;
  }

  .vr {
    display: inline-block;
    margin-left: 0.3rem;
    margin-right: 0.3rem;
    width: 1px;
    min-height: 2.5em;
    background-color: currentColor;
    opacity: 0.25;
    margin-top: auto;
    vertical-align: middle;
    margin-bottom: auto;
  }
</style>
<?php $GLOBALS['__HEAD__'][] = ob_get_clean();

page(trans('Process Payroll - HRM'), false, false, '', '', false, '', true); 

$departments = getDepartments();
$companies = Company::usedWorkingCompanies()->orderBy('name')->get();
$visaCompanies = Company::where('mol_id', '!=', '')->distinct('mol_id')->get();

$currentYear = date('Y');
$years = range($currentYear - 5, $currentYear);
$months = array_map(function($month) {
    return [
        "id" => $month,
        "name" => (new DateTime())->setDate(1970, $month, 1)->format('M')
    ];
}, range(1, 12));

?>
<div id="_content" class="text-dark">
    <div class="card mx-5">
        <form
            method="POST"
            action=""
            class="w-100"
            id="filter_form">
            <div class="card mx-4 mb-3">
                <div class="card-body">
                    <div class="row pt-3 align-items-center">
                        <div class="col-auto">
                            <div class="form-group required">
                                <label for="year"><?= trans('Year') ?>:</label>
                                <div>
                                    <select
                                        required
                                        class="custom-select mw-100"
                                        name="year" id="year">
                                        <option value="">-- select year --</option>
                                        <?php foreach($years as $year): ?>
                                        <option value="<?= $year ?>" <?= $year == $inputs['year'] ? 'selected' : '' ?>><?= $year ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="form-group required">
                                <label for="month"><?= trans('Month') ?>:</label>
                                <div>
                                    <select
                                        required
                                        class="custom-select mw-100"
                                        name="month"
                                        id="month">
                                        <option value="">-- select month --</option>
                                        <?php foreach($months as $month): ?>
                                        <option <?= $month['id'] == $inputs['month'] ? 'selected' : '' ?>
                                            value="<?= $month['id'] ?>"><?= $month['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group">
                                <label for="department_id"><?= trans('Department') ?>:</label>
                                <div>
                                    <select
                                        class="custom-select mw-100"
                                        name="department_id" id="department_id">
                                        <option value="">-- all departments --</option>
                                        <?php foreach($departments as $d): ?>
                                        <option value="<?= $d['id'] ?>" <?= $d['id'] == $inputs['department_id'] ? 'selected' : '' ?>><?= $d['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group">
                                <label for="working_company_id"><?= trans('Working Company') ?>:</label>
                                <div>
                                    <select
                                        class="custom-select mw-100"
                                        name="working_company_id" id="working_company_id">
                                        <option value="">-- all companies --</option>
                                        <?php foreach($companies as $c): ?>
                                        <option value="<?= $c->id ?>" <?= $c->id == $inputs['working_company_id'] ? 'selected' : '' ?>><?= $c->name ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto text-nowrap">
                            <button type="submit" id="proceed" class="btn btn-primary mx-3 shadow-none"><?= trans('Proceed') ?></button>
                            <button
                                type="button"
                                data-exports="xls"
                                class="btn btn-outline-primary mx-3 shadow-none"
                                data-method="export_payroll"
                                title="Export to Excel - Payroll Format">
                                <span class="la la-external-link"></span>
                            </button>
                            <select
                                data-exports="xls"
                                class="custom-select mx-3 mt-1 mw-200px"
                                data-method="export_wps"
                                title="Export to Excel - WPS Format">
                                <option value="">Download WPS Excel</option>
                                <?php foreach($visaCompanies as $c): ?>
                                <option value="<?= $c->mol_id ?>"><?= "{$c->name} - {$c->mol_id}" ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <input
                type="hidden"
                id="can_redo_payslip"
                value="<?= intval(user_check_access('HRM_REDO_PAYSLIP')) ?>">
        </form>

        <div class="card mx-4 mb-3">
            <div class="card-body">
                <h3 class="card-title mb-3">
                    <?= trans('Payroll') ?>
                    <?php if (user_check_access('HRM_FINALIZE_PAYROLL')): ?>
                    <button
                        disabled
                        id="finalize_payroll"
                        value="process_payroll"
                        name="process_payroll"
                        type="button"
                        style="font-size: 2rem"
                        class="btn shadow-none btn-success float-right display-5">
                        Finalize the Payroll
                        <span class="ml-2 la la-check" style="font-size: 2rem"></span>
                    </button>
                    <button
                        disabled
                        id="post_gl"
                        value="post_gl"
                        name="post_gl"
                        type="button"
                        style="font-size: 2rem"
                        class="btn shadow-none btn-success float-right mr-3 display-5">
                        Post To GL
                        <span class="ml-2 la la-check-double" style="font-size: 2rem"></span>
                    </button>
                    <?php endif; ?>
                </h3>
                <div class="table-responsive">
                    <table
                        class="text-center w-100 table-custom table text-nowrap"
                        id="payroll_tbl">
                        <!- Will generate dynamically -->
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script src="<?= $path_to_root ?>/../assets/plugins/general/toastr/build/toastr.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/parsley/parsley.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/sweetalert2/dist/sweetalert2.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/hrm/js/payroll.js?v1.2.0" type="text/javascript"></script>
<?php $GLOBALS['__FOOT__'][] = ob_get_clean(); end_page();?>