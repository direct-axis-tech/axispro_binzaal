<?php

$path_to_root = '..';
$page_security = 'SA_GRATUITY_ACCRUALS';

require_once $path_to_root . "/includes/session.inc";
require_once $path_to_root . "/hrm/helpers/gratuityAccrualHelpers.php";

$inputs = GratuityAccrualHelpers::getValidatedInputs();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!user_check_access('SA_GRATUITY_ACCRUALS')) {
        http_response_code(403);
        echo json_encode(['status' => 403, 'message' => 'You are not authorized to access this function']);
        exit();
    }

    if (!empty($_POST['action'])) {
        if ($_POST['action'] == 'showDetails') {
            GratuityAccrualHelpers::handleShowDetailsRequest($inputs);
            exit();
        }

        if ($_POST['action'] == 'postGL') {
            GratuityAccrualHelpers::handlePostGLRequest($inputs);
            exit();
        }
    }

    http_response_code(400);
    echo json_encode(['status' => 400, 'message' => 'Bad Request']);
    exit();
}

ob_start(); ?>
<link href="<?= $path_to_root ?>/../assets/plugins/general/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css" rel="stylesheet" type="text/css" />
<link href="<?= $path_to_root ?>/../assets/plugins/custom/parsley/parsley.css" rel="stylesheet" type="text/css" />
<link href="<?= $path_to_root ?>/../assets/plugins/general/toastr/build/toastr.css" rel="stylesheet" type="text/css" />
<link href="<?= $path_to_root ?>/../assets/plugins/general/sweetalert2/dist/sweetalert2.css" rel="stylesheet" type="text/css" />
<link href="<?= erp_url('/v3/plugins/custom/datatables/datatables.bundle.css') ?>" rel="stylesheet" type="text/css" />
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

page(trans('Process Gratuity Accruals'), false, false, '', '', false, '', true);

if (empty(pref('hr.gratuity_payable_account')) || empty(pref('hr.gratuity_expense_account'))) {
    echo '<div class="text-center w-100 py-10 text-danger fs-1">Please configure the gratuity accrual accounts</div>';
    end_page();
    exit();
}

$employees = getEmployeesKeyedById();

?>
<div id="_content" class="text-dark">
    <div class="card mx-5">
        <form method="POST" action="" class="w-100" id="gratuity-accrual-form">
            <div class="card mx-auto mb-3 mt-5 mw-800px">
                <div class="card-header">
                    <h1 class="card-title text-gray-600">Gratuity Accruals</h1>
                </div>
                <div class="card-body">
                    <div class="form-group row mt-5">
                        <label class="col-sm-3 col-form-label" for="employee_id"><?= trans('Employees') ?></label>
                        <div class="col-sm-9">
                            <select multiple class="custom-select mw-100" name="employee_ids[]" id="employee_id">
                                <option value="">-- select employee --</option>
                                <?php foreach ($employees as $id => $employee) : ?>
                                    <option value="<?= $id ?>" <?= in_array($id, $inputs['employee_ids']) ? 'selected' : '' ?>><?= $employee['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row required">
                        <label for="as_of_date" class="col-form-label col-sm-3">Gratuity accruals as of:</label>
                        <div class="col-sm-9">
                            <input type="text" required data-parsley-trigger-after-failure="change" class="form-control" name="as_of_date" id="as_of_date" data-provide="datepicker" autocomplete="off" data-date-format="<?= getDateFormatForBSDatepicker() ?>" data-date-autoclose="true" value="<?= sql2date($inputs['as_of_date']) ?>">
                            <small id="as_of_date_help" class="form-text text-muted">The date till which the gratuity accruals are calculated</small>
                        </div>
                    </div>

                    <div class="form-group row required">
                        <label for="trans_date" class="col-form-label col-sm-3">Transaction Posted On:</label>
                        <div class="col-sm-9">
                            <input type="text" required data-parsley-trigger-after-failure="change" class="form-control" name="trans_date" id="trans_date" data-provide="datepicker" autocomplete="off" data-date-format="<?= getDateFormatForBSDatepicker() ?>" data-date-autoclose="true" value="<?= sql2date($inputs['trans_date']) ?>">
                            <small id="trans_date_help" class="form-text text-muted">The date on which the transaction will be posted on</small>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="memo" class="col-form-label col-sm-3">Remarks</label>
                        <div class="col-sm-9">
                            <textarea placeholder="Remarks for this transaction" name="memo" data-parsley-pattern2="/^[\p{L}\p{M}\p{N}_\-\/,\. ]+$/u" data-parsley-pattern2-message="The name must only contains alphabets, numbers, dashes, underscore, comma, period, slash or spaces" class="form-control" id="memo">Gratuity accrual as of <?= sql2date($inputs['as_of_date']) ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <button type="button" data-action="showDetails" class="btn btn-info shadow-none">Show Details</button>
                    <button type="button" data-action="postGL" class="btn btn-primary shadow-none">Post GL</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal" tabindex="-1" id="detailsModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Gratuity Accrual Details As Of <span data-as-of></span></h5>
            </div>
            <div class="modal-body">
                <div class="table-responsive px-3 w-100">
                    <table class="table table-bordered text-nowrap text-end table-striped w-100" id="detailsDisplayTable">
                        <thead class="fw-bold">
                            <tr>
                                <th>Emp#</th>
                                <th>Employee</th>
                                <th>Date of Join</th>
                                <th>Month Sal.</th>
                                <th>Basic Sal.</th>
                                <th>Service Period</th>
                                <th>Last Accr.</th>
                                <th>Accum. Amt.</th>
                                <th>Accrued Amt.</th>
                                <th>This Amt.</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                        <tfoot>

                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer text-end">
                <button type="button" class="btn btn-secondary shadow-none" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script src="<?= $path_to_root ?>/../assets/plugins/general/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/parsley/parsley.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/parsley/extra/validator/date.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/parsley/extra/validator/words.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/toastr/build/toastr.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/sweetalert2/dist/sweetalert2.min.js" type="text/javascript"></script>
<script src="<?= erp_url('v3/plugins/custom/datatables/datatables.bundle.js') ?>" type="text/javascript"></script>
<script>
    $(function() {
        var findClosestGroupWrapper = function(field) {
            return field.$element.closest('div[class^="col"], div[class*=" col"]');
        }

        // Initialize the select2
        $('#employee_id').select2();

        // Adds Custom Validator Required if select
        window.Parsley.addValidator('pattern2', {
            validateString: function validateString(value, regexp) {
                if (!value) return true;

                var flags = '';
                if (/^\/.*\/(?:[gisumy]*)$/.test(regexp)) {
                    flags = regexp.replace(/.*\/([gisumy]*)$/, '$1');
                    regexp = regexp.replace(new RegExp('^/(.*?)/' + flags + '$'), '$1');
                } else {
                    regexp = '^' + regexp + '$';
                }

                regexp = new RegExp(regexp, flags);
                return regexp.test(value);
            },
            requirementType: 'string',
            messages: {
                en: 'This value seems to be invalid'
            }
        });

        var parsleyForm = $('#gratuity-accrual-form').parsley({
            errorClass: 'is-invalid',
            successClass: 'is-valid',
            errorsWrapper: '<ul class="errors-list"></ul>',
            classHandler: findClosestGroupWrapper,
            errorsContainer: findClosestGroupWrapper
        });

        const storage = {
            data: {
                accruals: [],
                total: []
            }
        }
        const NumberFormatter = new Intl.NumberFormat('en-US');
        const formatters = {
            number: function(data, type) {
                if (type !== 'display') {
                    return data;
                }

                return NumberFormatter.format(data);
            },
            date: function(data, type) {
                if (type !== 'display') {
                    return data;
                }

                return moment(data).format('<?= dateformat('momentJs') ?>');
            }
        }

        const submitHandlers = {
            showDetails: function(formData) {
                formData.append('action', 'showDetails');
                ajaxRequest({
                    url: parsleyForm.element.action,
                    method: 'post',
                    data: formData,
                    processData: false,
                    contentType: false,
                }).done(function(resp, msg, xhr) {
                    if (!resp.data) {
                        return defaultErrorHandler(xhr);
                    }

                    storage.data = resp.data;
                    const footer = document.querySelector('#detailsDisplayTable tfoot');
                    empty(footer);
                    footer.appendChild(prepareFooterRow());
                    document.querySelector('[data-as-of]').textContent = formData.get('as_of_date')

                    $('#detailsModal').on('shown.bs.modal', renderModal);
                    $('#detailsModal').modal('show');
                }).fail(defaultErrorHandler);
            },

            postGL: function(formData) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You are about to post GL transactions!" +
                        " This process modifies the financial statements",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, I Confirm!'
                }).then(function(result) {
                    if (result.value) {
                        formData.append('action', 'postGL');
                        ajaxRequest({
                            url: parsleyForm.element.action,
                            method: 'post',
                            data: formData,
                            processData: false,
                            contentType: false,
                        }).done(function(resp, msg, xhr) {
                            if (!resp.data) {
                                return defaultErrorHandler(xhr);
                            }

                            toastr.success('GL posted successfully');
                            createPopup(resp.data.view_link);
                        }).fail(defaultErrorHandler);
                    }
                })
            }
        }

        parsleyForm.$element.on('reset', function() {
            parsleyForm.reset();
            $('#employee_id').val('').trigger('change.select2');
            $('#as_of_date, #trans_date').datepicker('update', '');
        })

        $('#as_of_date').on('change', function() {
            const date = moment(this.value, '<?= dateformat('momentJs') ?>');
            if (date.isValid()) {
                $('#memo').val(`Gratuity accrual as of ${this.value}`);
                $('#trans_date').datepicker('setDate', date.endOf('month').toDate());
            }
        });

        // Handle the submission
        $('[data-action]').on('click', function(event) {
            var btn = event.target;
            parsleyForm
                .whenValidate()
                .then(() => {
                    storage.data = {
                        accruals: [],
                        total: []
                    };
                    submitHandlers[btn.dataset.action](new FormData(parsleyForm.element))
                });
        });

        $('#detailsModal').on('hidden.bs.modal', function() {
            if ($.fn.DataTable.isDataTable('#detailsDisplayTable')) {
                $('#detailsDisplayTable').DataTable().destroy();
            }
        });

        function renderModal() {
            const dataTable = $('#detailsDisplayTable').DataTable({
                processing: true,
                data: storage.data.accruals,
                columns: [{
                        data: 'employee_ref',
                        width: '300px',
                        className: 'ps-3 text-wrap text-start'
                    },
                    {
                        data: 'employee_name',
                        className: 'text-start'
                    },
                    {
                        data: 'date_of_join',
                        render: formatters.date,
                        className: 'text-center'
                    },
                    {
                        data: 'monthly_salary',
                        render: formatters.number
                    },
                    {
                        data: 'basic_salary',
                        render: formatters.number
                    },
                    {
                        data: 'service_period'
                    },
                    {
                        data: 'last_accrual_on',
                        render: function(data) {
                            return data ? formatters.date(data) : "-";
                        },
                        className: 'text-center'
                    },
                    {
                        data: 'accumulated_amount',
                        render: formatters.number
                    },
                    {
                        data: 'accrued_amount',
                        render: formatters.number
                    },
                    {
                        data: 'this_postings',
                        render: formatters.number,
                        className: 'pe-3'
                    },
                ]
            })

            $('#detailsModal').off('shown.bs.modal', renderModal);
        }

        function prepareFooterRow() {
            const tr = document.createElement('tr');

            let td = document.createElement('td');
            td.colSpan = 7;
            tr.appendChild(td);
            (['accumulated_amount', 'accrued_amount', 'this_postings']).forEach(key => {
                let td = document.createElement('td');
                td.textContent = NumberFormatter.format(storage.data.total[key] || '0.00');
                tr.appendChild(td);
            })

            return tr;
        }
    })
</script>
<?php $GLOBALS['__FOOT__'][] = ob_get_clean();
end_page();
