<?php

use App\Http\Controllers\Hr\PayslipController;
use App\Models\Hr\Company;
$path_to_root = "..";
$page_security = 'HRM_VIEWPAYSLIP';

require_once $path_to_root . "/includes/session.inc";
require_once $path_to_root . "/hrm/db/employees_db.php";
require_once $path_to_root . "/hrm/db/pay_elements_db.php";
require_once $path_to_root . "/hrm/db/payslip_elements_db.php";
require_once $path_to_root . "/hrm/db/payslips_db.php";
require_once $path_to_root . "/hrm/db/payrolls_db.php";
require_once $path_to_root . "/hrm/helpers/payslipHelpers.php";
require_once $path_to_root . "/hrm/db/departments_db.php";

$canAccess = [
    'OWN' => user_check_access('HRM_VIEWPAYSLIP_OWN'),
    'DEP' => user_check_access('HRM_VIEWPAYSLIP_DEP'),
    'ALL' => user_check_access('HRM_VIEWPAYSLIP_ALL')
];
$page_security = in_array(true, $canAccess, true) ? 'SA_ALLOW' : 'SA_DENIED'; 

$currentEmployee = getCurrentEmployee();
$currentEmployeeId = $currentEmployee['id'] ?? -1;
$inputs = PayslipHelpers::getValidatedInputs($currentEmployee);
$payrollId = $inputs['payroll_id'] ?? -1;
$selected_employee = $inputs['employee_id'] ?? -1;

$renderedHtml = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($selected_employee) {
        $renderedHtml = app(PayslipController::class)->render($payrollId, $selected_employee);
    }
}

ob_start(); ?>
<link href="<?= $path_to_root ?>/../assets/plugins/general/toastr/build/toastr.css" rel="stylesheet" type="text/css" />
<link href="<?= $path_to_root ?>/../assets/plugins/custom/parsley/parsley.css" rel="stylesheet" type="text/css" />
<link href="<?= $path_to_root ?>/../assets/plugins/general/sweetalert2/dist/sweetalert2.css" rel="stylesheet" type="text/css" />
<style>
    .table-custom td,
    .table-custom th {
        padding: 0.3rem 0.75rem;
    }
</style>
<?php $GLOBALS['__HEAD__'][] = ob_get_clean();

page(trans('Payslip Report - HRM'), false, false, '', '', false, '', true);

$departments = getAuthorizedDepartments($canAccess, $currentEmployeeId)->fetch_all(MYSQLI_ASSOC);
$employees = getAuthorizedEmployees($canAccess, $currentEmployeeId);
$companies = Company::orderBy('name')->get();
?>
<div id="_content" class="text-dark">
    <div class="card mx-5">
        <form method="POST" action="" class="w-100" id="filter_form">
            <div class="card mx-4 mb-3">
                <div class="card-body">
                    <div class="row pt-3 align-items-center">
                        <div class="col-auto">
                            <div class="form-group required">
                                <label for="payroll_id"><?= trans('Payrolls') ?>:</label>
                                <div>
                                    <select required class="custom-select mw-100" name="payroll_id" id="payroll_id">
                                        <option value="">-- select payroll --</option>
                                        <?php foreach (getPayrollsKeyedByID(['is_processed' => true]) as $id => $payroll) : ?>
                                            <option value="<?= $id ?>" <?= $id == $payrollId ? 'selected' : '' ?>><?= $payroll['custom_id'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group ">
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
                        <div class="col-lg-2">
                            <div class="form-group ">
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
                            <div class="form-group required">
                                <label for="employees"><?= trans('Employees') ?>:</label>
                                <div>
                                    <select
                                        style="line-height: 1.5"
                                        class="form-control mw-100"
                                        name="employee_id"
                                        id="employees"
                                        data-placeholder="-- all employees --">
                                        <?php foreach($employees as $e): ?>
                                            <option 
                                                value="<?= $e['id'] ?>" <?= $e['id'] == $inputs['employee_id'] ? 'selected' : '' ?>
                                                data-department="<?= $e['department_id'] ?>" data-working_company="<?= $e['working_company_id'] ?>"><?= $e['formatted_name'] ?>
                                            </option>                                       
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <button type="submit" id="view_payslip" name="action" value="view_payslip" class="btn btn-primary mx-3 shadow-none"><?= trans('View Payslip') ?></button>
                            <button type="button" id="print_payslip" name="action" value="print_payslip" class="btn btn-primary mx-3 shadow-none"><?= trans('Print Payslip') ?></button>
                        </div>
                    </div>
                </div>
        </form>

        <div class="table-responsive mx-auto" style="width: 1000px;">
            <?php if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                echo $renderedHtml;
            } ?>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script src="<?= $path_to_root ?>/../assets/plugins/general/toastr/build/toastr.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/parsley/parsley.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/sweetalert2/dist/sweetalert2.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/hrm/js/payslip_001.js" type="text/javascript"></script>
<script>
route.push('payslip.print', '<?= rawRoute('payslip.print') ?>')
$(function () {
    const parsleyForm = $('#filter_form').parsley();
    $('#print_payslip').on('click', function () {
        parsleyForm.whenValidate({force: true}).then(function () {
            let url = route('payslip.print', {
                'payroll': document.getElementById('payroll_id').value,
                'employee': document.getElementById('employees').value
            });
            setTimeout(function () { createPopup(url); })
        })
    })
})
</script>
<?php $GLOBALS['__FOOT__'][] = ob_get_clean(); end_page(); ?>