<?php

use App\Models\Bank;

$path_to_root = "../..";
$page_security = 'HRM_SALARY_CERTIFICATE';

require_once $path_to_root . "/includes/session.inc";
require_once $path_to_root . "/hrm/db/employees_db.php";
require_once $path_to_root . "/hrm/db/banks_db.php";
require_once $path_to_root . "/hrm/helpers/salaryCertificateHelpers.php";

$employeeId = $_POST['employee_id'] ?? '-1';
$selectedAddressee = $_POST['addressee'] ?? '';
$address = $_POST['address'] ?? '';
$hrId = $_POST['hr_id'] ?? '-1';

$employees = getEmployees();
$hrs = getEmployees(['designation_id' => pref('hr.designation_hr', -1)]);
$addressees = Bank::pluck('name')->toArray();
if (!empty($selectedAddressee) && !in_array($selectedAddressee, $addressees)) {
    $addressees[] = $selectedAddressee;
}
$renderedHtml = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_employees = getEmployeesKeyedById(["employee_id" => [$employeeId, $hrId]]);
    $employee = $_employees[$employeeId];
    $hr = $_employees[$hrId];
    $currentDateFormated = date('jS F Y', strtotime(date(DB_DATE_FORMAT)));
    $joiningDateFormated = date('jS F Y', strtotime($employee['date_of_join']));

    $userData = ([
        "ref_no" => $_POST['ref_no'],
        "addressee" => $_POST['addressee'],
        "address" => $address,
        "currentDate" => $currentDateFormated,
        "joiningDate" => $joiningDateFormated,
        "authorized_signatory" => $hr['name']
    ]);

    $renderedHtml = salaryCertificateHelpers::renderSalaryCertificate($employee, $userData);
    if ($_POST['action'] == 'print_salary_certificate') {
        return salaryCertificateHelpers::handlePrintSalaryCertificateRequest($renderedHtml, $employee);
    }
}

$refDate = date(DB_DATE_FORMAT);

?>
<style>
    .table-custom td,
    .table-custom th {
        padding: 0.3rem 0.75rem;
    }
</style>
<?php $GLOBALS['__HEAD__'][] = ob_get_clean();

page(trans('Salary Certificate - HRM'), false, false, '', '', false, '', true);

?>
<div id="_content" class="text-dark">
    <div class="card mx-5">
        <form method="POST" action="" class="w-100" id="filter_form">
            <div class="card mx-4 mb-3">
                <div class="card-body">
                    <div class="row pt-3 align-items-center">
                        <div class="col-lg-2">
                            <div class="form-group required">
                                <label for="employee_id"><?= trans('Employee') ?>:</label>
                                <div>
                                    <select 
                                        required 
                                        class="custom-select mw-100" 
                                        name="employee_id" 
                                        id="employee_id"
                                    >
                                        <option value="">-- all employees --</option>
                                        <?php foreach ($employees as $emp) : ?>
                                            <option value="<?= $emp['id'] ?>" <?= $emp['id'] == $employeeId ? 'selected' : '' ?>><?= $emp['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group required">
                                <label for="ref_no">Ref No.:</label>
                                <div>
                                    <input 
                                        type="text" 
                                        id="ref_no" 
                                        name="ref_no" 
                                        class="form-control" 
                                        placeholder="<?= trans("Ref No.") ?>"
                                        value="<?= e($_POST['ref_no'] ?? '') ?>"
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group required">
                                <label for="addressee"><?= trans('Addressee') ?>:</label>
                                <div>
                                    <select
                                        class="custom-select mw-100" 
                                        name="addressee" 
                                        id="addressee"
                                    >
                                        <option value="">-- select --</option>
                                        <?php foreach ($addressees as $addressee) : ?>
                                            <option value="<?= $addressee ?>" <?= $addressee == $selectedAddressee ? 'selected' : '' ?>><?= $addressee ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group required">
                                <label for="address">Address:</label>
                                <div>
                                    <textarea 
                                        type="text" 
                                        id="address" 
                                        name="address" 
                                        class="form-control" 
                                        placeholder="<?= trans("Address") ?>"><?= $address ?></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group required">
                                <label for="hr_id"><?= trans('Authorized Signatory') ?>:</label>
                                <div>
                                    <select 
                                        required 
                                        class="custom-select mw-100" 
                                        name="hr_id" 
                                        id="hr_id"
                                    >
                                        <option value="">-- all employees --</option>
                                        <?php foreach ($hrs as $emp) : ?>
                                            <option value="<?= $emp['id'] ?>" <?= $emp['id'] == $hrId ? 'selected' : '' ?>><?= $emp['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <button type="submit" id="view_salary_certificate" name="action" value="view_salary_certificate" class="btn btn-primary mx-3 shadow-none"><?= trans('View Salary Certificate') ?></button>
                            <button type="submit" id="print_salary_certificate" name="action" value="print_salary_certificate" class="btn btn-primary mx-3 shadow-none"><?= trans('Print Salary Certificate') ?></button>
                        </div>
                    </div>
                </div>
        </form>

        <div class="table-responsive mx-auto" style="width: 1000px;">
            <?= $_SERVER['REQUEST_METHOD'] == 'POST' ? $renderedHtml : '' ?>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
    $(function () {
        route.push('api.hr.getSalaryCertificateReference', '<?= rawRoute('api.hr.getSalaryCertificateReference') ?>');
        // Initialise the select2s
        $('#employee_id, #hr_id').select2();
        $('#addressee').select2({tags: true});

        $('#employee_id').on('change', (e) => {
            ajaxRequest({
                url: route('api.hr.getSalaryCertificateReference', {employee: e.target.value}),
                method: 'post'
            })
            .then(respJson => $('#ref_no').val(respJson.data))
            .catch(defaultErrorHandler)
        })
    });
</script>
<?php $GLOBALS['__FOOT__'][] = ob_get_clean();
end_page(); ?>