<?php

$path_to_root = "../..";
$page_security = 'HRM_VIEW_END_OF_SERVICE';

require_once $path_to_root . "/includes/session.inc";
require_once $path_to_root . "/hrm/helpers/endOfServiceReportHelpers.php";

[
    "employee_id" => $employeeId
] = EndOfServiceReportHelpers::getValidatedInputs();

$employees = getEmployees([
    "status" => ES_ALL,
    "not_status" => ES_ACTIVE
]);
$renderedHtml = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $renderedHtml = EndOfServiceReportHelpers::renderEndOfService($employeeId);
    if ($_POST['action'] == 'print_end_of_service') {
        return EndOfServiceReportHelpers::handlePrintEndOfServiceRequest($renderedHtml, $employeeId);
    }
}

?>
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

page(trans('End of Service Report - HRM'), false, false, '', '', false, '', true);

?>
<div id="_content" class="text-dark">
    <div class="card mx-5">
        <form method="POST" action="" class="w-100" id="filter_form">
            <div class="card mx-4 mb-3">
                <div class="card-body">
                    <div class="row pt-3 align-items-center">
                        <div class="col-auto">
                            <div class="form-group required">
                                <label for="employee_id"><?= trans('Employees') ?>:</label>
                                <div>
                                    <select required class="custom-select mw-100" name="employee_id" id="employee_id">
                                        <option value="">-- all employees --</option>
                                        <?php foreach ($employees as $emp) : ?>
                                            <option value="<?= $emp['id'] ?>" <?= $emp['id'] == $employeeId ? 'selected' : '' ?>><?= $emp['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <button type="submit" id="view_end_of_service" name="action" value="view_end_of_service" class="btn btn-primary mx-3 shadow-none"><?= trans('View End of Service') ?></button>
                            <button type="submit" id="print_end_of_service" name="action" value="print_end_of_service" class="btn btn-primary mx-3 shadow-none"><?= trans('Print End of Service') ?></button>
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
<script>
    $(function () {
        // Initialise the select2s
        $('#employee_id').select2();
    });
</script>
<?php $GLOBALS['__FOOT__'][] = ob_get_clean();
end_page(); ?>